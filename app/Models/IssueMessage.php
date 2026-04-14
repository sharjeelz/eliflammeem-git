<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class IssueMessage extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'issue_id',
        'sender',    // 'parent' | 'teacher' | 'admin' | 'system'
        'message',
        'is_internal',
        'meta',
        'author_type',
        'author_id',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_internal' => 'boolean',
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function attachments()
    {
        return $this->hasMany(IssueAttachment::class);
    }

    public function author()
    {
        return $this->morphTo();
    }

    public function authorDisplayName(): string
    {
        if ($this->author) {
            // Users likely have 'name'; parents (roster contacts) might too
            return $this->author->name
                ?? $this->author->full_name
                ?? $this->author->contact_name
                ?? ucfirst($this->sender);
        }

        return $this->meta['actor_name'] ?? ucfirst($this->sender ?? 'system');
    }

    public function getSenderLabelAttribute(): string
    {
        return match ($this->sender) {
            'admin' => 'Admin',
            'branch_manager' => 'Branch Manager',
            'staff' => 'Staff',
            'parent' => 'Parent',
            'teacher' => 'Teacher',
            'system' => 'System',
            default => ucfirst($this->sender ?? 'Unknown'),
        };
    }
}
