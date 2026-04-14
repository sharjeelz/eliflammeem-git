<?php

namespace App\Services\WhatsApp;

use App\Models\BroadcastRecipient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaCloudDriver implements WhatsAppDriverInterface
{
    public function __construct(
        private readonly string $phoneNumberId,
        private readonly string $accessToken,
    ) {}

    public function send(string $to, string $message): array
    {
        $url = $this->getApiUrl();

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];

        return $this->makeRequest($url, $payload);
    }

    public function sendMedia(string $to, string $mediaType, string $mediaPath, ?string $caption = null): array
    {
        $url = $this->getApiUrl();

        // Generate public URL for the media - use asset() helper
        $mediaUrl = asset('storage/'.$mediaPath);

        $mediaPayload = [
            'link' => $mediaUrl,
        ];

        // Add caption for supported types
        if ($caption && in_array($mediaType, ['image', 'document', 'video'])) {
            $mediaPayload['caption'] = $caption;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => $mediaType,
            $mediaType => $mediaPayload,
        ];

        return $this->makeRequest($url, $payload);
    }

    public function sendMediaById(string $to, string $mediaType, string $mediaId, ?string $caption = null): array
    {
        $url = $this->getApiUrl();

        $mediaPayload = ['id' => $mediaId];

        if ($caption && in_array($mediaType, ['image', 'document', 'video'])) {
            $mediaPayload['caption'] = $caption;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => $mediaType,
            $mediaType => $mediaPayload,
        ];

        return $this->makeRequest($url, $payload);
    }

    public function sendTemplate(string $to, string $templateName, array $parameters = [], string $language = 'en'): array
    {
        $url = $this->getApiUrl();

        // Build components array for template parameters
        $components = [];

        if (! empty($parameters)) {
            $parameterObjects = [];
            foreach ($parameters as $index => $value) {
                $parameterObjects[] = [
                    'type' => 'text',
                    'text' => (string) $value,
                ];
            }

            $components[] = [
                'type' => 'body',
                'parameters' => $parameterObjects,
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $language,
                ],
            ],
        ];

        if (! empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $this->makeRequest($url, $payload);
    }

    public function uploadMedia(string $filePath, string $mimeType): array
    {
        $url = $this->getMediaUploadUrl();

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
            ])
                ->attach('file', file_get_contents($filePath), basename($filePath))
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'type' => $mimeType,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'media_id' => $data['id'] ?? null,
                ];
            }

            $error = $response->json();
            Log::error('WhatsApp media upload failed', [
                'status' => $response->status(),
                'error' => $error,
            ]);

            return [
                'success' => false,
                'error' => $error['error']['message'] ?? 'Unknown error',
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp media upload exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function verifyWebhook(string $mode, string $token, string $verifyToken): bool
    {
        return $mode === 'subscribe' && $token === $verifyToken;
    }

    public function processWebhookPayload(array $payload): void
    {
        // Meta sends webhooks in this format:
        // {
        //   "object": "whatsapp_business_account",
        //   "entry": [{ "changes": [{ "value": { "statuses": [...] } }] }]
        // }

        if (! isset($payload['entry'])) {
            return;
        }

        foreach ($payload['entry'] as $entry) {
            if (! isset($entry['changes'])) {
                continue;
            }

            foreach ($entry['changes'] as $change) {
                if (! isset($change['value']['statuses'])) {
                    continue;
                }

                foreach ($change['value']['statuses'] as $status) {
                    $this->processStatusUpdate($status);
                }
            }
        }
    }

    private function processStatusUpdate(array $status): void
    {
        $messageId = $status['id'] ?? null;
        $statusType = $status['status'] ?? null; // sent, delivered, read, failed

        if (! $messageId || ! $statusType) {
            return;
        }

        $recipient = BroadcastRecipient::where('message_id', $messageId)->first();

        if (! $recipient) {
            Log::warning('WhatsApp webhook: recipient not found for message_id', [
                'message_id' => $messageId,
            ]);

            return;
        }

        // Update delivery status
        $updates = ['delivery_status' => $statusType];

        switch ($statusType) {
            case 'sent':
                // Message sent to WhatsApp server
                break;
            case 'delivered':
                $updates['delivered_at'] = now();
                break;
            case 'read':
                $updates['read_at'] = now();
                if (! $recipient->delivered_at) {
                    $updates['delivered_at'] = now();
                }
                break;
            case 'failed':
                $errorMessage = $status['errors'][0]['title'] ?? 'Delivery failed';
                $updates['error_message'] = $errorMessage;
                $recipient->markAsFailed($errorMessage);

                return; // Don't update delivery_status for failed messages
        }

        $recipient->update($updates);

        Log::info('WhatsApp webhook: status updated', [
            'message_id' => $messageId,
            'status' => $statusType,
            'recipient_id' => $recipient->id,
        ]);
    }

    private function makeRequest(string $url, array $payload): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'message_id' => $data['messages'][0]['id'] ?? null,
                ];
            }

            $error = $response->json();
            Log::error('WhatsApp send failed', [
                'status' => $response->status(),
                'error' => $error,
            ]);

            return [
                'success' => false,
                'error' => $error['error']['message'] ?? 'Unknown error',
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp send exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function getApiUrl(): string
    {
        $version = config('services.whatsapp.api_version', 'v18.0');

        return "https://graph.facebook.com/{$version}/{$this->phoneNumberId}/messages";
    }

    private function getMediaUploadUrl(): string
    {
        $version = config('services.whatsapp.api_version', 'v18.0');

        return "https://graph.facebook.com/{$version}/{$this->phoneNumberId}/media";
    }

    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (! str_starts_with($phone, '+')) {
            if (strlen($phone) <= 10) {
                $phone = '966'.$phone;
            }
            $phone = '+'.$phone;
        }

        return $phone;
    }
}
