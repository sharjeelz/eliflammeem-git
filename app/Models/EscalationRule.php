<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class EscalationRule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'is_active',
        'trigger_status',
        'hours_threshold',
        'priority_filter',
        'action_notify_role',
        'action_bump_priority',
        'scope_type',
        'scope_id',
        'sort_order',
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'action_bump_priority' => 'boolean',
        'hours_threshold'      => 'integer',
        'scope_id'             => 'integer',
        'sort_order'           => 'integer',
    ];

    public function escalations()
    {
        return $this->hasMany(IssueEscalation::class);
    }

    public function appliesToIssue(Issue $issue): bool
    {
        return match ($this->scope_type) {
            'branch'   => $issue->branch_id === $this->scope_id,
            'category' => $issue->issue_category_id === $this->scope_id,
            default    => true,
        };
    }
}
