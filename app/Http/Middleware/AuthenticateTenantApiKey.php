<?php

namespace App\Http\Middleware;

use App\Models\ApiRequestLog;
use App\Models\TenantApiKey;
use App\Services\PlanService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateTenantApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        // Security hardening: reject oversized payloads
        if ($request->header('Content-Length') && (int) $request->header('Content-Length') > 65536) {
            return response()->json(['error' => 'Request payload too large. Max 64 KB.'], 413);
        }

        // Security hardening: require JSON content type for mutation methods
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH']) && ! $request->isJson()) {
            return response()->json(['error' => 'Content-Type must be application/json.'], 415);
        }

        $apiKey    = null;
        $startTime = microtime(true);

        // Capture request body once (before the stream is consumed)
        $requestBody = $request->isJson() ? ($request->json()->all() ?: null) : null;

        return tap($this->authenticate($request, $next, $apiKey), function (Response $response) use ($request, &$apiKey, $startTime, $requestBody) {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);
            $tenantId   = $apiKey?->tenant_id ?? '';

            // Decode response body for logging (JSON responses only)
            $responseBody = null;
            $responseContent = $response->getContent();
            if ($responseContent) {
                $decoded = json_decode($responseContent, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $responseBody = $decoded;
                }
            }

            ApiRequestLog::record(
                tenantId:     $tenantId,
                apiKeyId:     $apiKey?->id ?? null,
                endpoint:     $request->method() . ' ' . $request->path(),
                statusCode:   $response->getStatusCode(),
                ip:           $request->ip(),
                durationMs:   $durationMs,
                requestBody:  $requestBody,
                responseBody: $responseBody,
            );
        });
    }

    private function authenticate(Request $request, Closure $next, ?TenantApiKey &$apiKey): Response
    {
        $failKey = 'api_fail:' . $request->ip();

        // Check if IP is already blocked for too many failures
        if (RateLimiter::tooManyAttempts($failKey, 10)) {
            return response()->json(['error' => 'Too many failed requests from your IP.'], 429);
        }

        // 1. Extract Bearer token
        $token = $request->bearerToken();
        if (! $token) {
            RateLimiter::hit($failKey, 60);
            return response()->json(['error' => 'Unauthenticated. Provide Authorization: Bearer <key>'], 401);
        }

        // 2. Lookup by hash
        $hash      = hash('sha256', $token);
        $foundKey  = TenantApiKey::where('key_hash', $hash)->first();
        if (! $foundKey) {
            RateLimiter::hit($failKey, 60);
            return response()->json(['error' => 'Invalid API key.'], 401);
        }

        // Assign to the reference so it's available for logging even on subsequent failures
        $apiKey = $foundKey;

        // 3. Check active
        if ($apiKey->revoked_at) {
            RateLimiter::hit($failKey, 60);
            return response()->json(['error' => 'API key has been revoked.'], 401);
        }
        if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
            RateLimiter::hit($failKey, 60);
            return response()->json(['error' => 'API key has expired.'], 401);
        }

        // 4. Resolve tenant
        $tenant = \App\Models\Tenant::find($apiKey->tenant_id);
        if (! $tenant) {
            return response()->json(['error' => 'Tenant not found.'], 500);
        }

        // 5. Initialize tenancy
        tenancy()->initialize($tenant);

        // 6. Check plan api_access feature
        $plan = PlanService::forCurrentTenant();
        if (! $plan->allows('api_access')) {
            return response()->json([
                'error' => "The 'api_access' feature is not available on your current plan ({$plan->planName()}).",
            ], 403);
        }

        // 7. Check daily rate limit by plan
        $dailyLimit = $plan->apiDailyLimit();
        if ($dailyLimit !== null && $dailyLimit > 0) {
            $usedToday = DB::table('api_request_logs')
                ->where('tenant_id', $tenant->id)
                ->where('api_key_id', $apiKey->id)
                ->whereDate('created_at', today())
                ->whereNotIn('status_code', [401, 403])
                ->count();

            if ($usedToday >= $dailyLimit) {
                return response()->json([
                    'error'  => 'Daily API limit reached.',
                    'limit'  => $dailyLimit,
                    'used'   => $usedToday,
                    'resets' => now()->endOfDay()->toIso8601String(),
                ], 429);
            }
        }

        // 8. Touch last_used_at (direct query — no model events)
        DB::table('tenant_api_keys')->where('id', $apiKey->id)->update(['last_used_at' => now()]);

        // 9. Store on request for controller use
        $request->attributes->set('api_key', $apiKey);

        // Clear failure counter on successful auth
        RateLimiter::clear($failKey);

        return $next($request);
    }
}
