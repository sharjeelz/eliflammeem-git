<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ApiRequestLog extends Model
{
    // Central table — no BelongsToTenant trait
    public $timestamps = false;

    const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'api_key_id',
        'endpoint',
        'request_body',
        'response_body',
        'status_code',
        'ip',
        'duration_ms',
        'created_at',
    ];

    protected $casts = [
        'request_body'  => 'array',
        'response_body' => 'array',
        'created_at'    => 'datetime',
    ];

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(TenantApiKey::class, 'api_key_id');
    }

    /**
     * Record an API request log entry using a direct DB insert to avoid
     * any global scopes or model events.
     */
    public static function record(
        string $tenantId,
        ?int $apiKeyId,
        string $endpoint,
        int $statusCode,
        string $ip,
        ?int $durationMs,
        ?array $requestBody = null,
        ?array $responseBody = null,
    ): void {
        DB::table('api_request_logs')->insert([
            'tenant_id'     => $tenantId,
            'api_key_id'    => $apiKeyId,
            'endpoint'      => substr($endpoint, 0, 100),
            'request_body'  => $requestBody  !== null ? json_encode($requestBody)  : null,
            'response_body' => $responseBody !== null ? json_encode($responseBody) : null,
            'status_code'   => $statusCode,
            'ip'            => $ip,
            'duration_ms'   => $durationMs,
            'created_at'    => now(),
        ]);
    }
}
