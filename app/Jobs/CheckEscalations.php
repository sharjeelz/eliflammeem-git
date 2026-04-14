<?php

namespace App\Jobs;

use App\Models\EscalationRule;
use App\Models\Issue;
use App\Models\IssueEscalation;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class CheckEscalations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;

    public int $timeout = 60;

    public function __construct(
        public readonly string $tenantId,
    ) {}

    public function handle(): void
    {
        tenancy()->initialize(Tenant::find($this->tenantId));

        try {
            $rules = EscalationRule::where('tenant_id', $this->tenantId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            if ($rules->isEmpty()) {
                return;
            }

            foreach ($rules as $rule) {
                $cutoff = now()->subHours($rule->hours_threshold);

                $issues = Issue::where('tenant_id', $this->tenantId)
                    ->where('status', $rule->trigger_status)
                    ->where('status_entered_at', '<=', $cutoff)
                    ->where('is_spam', false)
                    ->when($rule->priority_filter, fn ($q) => $q->where('priority', $rule->priority_filter))
                    ->get(['id', 'branch_id', 'issue_category_id', 'priority', 'assigned_user_id', 'status']);

                // Get issue IDs that have already been escalated by this rule
                $alreadyFiredIds = IssueEscalation::where('escalation_rule_id', $rule->id)
                    ->whereIn('issue_id', $issues->pluck('id'))
                    ->pluck('issue_id')
                    ->flip();

                foreach ($issues as $issue) {
                    if (isset($alreadyFiredIds[$issue->id])) {
                        continue;
                    }

                    if (! $rule->appliesToIssue($issue)) {
                        continue;
                    }

                    FireEscalation::dispatch($issue->id, $rule->id, $this->tenantId);
                }
            }
        } finally {
            tenancy()->end();
        }
    }
}
