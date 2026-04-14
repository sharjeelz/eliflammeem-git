<?php

namespace App\Services;

use App\Models\School;
use App\Services\Sms\MsegatDriver;
use App\Services\Sms\SmsDriverInterface;
use App\Services\Sms\TwilioDriver;

class TenantSms
{
    /**
     * Send an SMS using the school's configured provider.
     * Throws if no provider is configured or the send fails.
     */
    public static function send(string $to, string $message, string $tenantId): void
    {
        self::driver($tenantId)->send($to, $message);
    }

    /**
     * Returns true when the school has a fully-configured SMS provider.
     */
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

    /**
     * Resolve the driver for the given tenant, or return null if not configured.
     */
    private static function driver(string $tenantId): ?SmsDriverInterface
    {
        $school = School::where('tenant_id', $tenantId)->first();

        if (! $school) {
            return null;
        }

        return match ($school->setting('sms_provider')) {
            'twilio' => self::twilio($school),
            'msegat' => self::msegat($school),
            default  => null,
        };
    }

    private static function twilio(School $school): ?TwilioDriver
    {
        $sid   = $school->setting('twilio_sid');
        $token = self::decrypt($school->setting('twilio_token'));
        $from  = $school->setting('twilio_from');

        if (! $sid || ! $token || ! $from) {
            return null;
        }

        return new TwilioDriver($sid, $token, $from);
    }

    private static function msegat(School $school): ?MsegatDriver
    {
        $apiKey     = self::decrypt($school->setting('msegat_api_key'));
        $userName   = $school->setting('msegat_username');
        $userSender = $school->setting('msegat_sender');

        if (! $apiKey || ! $userName || ! $userSender) {
            return null;
        }

        return new MsegatDriver($apiKey, $userName, $userSender);
    }
}
