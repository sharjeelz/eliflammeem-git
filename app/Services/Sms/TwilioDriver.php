<?php

namespace App\Services\Sms;

use Twilio\Http\CurlClient as TwilioCurlClient;
use Twilio\Rest\Client as TwilioClient;

class TwilioDriver implements SmsDriverInterface
{
    public function __construct(
        private readonly string $sid,
        private readonly string $token,
        private readonly string $from,
    ) {}

    public function send(string $to, string $message): void
    {
        $httpClient = $this->curlClient();
        $twilio     = new TwilioClient($this->sid, $this->token, null, null, $httpClient);

        $twilio->messages->create(self::e164($to), [
            'from' => $this->from,
            'body' => $message,
        ]);
    }

    /**
     * Normalise to E.164 (+XXXXXXXXXXX) — Twilio's required format.
     */
    private static function e164(string $phone): string
    {
        $phone = trim($phone);

        if (str_starts_with($phone, '00')) {
            return '+' . substr($phone, 2);
        }

        return $phone; // already + prefixed or unknown — pass through
    }

    private function curlClient(): TwilioCurlClient
    {
        $options = [];

        if (! config('mail.mailers.smtp.verify_peer', true)) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = 0;
        }

        return new TwilioCurlClient($options);
    }
}
