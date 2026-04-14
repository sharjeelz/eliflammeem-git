<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushService
{
    public static function send(string $expoPushToken, string $title, string $body, array $data = []): void
    {
        if (! str_starts_with($expoPushToken, 'ExponentPushToken[')) {
            return;
        }

        try {
            Http::timeout(5)->post('https://exp.host/--/api/v2/push/send', [
                'to'    => $expoPushToken,
                'sound' => 'default',
                'title' => $title,
                'body'  => $body,
                'data'  => $data,
            ]);
        } catch (\Throwable $e) {
            Log::warning('ExpoPushService: failed to send push notification', [
                'token' => $expoPushToken,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
