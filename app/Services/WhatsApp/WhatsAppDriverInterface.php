<?php

namespace App\Services\WhatsApp;

interface WhatsAppDriverInterface
{
    public function send(string $to, string $message): array;

    public function sendMedia(string $to, string $mediaType, string $mediaPath, ?string $caption = null): array;

    public function sendMediaById(string $to, string $mediaType, string $mediaId, ?string $caption = null): array;

    public function sendTemplate(string $to, string $templateName, array $parameters = [], string $language = 'en'): array;

    public function uploadMedia(string $filePath, string $mimeType): array;

    public function verifyWebhook(string $mode, string $token, string $verifyToken): bool;

    public function processWebhookPayload(array $payload): void;
}
