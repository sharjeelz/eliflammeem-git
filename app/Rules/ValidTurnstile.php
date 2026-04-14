<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValidTurnstile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (app()->environment('testing', 'local')) {
            return; // skip during automated tests and local dev
        }

        $secret = config('services.turnstile.secret_key');

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret'   => $secret,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

            if ($response->successful() && ($response->json('success') === true)) {
                return;
            }

            Log::info('Turnstile failed', [
                'codes' => $response->json('error-codes'),
                'ip'    => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Turnstile verification error: ' . $e->getMessage());
            // On network failure — fail open (don't block legitimate users)
            return;
        }

        $fail('Security check failed. Please try again.');
    }
}
