<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class IssueGroup extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'label',
        'theme',
        'issue_category_id',
        'branch_id',
        'confidence',
        'issue_count',
        'status',
        'resolved_message',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'issue_count' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(IssueGroupItem::class);
    }

    /** Active items only (not removed by admin) */
    public function activeItems()
    {
        return $this->hasMany(IssueGroupItem::class)->whereNull('removed_at');
    }

    public function issues()
    {
        return $this->hasManyThrough(
            Issue::class,
            IssueGroupItem::class,
            'issue_group_id',
            'id',
            'id',
            'issue_id'
        )->whereNull('issue_group_items.removed_at');
    }

    public function category()
    {
        return $this->belongsTo(IssueCategory::class, 'issue_category_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function getConfidenceBadgeAttribute(): string
    {
        return match ($this->confidence) {
            'high'   => 'badge-light-success',
            'medium' => 'badge-light-warning',
            default  => 'badge-light-secondary',
        };
    }
}
