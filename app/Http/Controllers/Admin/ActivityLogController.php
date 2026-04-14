<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Issue;
use App\Models\IssueActivity;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = tenant('id');
        $user     = auth()->user();

        // Resolve role-based branch scope
        $branchIds = [];
        if ($user->hasRole('branch_manager')) {
            $branchIds = $user->branches()->pluck('branches.id')->all();
        }

        // Branch filter (admin only — branch_manager is already scoped)
        $filterBranchId = $user->hasRole('admin') ? (int) $request->get('branch_id') ?: null : null;

        // Scope visible issue IDs
        $issueQuery = Issue::where('tenant_id', $tenantId);
        if ($user->hasRole('branch_manager')) {
            $issueQuery->whereIn('branch_id', $branchIds ?: [-1]);
        }
        if ($filterBranchId) {
            $issueQuery->where('branch_id', $filterBranchId);
        }
        $visibleIssueIds = $issueQuery->pluck('id');

        $q = IssueActivity::where('tenant_id', $tenantId)
            ->whereIn('issue_id', $visibleIssueIds)
            ->with(['issue:id,public_id,title,branch_id', 'actor:id,name'])
            ->latest();

        // Filters
        if ($type = $request->get('type')) {
            $q->where('type', $type);
        }

        if ($actorId = $request->get('actor_id')) {
            $q->where('actor_id', $actorId);
        }

        if ($from = $request->get('from')) {
            $q->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $q->whereDate('created_at', '<=', $to);
        }

        if ($issueFilter = $request->get('issue')) {
            $matchedIds = Issue::where('tenant_id', $tenantId)
                ->where('public_id', 'ilike', "%{$issueFilter}%")
                ->pluck('id');
            $q->whereIn('issue_id', $matchedIds);
        }

        $activities = $q->paginate(50)->withQueryString();

        // Bulk-resolve user IDs stored inside 'assigned' data fields
        $assignedUserIds = $activities
            ->filter(fn ($a) => $a->type === 'assigned')
            ->flatMap(fn ($a) => array_filter([$a->data['from'] ?? null, $a->data['to'] ?? null]))
            ->unique()
            ->values();

        $userMap = User::whereIn('id', $assignedUserIds)->pluck('name', 'id');

        // Branches for admin filter dropdown
        $branches = $user->hasRole('admin')
            ? Branch::where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name'])
            : collect();

        // Actors dropdown — scoped to selected branch (admin) or own branches (branch_manager)
        $actorsQuery = User::where('tenant_id', $tenantId)->orderBy('name')->with('branches:id,name');
        if ($user->hasRole('branch_manager')) {
            $actorsQuery->whereHas('branches', fn ($q) => $q->whereIn('branches.id', $branchIds ?: [-1]));
        } elseif ($filterBranchId) {
            $actorsQuery->whereHas('branches', fn ($q) => $q->where('branches.id', $filterBranchId));
        }
        $actors = $actorsQuery->get(['id', 'name']);

        $types = ['assigned', 'status_changed', 'priority_changed', 'commented', 'message_deleted', 'contact_moved'];

        return view('tenant.admin.activity_log.index', compact(
            'activities', 'userMap', 'actors', 'branches', 'filterBranchId', 'types'
        ));
    }
}
