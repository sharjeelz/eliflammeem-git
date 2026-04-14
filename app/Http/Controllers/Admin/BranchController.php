<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\School;
use App\Services\PlanService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $q = Branch::where('tenant_id', tenant('id'))
            ->orderBy('name');

        if ($search = $request->get('search')) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhere('city', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $q->where('status', $status);
        }

        $branches = $q->paginate(25)->withQueryString();

        return view('tenant.admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('tenant.admin.branches.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $tenantId = tenant('id');
        $schoolId = School::where('tenant_id', $tenantId)->value('id');

        // Plan limit check
        $plan = PlanService::forCurrentTenant();
        $branchCount = Branch::where('tenant_id', $tenantId)->count();
        if (! $plan->withinLimit('max_branches', $branchCount)) {
            return back()->with('error', "Your plan ({$plan->planName()}) allows a maximum of {$plan->limitLabel('max_branches')} branches. Please upgrade to add more.")->withInput();
        }

        // Unique code per tenant
        $exists = Branch::where('tenant_id', $tenantId)
            ->where('code', $data['code'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'A branch with this code already exists.'])->withInput();
        }

        Branch::create([
            'tenant_id' => $tenantId,
            'school_id' => $schoolId,
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $data['status'],
        ]);

        return redirect()->route('tenant.admin.branches.index')
            ->with('ok', 'Branch created successfully.');
    }

    public function edit(Branch $branch)
    {
        abort_unless($branch->tenant_id === tenant('id'), 404);

        return view('tenant.admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        abort_unless($branch->tenant_id === tenant('id'), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Unique code per tenant (excluding self)
        $exists = Branch::where('tenant_id', tenant('id'))
            ->where('code', $data['code'])
            ->where('id', '!=', $branch->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'A branch with this code already exists.'])->withInput();
        }

        $branch->update([
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $data['status'],
        ]);

        return redirect()->route('tenant.admin.branches.edit', $branch)
            ->with('ok', 'Branch updated.');
    }

    public function toggleStatus(Branch $branch)
    {
        abort_unless($branch->tenant_id === tenant('id'), 404);

        $newStatus = $branch->status === 'active' ? 'inactive' : 'active';
        $branch->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'activated' : 'deactivated';

        return back()->with('ok', "Branch \"{$branch->name}\" {$label}.");
    }

    public function destroy(Branch $branch)
    {
        abort_unless($branch->tenant_id === tenant('id'), 404);

        $branch->delete();

        return redirect()->route('tenant.admin.branches.index')
            ->with('ok', 'Branch deleted.');
    }
}
