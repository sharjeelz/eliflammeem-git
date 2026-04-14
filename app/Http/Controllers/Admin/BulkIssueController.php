<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\IssueActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

class BulkIssueController extends Controller
{
    public function __construct()
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(tenant('id'));
    }

    public function update(Request $request)
    {
        $user       = $request->user();
        $tenantId   = tenant('id');
        $branchIds  = $user->branches()->pluck('branches.id')->all();

        $data = $request->validate([
            'ids'              => ['required', 'array', 'min:1', 'max:100'],
            'ids.*'            => ['integer'],
            'action'           => ['required', Rule::in(['assign', 'status', 'priority'])],
            'assigned_user_id' => ['nullable', 'integer'],
            'status'           => ['nullable', Rule::in(['new', 'in_progress', 'resolved', 'closed'])],
            'priority'         => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'close_note'       => ['nullable', 'string', 'max:2000'],
        ]);

        // Closing issues requires a resolution note
        if ($data['action'] === 'status' && ($data['status'] ?? null) === 'closed' && empty(trim($data['close_note'] ?? ''))) {
            return response()->json(['message' => 'A resolution note is required when closing issues.'], 422);
        }

        // Staff may not bulk-assign or bulk-change-priority
        if ($user->hasRole('staff') && in_array($data['action'], ['assign', 'priority'], true)) {
            abort(403);
        }

        // Scope issues to what this user is allowed to see
        $issues = Issue::where('tenant_id', $tenantId)
            ->whereIn('id', $data['ids'])
            ->when($user->hasRole('branch_manager'), fn ($q) => $q->whereIn('branch_id', $branchIds ?: [-1]))
            ->when($user->hasRole('staff'),          fn ($q) => $q->where('assigned_user_id', $user->id))
            ->get();

        // Pre-load assignee for assign action (avoids N+1)
        $assignee = null;
        if ($data['action'] === 'assign' && ! empty($data['assigned_user_id'])) {
            $assignee = User::where('tenant_id', $tenantId)
                ->with('branches')
                ->find($data['assigned_user_id']);
        }

        $updated = 0;
        $skipped = 0;

        foreach ($issues as $issue) {
            match ($data['action']) {
                'assign'   => $this->applyAssign($issue, $user, $assignee, $branchIds, $updated, $skipped),
                'status'   => $this->applyStatus($issue, $user, $data['status'] ?? null, trim($data['close_note'] ?? ''), $updated, $skipped),
                'priority' => $this->applyPriority($issue, $user, $data['priority'] ?? null, $updated, $skipped),
            };
        }

        $msg = "{$updated} issue" . ($updated === 1 ? '' : 's') . ' updated';
        if ($skipped) {
            $msg .= ", {$skipped} skipped (invalid transition or permission).";
        }

        return response()->json(['updated' => $updated, 'skipped' => $skipped, 'message' => $msg]);
    }

    private function applyAssign(Issue $issue, User $actor, ?User $assignee, array $branchIds, int &$updated, int &$skipped): void
    {
        if (! $assignee) { $skipped++; return; }

        // Branch manager may only assign to staff in their own branches
        if ($actor->hasRole('branch_manager')) {
            $assigneeBranchIds = $assignee->branches->pluck('id')->toArray();
            if (! $assignee->hasRole('staff') || count(array_intersect($branchIds, $assigneeBranchIds)) === 0) {
                $skipped++; return;
            }
        }

        $prev = $issue->assigned_user_id;
        $issue->assigned_user_id = $assignee->id;
        if ($issue->status === 'new' && ! $issue->first_response_at) {
            $issue->first_response_at = now();
        }
        $issue->last_activity_at = now();
        $issue->save();

        IssueActivity::create([
            'issue_id' => $issue->id,
            'actor_id' => $actor->id,
            'type'     => 'assigned',
            'data'     => ['from' => $prev, 'to' => $assignee->id],
        ]);

        $updated++;
    }

    private function applyStatus(Issue $issue, User $actor, ?string $to, string $closeNote, int &$updated, int &$skipped): void
    {
        if (! $to) { $skipped++; return; }

        $from    = $issue->status;
        $allowed = Issue::allowedTransitions();

        if (! in_array($to, $allowed[$from] ?? [], true)) { $skipped++; return; }
        if ($actor->hasRole('staff') && $to === 'closed')  { $skipped++; return; }

        $issue->status = $to;
        if ($to === 'in_progress' && ! $issue->first_response_at) {
            $issue->first_response_at = now();
        }
        if ($to === 'resolved') {
            $issue->resolved_at = now();
        }
        if ($to === 'closed' && $closeNote !== '') {
            $issue->close_note = $closeNote;
        }
        $issue->last_activity_at = now();
        $issue->save();

        IssueActivity::create([
            'tenant_id' => tenant('id'),
            'issue_id'  => $issue->id,
            'actor_id'  => $actor->id,
            'type'      => 'status_changed',
            'data'      => array_filter(['from' => $from, 'to' => $to, 'close_note' => $closeNote ?: null]),
        ]);

        $updated++;
    }

    private function applyPriority(Issue $issue, User $actor, ?string $to, int &$updated, int &$skipped): void
    {
        if (! $to || $issue->priority === $to) { $skipped++; return; }

        $from = $issue->priority;
        $issue->update(['priority' => $to]);

        IssueActivity::create([
            'issue_id' => $issue->id,
            'actor_id' => $actor->id,
            'type'     => 'priority_changed',
            'data'     => ['from' => $from, 'to' => $to],
        ]);

        $updated++;
    }
}
