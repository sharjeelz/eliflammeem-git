<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use BelongsToTenant, HasFactory, HasRoles, Notifiable, SoftDeletes, \Laravel\Fortify\TwoFactorAuthenticatable;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'last_login',
        'last_login_ip',
        'last_login_user_agent',
        'login_count',
        'active_session_id',
        'phone_number',
        'address',
        'account_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'        => 'datetime',
            'last_login'               => 'datetime',
            'last_login_ip'            => 'string',
            'last_login_user_agent'    => 'string',
            'login_count'              => 'integer',
            'password'                 => 'hashed',
            'tenant_id'                => 'string',
            'phone_number'             => 'string',
            'address'                  => 'string',
            'account_id'               => 'string',
            'deleted_at'               => 'datetime',
            'two_factor_confirmed_at'  => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (empty($user->account_id)) {
                $user->account_id = 'ID-'.Str::upper(Str::random(4));
            }
        });
    }

    public function branches()
    {
        return $this->belongsToMany(\App\Models\Branch::class)
            ->withTimestamps()
            ->withPivot('tenant_id', 'title');
    }

    public function assignedIssues()
    {
        return $this->hasMany(\App\Models\Issue::class, 'assigned_user_id');
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }

    public function primaryBranchId(): ?int
    {
        return $this->branches()->pluck('branches.id')->first();
    }

    public function primaryBranchName(): ?string
    {
        return $this->branches()->pluck('branches.name')->first();
    }

    public static function defaultAssigneeForBranch(?int $branchId): ?self
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(tenant('id'));

        if ($branchId) {
            return static::role('branch_manager')
                ->where('tenant_id', tenant('id'))
                ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
                ->orderBy('id')
                ->first();
        }

        return null;
    }

    public function categories()
    {
        return $this->belongsToMany(IssueCategory::class, 'issue_category_user')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    public function scopeWhereRole($query, $role)
    {
        return $query->role($role);
    }

    /**
     * Send the password reset notification using our custom branded mail.
     */
    public function sendPasswordResetNotification($token): void
    {
        $resetUrl = url(route('tenant.admin.password.reset', [
            'token' => $token,
            'email' => $this->email,
        ], false));

        \Illuminate\Support\Facades\Mail::to($this->email)->queue(
            new \App\Mail\PasswordResetMail(
                name:       $this->name,
                email:      $this->email,
                resetUrl:   $resetUrl,
                schoolName: tenant()->name ?? config('app.name'),
                tenantId:   tenant('id') ?? '',
            )
        );
    }
}
