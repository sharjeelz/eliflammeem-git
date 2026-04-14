<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Issue;
use App\Models\IssueAiAnalysis;
use App\Models\IssueCategory;
use App\Models\SavedFilter;
use App\Models\User;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class IssueController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $userBranchIds = $user->branches()->pluck('branches.id')->all();

        $q = Issue::query()
            ->where('tenant_id', tenant('id'))
            ->with([
                'branch:id,name',
                'assignedTo:id,name',
                'roasterContact:id,name,role',
                'aiAnalysis',
                'issueCategory:id,name',
            ])
            ->when($user->hasRole('branch_manager'), fn ($qq) => $qq->whereIn('branch_id', $userBranchIds ?: [-1])->where('is_anonymous', false))
            ->when($user->hasRole('staff'), fn ($qq) => $qq->where('assigned_user_id', $user->id));

        if ($search = $request->get('search')) {
            $like = '%'.$search.'%';
            $q->where(fn ($w) => $w->where('title', 'ilike', $like)
                ->orWhere('public_id', 'ilike', $like)
                ->orWhere('description', 'ilike', $like));
        }
        if ($status = $request->get('status')) {
            $q->where('status', $status);
        }
        if ($priority = $request->get('priority')) {
            $q->where('priority', $priority);
        }
        if ($request->get('assigned_user_id') === 'none') {
            $q->whereNull('assigned_user_id');
        } elseif ($request->get('assigned_user_id') === 'branch_moved') {
            $q->whereRaw("meta->>'unassigned_reason' = 'contact_branch_changed'");
        } elseif ($assignee = $request->get('assigned_user_id')) {
            $q->where('assigned_user_id', $assignee);
        }
        if ($branch = $request->get('branch_id')) {
            // Branch managers may not filter outside their own branches
            if (! $user->hasRole('branch_manager') || in_array($branch, $userBranchIds, false)) {
                $q->where('branch_id', $branch);
            }
        }
        if ($from = $request->get('from')) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $q->whereDate('created_at', '<=', $to);
        }
        if ($categoryId = $request->get('category_id')) {
            $q->where('issue_category_id', $categoryId);
        }
        if ($urgency = $request->get('urgency')) {
            $q->whereHas('aiAnalysis', fn ($aq) =>
                $aq->where('analysis_type', 'full')
                   ->whereRaw("result->>'urgency_flag' = ?", [$urgency])
            );
        }
        if ($theme = $request->get('theme')) {
            $q->whereHas('aiAnalysis', fn ($aq) =>
                $aq->where('analysis_type', 'full')
                   ->whereRaw("(result->'themes')::jsonb @> ?::jsonb", [json_encode([$theme])])
            );
        }
        if ($sentiment = $request->get('sentiment')) {
            $q->whereHas('aiAnalysis', fn ($aq) =>
                $aq->whereIn('analysis_type', ['full', 'sentiment'])
                   ->whereRaw("lower(COALESCE(result->>'sentiment', result->>'label')) = ?", [strtolower($sentiment)])
            );
        }
        if ($submissionType = $request->get('submission_type')) {
            $q->where('submission_type', $submissionType);
        }

        // SLA overdue filter
        if ($request->boolean('sla_overdue')) {
            $q->whereNotNull('sla_due_at')
              ->where('sla_due_at', '<', now())
              ->whereNotIn('status', ['resolved', 'closed']);
        }

        // View filter: 'only' = spam, 'anonymous' = anonymous, default = normal
        $view = $request->get('spam');
        if ($view === 'only') {
            $q->where('is_spam', true);
        } elseif ($view === 'anonymous') {
            $q->where('is_anonymous', true)->where('is_spam', false);
        } else {
            $q->where('is_spam', false);
        }

        $issues = $q->orderByDesc('created_at')->paginate(25)->withQueryString();

        // Assignable staff: admin sees all, branch_manager sees own branch staff + themselves
        $staffQuery = User::where('tenant_id', tenant('id'))->orderBy('name');
        if ($user->hasRole('branch_manager')) {
            $staffQuery->where(fn ($w) =>
                $w->whereHas('branches', fn ($b) => $b->whereIn('branches.id', $userBranchIds ?: [-1]))
                  ->orWhere('id', $user->id)
            );
        }
        $staffList  = $staffQuery->get(['id', 'name']);
        $branches   = Branch::active()->where('tenant_id', tenant('id'))->orderBy('name')->get(['id', 'name']);
        $categories = IssueCategory::where('tenant_id', tenant('id'))->orderBy('name')->get(['id', 'name']);

        // Overdue issues: SLA deadline passed and still open
        $overdueIssues = Issue::query()
            ->where('tenant_id', tenant('id'))
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', now())
            ->whereNotIn('status', ['resolved', 'closed'])
            ->with(['branch:id,name', 'assignedTo:id,name', 'roasterContact:id,name,role'])
            ->when($user->hasRole('branch_manager'), fn ($qq) => $qq->whereIn('branch_id', $userBranchIds ?: [-1]))
            ->when($user->hasRole('staff'), fn ($qq) => $qq->where('assigned_user_id', $user->id))
            ->orderBy('sla_due_at')
            ->get();

        // Spam banner — always shown regardless of active filter, role-scoped
        $spamIssues = Issue::query()
            ->where('tenant_id', tenant('id'))
            ->where('is_spam', true)
            ->with(['branch:id,name', 'roasterContact:id,name,role'])
            ->when($user->hasRole('branch_manager'), fn ($qq) => $qq->whereIn('branch_id', $userBranchIds ?: [-1]))
            ->when($user->hasRole('staff'), fn ($qq) => $qq->where('assigned_user_id', $user->id))
            ->orderByDesc('created_at')
            ->get();

        // Get all unique themes from AI analysis for filter dropdown
        $availableThemes = IssueAiAnalysis::where('tenant_id', tenant('id'))
            ->where('analysis_type', 'full')
            ->whereNotNull('result')
            ->get(['result'])
            ->flatMap(fn ($a) => $a->result['themes'] ?? [])
            ->unique()
            ->sort()
            ->values();

        $planAllowCsvExport = PlanService::forCurrentTenant()->allows('csv_export');

        $savedFilters = SavedFilter::where('tenant_id', tenant('id'))
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get(['id', 'name', 'query_params']);

        return view('tenant.admin.issues.index', compact('issues', 'staffList', 'branches', 'categories', 'overdueIssues', 'spamIssues', 'availableThemes', 'planAllowCsvExport', 'savedFilters'));
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $userBranchIds = $user->branches()->pluck('branches.id')->all();

        $q = Issue::query()
            ->where('tenant_id', tenant('id'))
            ->with([
                'branch:id,name',
                'assignedTo:id,name',
                'roasterContact:id,name',
                'issueCategory:id,name',
            ])
            ->when($user->hasRole('branch_manager'), fn ($qq) => $qq->whereIn('branch_id', $userBranchIds ?: [-1]))
            ->when($user->hasRole('staff'), fn ($qq) => $qq->where('assigned_user_id', $user->id));

        if ($search = $request->get('search')) {
            $like = '%'.$search.'%';
            $q->where(fn ($w) => $w->where('title', 'ilike', $like)
                ->orWhere('public_id', 'ilike', $like)
                ->orWhere('description', 'ilike', $like));
        }
        if ($status = $request->get('status')) {
            $q->where('status', $status);
        }
        if ($priority = $request->get('priority')) {
            $q->where('priority', $priority);
        }
        if ($request->get('assigned_user_id') === 'none') {
            $q->whereNull('assigned_user_id');
        } elseif ($request->get('assigned_user_id') === 'branch_moved') {
            $q->whereRaw("meta->>'unassigned_reason' = 'contact_branch_changed'");
        } elseif ($assignee = $request->get('assigned_user_id')) {
            $q->where('assigned_user_id', $assignee);
        }
        if ($branch = $request->get('branch_id')) {
            if (! $user->hasRole('branch_manager') || in_array($branch, $userBranchIds, false)) {
                $q->where('branch_id', $branch);
            }
        }
        if ($from = $request->get('from')) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $q->whereDate('created_at', '<=', $to);
        }

        $filename = 'issues_'.now()->format('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store',
        ];

        $columns = ['ID', 'Title', 'Status', 'Priority', 'Branch', 'Category', 'Contact', 'Assigned To', 'Created At', 'Resolved At'];

        $callback = function () use ($q, $columns) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so Excel opens it correctly
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $columns);

            $q->orderByDesc('created_at')->chunk(500, function ($issues) use ($out) {
                foreach ($issues as $i) {
                    fputcsv($out, [
                        $i->public_id,
                        $i->title,
                        ucwords(str_replace('_', ' ', $i->status)),
                        ucfirst($i->priority),
                        $i->branch->name ?? '',
                        $i->issueCategory->name ?? '',
                        $i->roasterContact->name ?? '',
                        $i->assignedTo->name ?? '',
                        $i->created_at?->format('Y-m-d H:i'),
                        $i->resolved_at?->format('Y-m-d H:i') ?? '',
                    ]);
                }
            });

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show(\App\Models\Issue $issue)
    {
        // Ensure the issue belongs to the current tenant
        abort_unless($issue->tenant_id === tenant('id'), 404);

        $user = request()->user()->load(['branches.users', 'branches.users.categories']);

        // Ensure Spatie permission team is scoped to this tenant
        app(PermissionRegistrar::class)->setPermissionsTeamId(tenant('id'));

        $this->authorize('view', $issue);

        // -------------------- Eager Loads --------------------
        $issue->load([
            'messages' => fn ($q) => $q->orderBy('created_at'),
            'messages.author',
            'attachments',
            'aiAnalysis',
            'branch:id,name',
            'school:id,name',
            'assignedTo:id,name',
            'assignedTo.branches:id,name',
            'assignedTo.categories:id,name',
            'roasterContact:id,name,role',
            'issueCategory:id,name',
            'activities' => fn ($q) => $q->latest('created_at')->with('actor:id,name'),
        ]);

        // -------------------- Staff List for Reassignment --------------------
        // Each entry gets ->role_label and ->branch_names for display in the modal.
        // Always scoped to the issue's branch — no cross-branch assignment.
        if ($user->hasRole('admin')) {
            $staff = User::query()
                ->where('tenant_id', tenant('id'))
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['branch_manager', 'staff']))
                ->whereHas('branches', fn ($q) => $q->where('branches.id', $issue->branch_id))
                ->with(['branches:id,name', 'categories:id,name'])
                ->orderBy('name')
                ->get()
                ->each(function ($u) {
                    $u->role_label      = $u->hasRole('branch_manager') ? 'Branch Manager' : 'Staff';
                    $u->branch_names    = $u->branches->pluck('name')->join(', ') ?: '—';
                    $u->category_names  = $u->categories->pluck('name');
                });
        } elseif ($user->hasRole('branch_manager')) {
            $staff = $user->branches
                ->flatMap(fn ($branch) => $branch->users
                    ->filter(fn ($u) => $u->hasRole('staff'))
                    ->each(fn ($u) => $u->branch_names = $branch->name)
                )
                ->unique('id')
                ->each(function ($u) {
                    $u->role_label     = 'Staff';
                    $u->category_names = $u->relationLoaded('categories') ? $u->categories->pluck('name') : collect();
                })
                ->sortBy('name')
                ->values();
        } else {
            $staff = collect();
        }

        // -------------------- Allowed Status Transitions --------------------
        $allowed = \App\Models\Issue::allowedTransitions()[$issue->status] ?? [];

        // Only admins can move away from 'closed'
        if ($issue->status === 'closed' && ! $user->hasRole('admin')) {
            $allowed = [];
        }

        // Staff can only resolve — remove 'closed' from their dropdown
        if ($user->hasRole('staff')) {
            $allowed = array_values(array_filter($allowed, fn ($s) => $s !== 'closed'));
        }

        // Private note for the current user only — scoped strictly to their user_id
        $myNote = \App\Models\IssueNote::where('tenant_id', tenant('id'))
            ->where('issue_id', $issue->id)
            ->where('user_id', $user->id)
            ->first();

        $categories = IssueCategory::where('tenant_id', tenant('id'))->orderBy('name')->get(['id', 'name']);

        return view('tenant.admin.issues.show', [
            'issue'              => $issue,
            'staff'              => $staff,
            'allowedTransitions' => $allowed,
            'myNote'             => $myNote,
            'categories'         => $categories,
        ]);
    }

    public function getIssuesByUser(User $user, Request $request)
    {
        $authUser      = Auth::user();
        $authBranchIds = $authUser->branches()->pluck('branches.id')->all();

        if ($authUser->hasRole('admin')) {
            // Admin can view any user's issues — no branch restriction
        } elseif ($authUser->hasRole('branch_manager')) {
            // BM may only view issues for users in their own branches
            $targetBranchIds = $user->branches()->pluck('branches.id')->all();
            if (count(array_intersect($authBranchIds, $targetBranchIds)) === 0) {
                abort(403, 'This user does not belong to your branch(es).');
            }
        } elseif ($user->id !== $authUser->id) {
            abort(403);
        }

        $issues = Issue::query()
            ->where('tenant_id', tenant('id'))
            ->where('assigned_user_id', $user->id)
            ->with([
                'messages'      => fn ($q) => $q->orderBy('created_at'),
                'messages.author',
                'attachments',
                'branch:id,name',
                'school:id,name',
                'assignedTo:id,name',
                'roasterContact:id,name,role',
                'activities'    => fn ($q) => $q->latest('created_at')->with('actor:id,name'),
            ])
            ->when($authUser->hasRole('branch_manager'), fn ($qq) => $qq->whereIn('branch_id', $authBranchIds ?: [-1]))
            ->orderByDesc('created_at')
            ->get();

        return view('tenant.admin.issues.index-issues', compact('issues', 'user'));
    }

    public function getIssuesToMe(Request $request)
    {
        $issues = Issue::query()
            ->where('tenant_id', tenant('id'))
            ->where('assigned_user_id', Auth::user()->id)
            ->with([
                'messages'      => fn ($q) => $q->orderBy('created_at'),
                'messages.author',
                'attachments',
                'branch:id,name',
                'school:id,name',
                'assignedTo:id,name',
                'roasterContact:id,name,role',
                'activities'    => fn ($q) => $q->latest('created_at')->with('actor:id,name'),
            ])
            ->orderByDesc('created_at')
            ->get();
        $user = Auth::user();

        return view('tenant.admin.issues.index-issues', compact('issues', 'user'));
    }
}
