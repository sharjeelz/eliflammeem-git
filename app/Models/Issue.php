<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Issue extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'school_id',
        'branch_id',
        'title',
        'public_id',
        'description',
        'priority',
        'status',
        'ai_summary',
        'meta',
        'assigned_user_id',
        'first_response_at',
        'resolved_at',
        'sla_hours',
        'sla_due_at',
        'category_id',
        'last_activity_at',
        'source_role',
        'roster_contact_id',
        'issue_category_id',
        'is_spam',
        'spam_reason',
        'is_anonymous',
        'submission_type',
        'status_entered_at',
        'reopen_token',
        'close_note',
        'close_reason',
    ];

    protected $casts = [
        'ai_summary' => 'array',
        'meta' => 'array',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'sla_due_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_spam'          => 'boolean',
        'is_anonymous'     => 'boolean',
        'submission_type'  => 'string',
        'status_entered_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function escalations()
    {
        return $this->hasMany(IssueEscalation::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function messages()
    {
        return $this->hasMany(IssueMessage::class);
    }

    public function attachments()
    {
        return $this->hasMany(IssueAttachment::class);
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }

    public function issueCategory()
    {
        return $this->belongsTo(IssueCategory::class, 'issue_category_id', 'id')->withDefault();
    }

    public static function statuses(): array
    {
        return ['new', 'in_progress', 'resolved', 'closed'];
    }

    public static function priorities(): array
    {
        return ['low', 'medium', 'high', 'urgent'];
    }

    public static function allowedTransitions(): array
    {
        return [
            'new' => ['in_progress', 'resolved', 'closed'],
            'in_progress' => ['resolved', 'closed'],
            'resolved' => ['in_progress', 'closed'], // reopen
            'closed' => ['in_progress'],          // reopen
        ];
    }

    public function canTransitionTo(string $to): bool
    {
        return in_array($to, static::allowedTransitions()[$this->status] ?? [], true);
    }

    // Visibility scopes (admin: all; branch_manager: own branch; staff: assigned only)
    public function scopeVisibleTo(Builder $q, User $user): Builder
    {
        if ($user->hasRole('admin')) {
            return $q;
        }
        if ($user->hasRole('branch_manager')) {
            $branchIds = $user->relationLoaded('branches')
                ? $user->branches->pluck('id')
                : $user->branches()->pluck('id');

            return $q->whereIn('branch_id', $branchIds)
                ->where('is_anonymous', false);
        }

        return $q->where('assigned_user_id', $user->id);
    }

    public function scopeSpam(Builder $q): Builder
    {
        return $q->where('is_spam', true);
    }

    public function scopeNotSpam(Builder $q): Builder
    {
        return $q->where('is_spam', false);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function activities()
    {
        return $this->hasMany(\App\Models\IssueActivity::class);
    }

    public function branches()
    {
        return $this->belongsToMany(\App\Models\Branch::class)
            ->withTimestamps()
            ->withPivot('tenant_id', 'title');
    }

    public function roasterContact()
    {
        return $this->belongsTo(RosterContact::class, 'roster_contact_id', 'id');
    }

    public function aiAnalysis()
    {
        // Prefer the richer 'full' analysis; fall back to legacy 'sentiment'
        return $this->hasOne(IssueAiAnalysis::class)
            ->whereIn('analysis_type', ['full', 'sentiment'])
            ->orderByRaw("CASE analysis_type WHEN 'full' THEN 0 ELSE 1 END, updated_at DESC");
    }
}
