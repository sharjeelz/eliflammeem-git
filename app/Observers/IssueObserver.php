<?php

// app/Observers/IssueObserver.php

namespace App\Observers;

use App\Models\Issue;
use App\Models\IssueCategory;

class IssueObserver
{
    public function creating(Issue $issue): void
    {

        if ($issue->issue_category_id && ! $issue->sla_hours) {
            $cat = IssueCategory::find($issue->issue_category_id);
            if ($cat && $cat->default_sla_hours) {
                $issue->sla_hours = (int) $cat->default_sla_hours;
            }
        }

        // Compute due time if we got sla_hours
        if ($issue->sla_hours && ! $issue->sla_due_at) {
            $issue->sla_due_at = now()->addHours($issue->sla_hours);
        }

        // Always set last activity
        if (! $issue->last_activity_at) {
            $issue->last_activity_at = now();
        }

    }

    public function updating(Issue $issue): void
    {
        // If category changed and SLA not manually set, re-derive from new category
        if ($issue->isDirty('category_id') && ! $issue->isDirty('sla_hours')) {
            $cat = null;
            if ($issue->category_id) {
                $cat = IssueCategory::query()
                    ->where('tenant_id', $issue->tenant_id ?? tenant('id'))
                    ->find($issue->category_id);
            }
            if ($cat && $cat->default_sla_hours) {
                $issue->sla_hours = (int) $cat->default_sla_hours;
            }
        }

        // Recompute due when hours change or category forced a change
        if ($issue->isDirty('sla_hours') && $issue->sla_hours) {
            $issue->sla_due_at = now()->addHours((int) $issue->sla_hours);
        }
    }
}
