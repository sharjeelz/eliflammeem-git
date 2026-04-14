<?php

namespace App\Jobs;

use App\Mail\AnnouncementMail;
use App\Models\BroadcastRecipient;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAnnouncementViaMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public int $maxExceptions = 3;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $email,
        public readonly string $contactName,
        public readonly string $subject,
        public readonly string $body,
        public readonly string $schoolName,
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
            Mail::to($this->email)->send(new AnnouncementMail(
                $this->subject,
                $this->body,
                $this->contactName,
                $this->schoolName,
                $this->tenantId,
            ));

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
        Log::error("SendAnnouncementViaMail failed for email={$this->email}: ".$e->getMessage());

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
            Log::error('SendAnnouncementViaMail::failed() could not update recipient: '.$inner->getMessage());
        }
    }
}
