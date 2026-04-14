<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserWelcomeMail;
use App\Models\Branch;
use App\Models\IssueCategory;
use App\Models\User;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function __construct()
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(tenant('id'));
    }

    public function index(Request $request)
    {
        $tenantId = tenant('id');

        $q = User::withTrashed()
            ->where('tenant_id', $tenantId)
            ->whereHas('roles', fn ($r) => $r->where('name', '!=', 'admin'))
            ->with('roles', 'branches:id,name')
            ->withCount(['assignedIssues as open_issues_count' => fn ($q) => $q
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['new', 'in_progress']),
            ])
            ->orderByDesc('created_at');

        if ($search = $request->get('search')) {
            $like = '%'.$search.'%';
            $q->where(fn ($w) => $w->where('name', 'ilike', $like)
                ->orWhere('email', 'ilike', $like));
        }

        if ($role = $request->get('role')) {
            $q->whereHas('roles', fn ($r) => $r->where('name', $role));
        }

        if ($branch = $request->get('branch_id')) {
            $q->whereHas('branches', fn ($r) => $r->where('branches.id', $branch));
        }

        if ($request->get('status') === 'disabled') {
            $q->onlyTrashed();
        } elseif ($request->get('status') === 'active') {
            $q->withoutTrashed();
        }

        $users = $q->paginate(25)->withQueryString();
        $roles = Role::where('tenant_id', $tenantId)->get();
        $branches = Branch::where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']);

        return view('tenant.admin.users.index', compact('users', 'roles', 'branches'));
    }

    public function create()
    {
        $tenantId = tenant('id');

        $branches = Branch::where('tenant_id', $tenantId)
            ->orderBy('name')->get(['id', 'name']);

        $categories = IssueCategory::where('tenant_id', $tenantId)
            ->orderBy('name')->get(['id', 'name']);

        // Branch IDs that already have an active branch_manager assigned
        $takenBranchIds = User::role('branch_manager')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->with('branches:id')
            ->get()
            ->flatMap(fn ($u) => $u->branches->pluck('id'))
            ->unique()
            ->all();

        $roles = $branches->isNotEmpty() ? ['branch_manager', 'staff'] : ['staff'];

        return view('tenant.admin.users.create', compact('branches', 'roles', 'categories', 'takenBranchIds'));
    }

    public function store(Request $request)
    {
        $roles = ['admin', 'branch_manager', 'staff'];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:191', \Illuminate\Validation\Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', \Illuminate\Validation\Rule::in($roles)],
            'address' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'branch_id' => [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('branches', 'id')
                    ->where(fn ($q) => $q->where('tenant_id', tenant('id'))),
            ],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [
                \Illuminate\Validation\Rule::exists('issue_categories', 'id')
                    ->where(fn ($q) => $q->where('tenant_id', tenant('id'))),
            ],
        ]);

        // Plan limit check — count non-admin, non-deleted users
        $plan = PlanService::forCurrentTenant();
        $userCount = User::where('tenant_id', tenant('id'))->whereNull('deleted_at')->count();
        if (! $plan->withinLimit('max_users', $userCount)) {
            return back()->with('error', "Your plan ({$plan->planName()}) allows a maximum of {$plan->limitLabel('max_users')} staff members. Please upgrade to add more.")->withInput();
        }

        // Enforce 1 branch for these roles (for now)
        if (in_array($data['role'], ['branch_manager', 'staff'], true) && empty($data['branch_id'])) {
            return back()->withErrors(['branch_id' => 'Branch is required for this role'])->withInput();
        }

        // Enforce one branch_manager per branch
        if ($data['role'] === 'branch_manager' && ! empty($data['branch_id'])) {
            $alreadyHasManager = User::role('branch_manager')
                ->where('tenant_id', tenant('id'))
                ->whereNull('deleted_at')
                ->whereHas('branches', fn ($q) => $q->where('branches.id', $data['branch_id']))
                ->exists();

            if ($alreadyHasManager) {
                return back()->withErrors(['branch_id' => 'This branch already has a branch manager.'])->withInput();
            }
        }

        $user = \App\Models\User::create([
            'tenant_id' => tenant('id'),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'address' => $data['address'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
        ]);

        // role (team already set by your tenancy listener)
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(tenant('id'));
        $user->syncRoles([$data['role']]);

        // write SINGLE branch to pivot (but still using pivot for future flexibility)
        if (! empty($data['branch_id'])) {
            $user->branches()->sync([
                $data['branch_id'] => ['tenant_id' => tenant('id'), 'title' => null],
            ]);
        } else {
            $user->branches()->sync([]); // admins can have none
        }

        // Sync category assignments (staff only)
        if ($data['role'] === 'staff') {
            $categoryIds = collect($data['category_ids'] ?? [])->mapWithKeys(
                fn ($id) => [$id => ['tenant_id' => tenant('id')]]
            )->all();
            $user->categories()->sync($categoryIds);
        }

        Mail::to($data['email'])->queue(new UserWelcomeMail(
            name:       $data['name'],
            email:      $data['email'],
            password:   $data['password'],
            role:       $data['role'],
            schoolName: tenant()->name,
            loginUrl:   route('tenant.login'),
            tenantId:   tenant('id'),
        ));

        return redirect()->route('tenant.admin.users.index')->with('ok', 'User created.');
    }

    /**
     * Enforce branch_manager scope: they may only edit staff in their own branch(es).
     * Admins pass through unconditionally.
     */
    private function authorizeUserAccess(User $user): void
    {
        $auth = auth()->user();

        if ($auth->hasRole('admin')) {
            return;
        }

        // Branch managers may only manage staff (not other managers or admins)
        $targetRole = $user->getRoleNames()->first();
        abort_unless($targetRole === 'staff', 403);

        // And only staff who share at least one branch with the manager
        $managerBranchIds = $auth->branches->pluck('id');
        $userBranchIds    = $user->branches->pluck('id');
        abort_unless($managerBranchIds->intersect($userBranchIds)->isNotEmpty(), 403);
    }

    public function edit(\App\Models\User $user)
    {
        abort_unless($user->tenant_id === tenant('id'), 404);
        $this->authorizeUserAccess($user);

        $branches = Branch::where('tenant_id', tenant('id'))
            ->orderBy('name')->get(['id', 'name']);

        $categories = IssueCategory::where('tenant_id', tenant('id'))
            ->orderBy('name')->get(['id', 'name']);

        $tenantId = tenant('id');
        $roles = Role::where('tenant_id', $tenantId)->where('name', '!=', 'admin')->get();

        $currentRole = $user->getRoleNames()->first();
        $selectedBranchId = $user->primaryBranchId();
        $selectedBranchName = $user->primaryBranchName();
        $selectedCategories = $user->categories()->pluck('issue_categories.id')->all();

        return view('tenant.admin.users.edit', compact(
            'user',
            'branches',
            'categories',
            'roles',
            'currentRole',
            'selectedBranchId',
            'selectedBranchName',
            'selectedCategories',
        ));
    }

    public function update(\Illuminate\Http\Request $request, \App\Models\User $user)
    {
        abort_unless($user->tenant_id === tenant('id'), 404);
        $this->authorizeUserAccess($user);

        $roles = ['admin', 'branch_manager', 'staff'];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:191', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', \Illuminate\Validation\Rule::in($roles)],
            'address' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'branch_id' => [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('branches', 'id')
                    ->where(fn ($q) => $q->where('tenant_id', tenant('id'))),
            ],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [
                \Illuminate\Validation\Rule::exists('issue_categories', 'id')
                    ->where(fn ($q) => $q->where('tenant_id', tenant('id'))),
            ],
        ]);

        if (in_array($data['role'], ['branch_manager', 'staff'], true) && empty($data['branch_id'])) {
            return back()->withErrors(['branch_id' => 'Branch is required for this role'])->withInput();
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'address' => $data['address'],
            'phone_number' => $data['phone_number'],
        ]);
        if (! empty($data['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
        }
        $user->save();

        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(tenant('id'));
        $user->syncRoles([$data['role']]);

        // enforce SINGLE pivot row for now
        if (! empty($data['branch_id'])) {
            $user->branches()->sync([
                $data['branch_id'] => ['tenant_id' => tenant('id'), 'title' => null],
            ]);
        } else {
            $user->branches()->sync([]);
        }

        // Sync category assignments (staff only; clear for other roles)
        if ($data['role'] === 'staff') {
            $categoryIds = collect($data['category_ids'] ?? [])->mapWithKeys(
                fn ($id) => [$id => ['tenant_id' => tenant('id')]]
            )->all();
            $user->categories()->sync($categoryIds);
        } else {
            $user->categories()->sync([]);
        }

        return redirect()->route('tenant.admin.users.edit', $user)->with('ok', 'User updated.');
    }

    public function autoAssign()
    {
        $tenantId = tenant('id');
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        $branches   = Branch::where('tenant_id', $tenantId)->orderBy('name')->get();
        $categories = IssueCategory::where('tenant_id', $tenantId)->orderBy('name')->get();

        $staff = User::role('staff')
            ->where('tenant_id', $tenantId)
            ->with(['branches:id,name', 'categories:id,name'])
            ->orderBy('name')
            ->get();

        $managers = User::role('branch_manager')
            ->where('tenant_id', $tenantId)
            ->with('branches:id,name')
            ->orderBy('name')
            ->get();

        $matrix = $branches->map(function (Branch $branch) use ($staff, $managers, $categories) {
            $manager = $managers->first(fn ($m) => $m->branches->contains('id', $branch->id));

            $rules = $categories->map(fn ($cat) => [
                'category' => $cat,
                'assignee' => $staff->first(
                    fn ($s) => $s->branches->contains('id', $branch->id)
                        && $s->categories->contains('id', $cat->id)
                ),
            ]);

            $staffInBranch           = $staff->filter(fn ($s) => $s->branches->contains('id', $branch->id));
            $staffWithoutCategories  = $staffInBranch->filter(fn ($s) => $s->categories->isEmpty());

            return compact('branch', 'manager', 'rules', 'staffWithoutCategories');
        });

        return view('tenant.admin.users.auto_assign', compact('matrix', 'categories'));
    }

    /**
     * Soft delete the specified user.
     */
    public function destroy(User $user)
    {
        abort_unless($user->tenant_id === tenant('id'), 404);
        $this->authorizeUserAccess($user);

        $user->delete();

        return redirect()->route('tenant.admin.users.index')->with('ok', 'User disabled.');
    }

    public function restore($id)
    {
        $user = User::withTrashed()->where('id', $id)->first();
        abort_unless($user && $user->tenant_id === tenant('id'), 404);
        $this->authorizeUserAccess($user);
        $user->restore();

        return redirect()->route('tenant.admin.users.index')->with('ok', 'User re-enabled.');
    }

    public function forceLogout(User $user)
    {
        abort_unless($user->tenant_id === tenant('id'), 404);
        $this->authorizeUserAccess($user);

        $user->update(['active_session_id' => 'terminated']);

        return back()->with('ok', "{$user->name}'s session has been terminated. They will be signed out on their next request.");
    }
}
