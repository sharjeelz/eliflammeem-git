<?php

namespace App\Jobs;

use App\Models\EscalationRule;
use App\Models\Issue;
use App\Models\IssueEscalation;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\IssueEscalatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\Permission\PermissionRegistrar;

class FireEscalation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 2;

    public int $timeout = 30;

    public function __construct(
        public readonly int $issueId,
        public readonly int $ruleId,
        public readonly string $tenantId,
    ) {}

    public function handle(): void
    {
        tenancy()->initialize(Tenant::find($this->tenantId));

        try {
            $rule  = EscalationRule::find($this->ruleId);
            $issue = Issue::with(['branch', 'assignedTo'])->find($this->issueId);

            if (! $rule || ! $issue) {
                return;
            }

            // Bail if status changed while job was queued
            if ($issue->status !== $rule->trigger_status || $issue->status === 'closed') {
                return;
            }

            // Atomic dedup — unique DB constraint is the final guard against race conditions
            $escalation = IssueEscalation::firstOrCreate(
                ['issue_id' => $issue->id, 'escalation_rule_id' => $rule->id],
                ['tenant_id' => $this->tenantId, 'fired_at' => now(), 'action_taken' => []],
            );

            if (! $escalation->wasRecentlyCreated) {
                return; // Another worker beat us to it
            }

            app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenantId);

            $actionTaken = [];

            // --- Notify roles ---
            if ($rule->action_notify_role) {
                $roles = $rule->action_notify_role === 'both'
                    ? ['admin', 'branch_manager']
                    : [$rule->action_notify_role];

                $usersQuery = User::role($roles)->where('tenant_id', $this->tenantId);

                // Scope branch_managers to the issue's branch
                if ($issue->branch_id && ! in_array('admin', $roles, true)) {
                    $usersQuery->whereHas('branches', fn ($q) => $q->where('branches.id', $issue->branch_id));
                } elseif ($issue->branch_id && $rule->action_notify_role === 'both') {
                    // For 'both': admins get all, branch_managers only their branch
                    $usersQuery = User::where('tenant_id', $this->tenantId)->where(function ($q) use ($issue) {
                        $q->role('admin')
                          ->orWhere(fn ($q2) => $q2->role('branch_manager')
                              ->whereHas('branches', fn ($b) => $b->where('branches.id', $issue->branch_id))
                          );
                    });
                }

                $notifyUsers = $usersQuery->get();

                $notification = new IssueEscalatedNotification($issue, $rule);
                foreach ($notifyUsers as $user) {
                    $user->notify($notification);
                }

                $actionTaken['notified'] = $notifyUsers->pluck('id')->toArray();
            }

            // --- Bump priority ---
            if ($rule->action_bump_priority) {
                $levels = ['low', 'medium', 'high', 'urgent'];
                $currentIdx = array_search($issue->priority, $levels, true);

                if ($currentIdx !== false && $currentIdx < 3) {
                    $newPriority = $levels[$currentIdx + 1];
                    Issue::where('id', $issue->id)->update(['priority' => $newPriority]);
                    $actionTaken['priority_bumped'] = ['from' => $issue->priority, 'to' => $newPriority];
                }
            }

            $escalation->update(['action_taken' => $actionTaken]);
        } finally {
            tenancy()->end();
        }
    }
}
