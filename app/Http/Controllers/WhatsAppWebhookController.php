<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Services\TenantWhatsApp;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Resolve the tenant ID from an opaque webhook ID stored in school settings.
     * This prevents tenant enumeration via attacker-controlled query params.
     */
    private function resolveTenantId(string $webhookId): ?string
    {
        // Look up the school whose whatsapp_webhook_id matches this opaque token
        $school = School::withoutGlobalScopes()
            ->where('settings->whatsapp_webhook_id', $webhookId)
            ->first();

        return $school?->tenant_id;
    }

    /**
     * Handle webhook verification (GET request from Meta).
     */
    public function verify(Request $request, string $webhookId): Response
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $tenantId = $this->resolveTenantId($webhookId);

        if (! $tenantId) {
            Log::warning('WhatsApp webhook verification: invalid webhook ID');

            return response('Forbidden', 403);
        }

        if ($mode && $token && TenantWhatsApp::verifyWebhook($mode, $token, $tenantId)) {
            Log::info('WhatsApp webhook verified successfully', ['tenant_id' => $tenantId]);

            return response($challenge, 200);
        }

        Log::warning('WhatsApp webhook verification failed', [
            'tenant_id' => $tenantId,
            'mode'      => $mode,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle webhook payload (POST request from Meta with status updates).
     */
    public function handle(Request $request, string $webhookId): Response
    {
        // Verify Meta's HMAC-SHA256 signature before touching the payload
        $appSecret = config('services.whatsapp.app_secret');
        $signature = $request->header('X-Hub-Signature-256');

        if (! $appSecret || ! $signature) {
            Log::warning('WhatsApp webhook: missing app secret or signature header');

            return response('Forbidden', 403);
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret);

        if (! hash_equals($expected, $signature)) {
            Log::warning('WhatsApp webhook: invalid HMAC signature');

            return response('Forbidden', 403);
        }

        $tenantId = $this->resolveTenantId($webhookId);

        if (! $tenantId) {
            Log::warning('WhatsApp webhook: invalid webhook ID');

            return response('OK', 200); // Return 200 to prevent Meta retries
        }

        $payload = $request->all();

        Log::info('WhatsApp webhook received', [
            'tenant_id'   => $tenantId,
            'object'      => $payload['object'] ?? null,
            'entry_count' => count($payload['entry'] ?? []),
        ]);

        try {
            TenantWhatsApp::processWebhook($payload, $tenantId);
        } catch (\Throwable $e) {
            Log::error('WhatsApp webhook processing failed', [
                'error'     => $e->getMessage(),
                'tenant_id' => $tenantId,
            ]);
        }

        // Always return 200 to acknowledge receipt
        return response('OK', 200);
    }
}
