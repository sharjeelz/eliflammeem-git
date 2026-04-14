<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Facades\Config;

class TenantMailer
{
    /**
     * Apply the tenant's custom SMTP config (if configured) and return
     * true so the caller knows to use the 'tenant_smtp' mailer.
     * Returns false when the school has no custom SMTP — use global default.
     */
    public static function configure(string $tenantId): bool
    {
        $school = School::where('tenant_id', $tenantId)->first();

        if (! $school || ! $school->setting('smtp_enabled')) {
            return false;
        }

        $host = $school->setting('smtp_host');
        if (! $host) {
            return false;
        }

        $password = self::decrypt($school->setting('smtp_password'));

        Config::set('mail.mailers.tenant_smtp', [
            'transport'   => 'smtp',
            'host'        => $host,
            'port'        => (int) $school->setting('smtp_port', 587),
            'encryption'  => $school->setting('smtp_encryption', 'tls') ?: null,
            'username'    => $school->setting('smtp_username'),
            'password'    => $password,
            'timeout'     => 15,
            'verify_peer' => config('mail.mailers.smtp.verify_peer', true),
        ]);

        Config::set('mail.from.address',
            $school->setting('smtp_from_address') ?: config('mail.from.address'));
        Config::set('mail.from.name',
            $school->setting('smtp_from_name') ?: config('mail.from.name'));

        // Purge any cached instance so the next resolve picks up new config
        app('mail.manager')->purge('tenant_smtp');

        return true;
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
}
