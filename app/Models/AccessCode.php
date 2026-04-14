<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class AccessCode extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'roster_contact_id', 'branch_id', 'code',
        'channel', 'expires_at', 'used_at', 'sent_at', 'send_error', 'meta',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
        'sent_at'    => 'datetime',
        'meta'       => 'array',
    ];

    public function contact()
    {
        return $this->belongsTo(RosterContact::class, 'roster_contact_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeActive($q)
    {
        return $q->whereNull('used_at')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }
}
