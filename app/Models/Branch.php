<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Branch extends Model
{
    use BelongsToTenant;
    protected $fillable = ['tenant_id', 'school_id', 'name', 'code', 'status'];

    protected $casts = ['tenant_id' => 'string'];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('title');
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }
}
