<?php

namespace App\Services;

use App\Models\School;
use App\Services\WhatsApp\MetaCloudDriver;
use App\Services\WhatsApp\WhatsAppDriverInterface;

class TenantWhatsApp
{
    public static function send(string $to, string $message, string $tenantId): array
    {
        $driver = self::driver($tenantId);

        if (! $driver) {
            return [
                'success' => false,
                'error' => 'WhatsApp is not configured for this school',
            ];
        }

        return $driver->send($to, $message);
    }

    public static function sendMedia(string $to, string $mediaType, string $mediaPath, ?string $caption, string $tenantId): array
    {
        $driver = self::driver($tenantId);

        if (! $driver) {
            return [
                'success' => false,
                'error' => 'WhatsApp is not configured for this school',
            ];
        }

        return $driver->sendMedia($to, $mediaType, $mediaPath, $caption);
    }

    public static function sendMediaById(string $to, string $mediaType, string $mediaId, ?string $caption, string $tenantId): array
    {
        $driver = self::driver($tenantId);

        if (! $driver) {
            return [
                'success' => false,
                'error' => 'WhatsApp is not configured for this school',
            ];
        }

        return $driver->sendMediaById($to, $mediaType, $mediaId, $caption);
    }

    public static function sendTemplate(string $to, string $templateName, array $parameters, string $tenantId, string $language = 'en'): array
    {
        $driver = self::driver($tenantId);

        if (! $driver) {
            return [
                'success' => false,
                'error' => 'WhatsApp is not configured for this school',
            ];
        }

        return $driver->sendTemplate($to, $templateName, $parameters, $language);
    }

    public static function uploadMedia(string $filePath, string $mimeType, string $tenantId): array
    {
        $driver = self::driver($tenantId);

        if (! $driver) {
            return [
                'success' => false,
                'error' => 'WhatsApp is not configured for this school',
            ];
        }

        return $driver->uploadMedia($filePath, $mimeType);
    }

    public static function isConfigured(string $tenantId): bool
    {
        return self::driver($tenantId) !== null;
    }

    public static function encrypt(string $value): string
    {
        return encrypt($value);
    }

    public static function decrypt(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        try {
            return decrypt($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function verifyWebhook(string $mode, string $token, string $tenantId): bool
    {
        $driver = self::driver($tenantId);

        if (! $driver) {
            return false;
        }

        $school = School::withoutGlobalScopes()->where('tenant_id', $tenantId)->first();
        $verifyToken = $school?->setting('whatsapp_webhook_verify_token');

        return $driver->verifyWebhook($mode, $token, $verifyToken ?? '');
    }

    public static function processWebhook(array $payload, string $tenantId): void
    {
        $driver = self::driver($tenantId);

        if (! $driver) {
            return;
        }

        $tenant = \App\Models\Tenant::find($tenantId);
        if (! $tenant) {
            return;
        }

        tenancy()->initialize($tenant);
        try {
            $driver->processWebhookPayload($payload);
        } finally {
            tenancy()->end();
        }
    }

    private static function driver(string $tenantId): ?WhatsAppDriverInterface
    {
        $school = School::withoutGlobalScopes()->where('tenant_id', $tenantId)->first();

        if (! $school) {
            return null;
        }

        $enabled = $school->setting('whatsapp_enabled');

        if (! $enabled) {
            return null;
        }

        $phoneNumberId = $school->setting('whatsapp_phone_number_id');
        $accessToken = self::decrypt($school->setting('whatsapp_access_token'));

        if (! $phoneNumberId || ! $accessToken) {
            return null;
        }

        return new MetaCloudDriver($phoneNumberId, $accessToken);
    }
}
