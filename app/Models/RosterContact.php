<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class RosterContact extends Model
{
    use BelongsToTenant, HasApiTokens;

    protected $guarded = [];

    protected $casts = [
        'meta'             => 'array',
        'spam_pardoned_at' => 'datetime',
        'deactivated_at'   => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->whereNull('deactivated_at');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function accessCodes()
    {
        return $this->hasMany(AccessCode::class);
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }
}
