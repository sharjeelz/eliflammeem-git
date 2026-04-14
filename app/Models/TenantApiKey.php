<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TenantApiKey extends Model
{
    // No BelongsToTenant — this is a central table

    protected $fillable = [
        'tenant_id',
        'name',
        'key_prefix',
        'key_hash',
        'created_by',
        'last_used_at',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
        'revoked_at'   => 'datetime',
    ];

    /**
     * Whether this key is currently active (not revoked, not expired).
     */
    public function isActive(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Generate a new API key, save to DB, and return the plaintext + model.
     *
     * Token format: slyk_ + first 8 chars of tenantId (no dashes) + _ + 80 hex random chars
     * Stored: key_prefix = first 16 chars of plaintext, key_hash = sha256(plaintext)
     *
     * @return array{plaintext: string, model: TenantApiKey}
     */
    public static function generate(string $tenantId, string $name, ?int $createdBy = null): array
    {
        $uuidCompact = str_replace('-', '', $tenantId);
        $prefix8     = substr($uuidCompact, 0, 8);
        $random80    = bin2hex(random_bytes(40)); // 40 bytes = 80 hex chars

        $plaintext = 'slyk_' . $prefix8 . '_' . $random80;
        $keyPrefix = substr($plaintext, 0, 16);
        $keyHash   = hash('sha256', $plaintext);

        $model = static::create([
            'tenant_id'  => $tenantId,
            'name'       => $name,
            'key_prefix' => $keyPrefix,
            'key_hash'   => $keyHash,
            'created_by' => $createdBy,
        ]);

        return [
            'plaintext' => $plaintext,
            'model'     => $model,
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function requestLogs(): HasMany
    {
        return $this->hasMany(ApiRequestLog::class, 'api_key_id')->orderByDesc('created_at');
    }
}
