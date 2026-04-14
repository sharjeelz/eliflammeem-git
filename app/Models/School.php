<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class School extends Model
{
    protected $fillable = ['tenant_id', 'name', 'code', 'city', 'status', 'logo', 'settings'];

    protected $casts = [
        'tenant_id' => 'string',
        'settings'  => 'array',
    ];

    /** Get a value from the settings JSON column with an optional default. */
    public function setting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::disk('logos')->url($this->logo) : null;
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }
}
