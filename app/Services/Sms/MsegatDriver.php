<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MsegatDriver implements SmsDriverInterface
{
    private const ENDPOINT = 'https://www.msegat.com/gw/sendsms.php';

    // Response codes that indicate success
    private const SUCCESS_CODES = ['1', 'M0000'];

    public function __construct(
        private readonly string $apiKey,
        private readonly string $userName,
        private readonly string $userSender,
    ) {}

    public function send(string $to, string $message): void
    {
        $response = Http::withOptions($this->httpOptions())
            ->post(self::ENDPOINT, [
                'userName'    => $this->userName,
                'apiKey'      => $this->apiKey,
                'userSender'  => $this->userSender,
                'numbers'     => self::normalize($to),
                'msg'         => $message,
                'msgEncoding' => 'UTF8',
            ]);

        $body = trim($response->body());

        if (! in_array($body, self::SUCCESS_CODES, true)) {
            throw new RuntimeException("Msegat error ({$body}): " . self::errorMessage($body));
        }
    }

    /**
     * Msegat expects international format without leading zeros or +.
     * 0096650047XXXX → 96650047XXXX
     * +96650047XXXX  → 96650047XXXX
     */
    private static function normalize(string $phone): string
    {
        $phone = trim($phone);

        if (str_starts_with($phone, '00')) {
            return substr($phone, 2);
        }

        if (str_starts_with($phone, '+')) {
            return substr($phone, 1);
        }

        return $phone;
    }

    private static function errorMessage(string $code): string
    {
        return match ($code) {
            'M0001', '1010' => 'Missing required variables.',
            'M0002', '1020' => 'Invalid login credentials.',
            'M0003', '1050' => 'Message body is empty.',
            'M0004', '0000', '1060' => 'Insufficient balance.',
            '1061'           => 'Duplicate message.',
            '1110'           => 'Sender name is missing or incorrect.',
            '0010', '1120'  => 'Invalid mobile number.',
            '1140'           => 'Message is too long.',
            'M0022'          => 'Exceeded allowed number of senders.',
            'M0023'          => 'Sender name is under review or refused.',
            default          => 'Unknown error.',
        };
    }

    private function httpOptions(): array
    {
        if (! config('mail.mailers.smtp.verify_peer', true)) {
            return ['verify' => false];
        }

        return [];
    }
}
