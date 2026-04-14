<?php

namespace App\Jobs;

use App\Models\BroadcastRecipient;
use App\Models\Tenant;
use App\Services\TenantWhatsApp;
use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAnnouncementViaWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public int $maxExceptions = 3;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $phone,
        public readonly string $message,
        public readonly ?int $recipientId = null,
        public readonly ?string $mediaType = null,
        public readonly ?string $mediaPath = null,
        public readonly ?string $templateName = null,
        public readonly ?array $templateParameters = null,
        public readonly string $templateLanguage = 'en',
    ) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);

        if (! $tenant) {
            return;
        }

        tenancy()->initialize($tenant);

        try {
            // Priority: Template > Media > Text
            if ($this->templateName) {
                $result = TenantWhatsApp::sendTemplate(
                    $this->phone,
                    $this->templateName,
                    $this->templateParameters ?? [],
                    $this->tenantId,
                    $this->templateLanguage
                );
            } elseif ($this->mediaType && $this->mediaPath) {
                // Upload the private file to Meta's Media API, then send by media_id
                // so the file never needs to be on a public URL.
                $absolutePath = Storage::disk('local')->path($this->mediaPath);
                $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';

                $upload = TenantWhatsApp::uploadMedia($absolutePath, $mimeType, $this->tenantId);

                if (! $upload['success'] || empty($upload['media_id'])) {
                    $result = ['success' => false, 'error' => $upload['error'] ?? 'Media upload failed'];
                } else {
                    $result = TenantWhatsApp::sendMediaById(
                        $this->phone,
                        $this->mediaType,
                        $upload['media_id'],
                        $this->message,
                        $this->tenantId
                    );
                }
            } else {
                $result = TenantWhatsApp::send($this->phone, $this->message, $this->tenantId);
            }

            if ($this->recipientId) {
                $recipient = BroadcastRecipient::find($this->recipientId);
                if ($recipient) {
                    if ($result['success']) {
                        $recipient->markAsSent($result['message_id'] ?? null);
                    } else {
                        $recipient->markAsFailed($result['error'] ?? 'Unknown error');
                    }
                }
            }
        } finally {
            tenancy()->end();
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendAnnouncementViaWhatsApp failed for phone='.$this->phone.': '.$e->getMessage());

        if (! $this->recipientId) {
            return;
        }

        try {
            $tenant = Tenant::find($this->tenantId);
            if (! $tenant) {
                return;
            }
            tenancy()->initialize($tenant);
            try {
                $recipient = BroadcastRecipient::find($this->recipientId);
                if ($recipient) {
                    $recipient->markAsFailed($e->getMessage());
                }
            } finally {
                tenancy()->end();
            }
        } catch (\Throwable $inner) {
            Log::error('SendAnnouncementViaWhatsApp::failed() could not update recipient: '.$inner->getMessage());
        }
    }
}
