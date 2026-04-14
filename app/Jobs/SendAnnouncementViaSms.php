<?php

namespace App\Jobs;

use App\Models\BroadcastRecipient;
use App\Models\Tenant;
use App\Services\TenantSms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAnnouncementViaSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 30;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $phone,
        public readonly string $message,
        public readonly ?int $recipientId = null,
    ) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);

        if (! $tenant) {
            return;
        }

        tenancy()->initialize($tenant);

        try {
            TenantSms::send($this->phone, $this->message, $this->tenantId);

            if ($this->recipientId) {
                $recipient = BroadcastRecipient::find($this->recipientId);
                if ($recipient) {
                    $recipient->markAsSent();
                }
            }
        } finally {
            tenancy()->end();
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SendAnnouncementViaSms failed for phone={$this->phone}: ".$e->getMessage());

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
            Log::error('SendAnnouncementViaSms::failed() could not update recipient: '.$inner->getMessage());
        }
    }
}
