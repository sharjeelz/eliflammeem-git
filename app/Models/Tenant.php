<?php

// app/Models/Tenant.php

namespace App\Models;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasDomains;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'plan',
        'subscription_starts_at',
        'subscription_ends_at',
        'contract_type',
        'contract_file_url',
        'registration_status',
        'data',
        'terms_accepted_at',
        'terms_accepted_ip',
        'terms_accepted_version',
    ];

    /**
     * Tell Stancl these are real DB columns — not to be stored inside the JSON `data` column.
     * By default BaseTenant only excludes 'id'; everything else goes into data.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'phone',
            'email',
            'plan',
            'subscription_starts_at',
            'subscription_ends_at',
            'contract_type',
            'contract_file_url',
            'data',
            'terms_accepted_at',
            'terms_accepted_ip',
            'terms_accepted_version',
            'created_at',
            'updated_at',
        ];
    }

    protected $casts = [
        'data'                   => 'array',
        'subscription_starts_at' => 'date',
        'subscription_ends_at'   => 'date',
        'terms_accepted_at'      => 'datetime',
    ];

    /** Days remaining in the current subscription period. Null if no end date set. */
    public function subscriptionDaysRemaining(): ?int
    {
        if (! $this->subscription_ends_at) {
            return null;
        }

        return (int) max(0, now()->startOfDay()->diffInDays($this->subscription_ends_at, false));
    }

    /** True if the subscription end date is in the past. */
    public function subscriptionExpired(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isPast();
    }

    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }

    public function issueCategories()
    {
        return $this->hasMany(\App\Models\IssueCategory::class, 'tenant_id', 'id');
    }

    protected static function booted()
    {
        static::deleting(function ($tenant) {
            // Delete all roles and permissions for this tenant
            Role::where('tenant_id', $tenant->id)->delete();
            Permission::where('tenant_id', $tenant->id)->delete();
            IssueCategory::where('tenant_id', $tenant->id)->delete();
            User::where('tenant_id', $tenant->id)->delete();
            Issue::where('tenant_id', $tenant->id)->delete();
        });

    }

    public function branches()
    {
        return $this->hasMany(\App\Models\Branch::class, 'tenant_id', 'id');
    }

    public function apiKeys()
    {
        return $this->hasMany(\App\Models\TenantApiKey::class, 'tenant_id', 'id');
    }
}
