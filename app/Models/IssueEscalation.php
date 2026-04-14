<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class IssueEscalation extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'issue_id',
        'escalation_rule_id',
        'fired_at',
        'action_taken',
    ];

    protected $casts = [
        'fired_at'     => 'datetime',
        'action_taken' => 'array',
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function rule()
    {
        return $this->belongsTo(EscalationRule::class, 'escalation_rule_id');
    }
}
