<?php

namespace App\Services\Sms;

interface SmsDriverInterface
{
    public function send(string $to, string $message): void;
}
