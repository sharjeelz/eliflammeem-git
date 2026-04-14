<?php

namespace App\Jobs;

use App\Mail\AccessCodeMail;
use App\Models\AccessCode;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAccessCodeViaMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 60;

    public function __construct(
        public readonly string $tenantId,
        public readonly int    $accessCodeId,
        public readonly string $email,
        public readonly string $contactName,
        public readonly string $codeValue,
        public readonly string $schoolName,
    ) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);

        if (! $tenant) {
            return;
        }

        tenancy()->initialize($tenant);

        try {
            AccessCode::find($this->accessCodeId)?->update(['send_error' => null]);
            // Use send() not queue() — we are already inside the queue worker
            Mail::to($this->email)->send(new AccessCodeMail($this->codeValue, $this->schoolName, $this->contactName, $this->tenantId));
            AccessCode::find($this->accessCodeId)?->update(['sent_at' => now()]);
        } finally {
            tenancy()->end();
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SendAccessCodeViaMail failed for access_code={$this->accessCodeId} email={$this->email}: " . $e->getMessage());

        try {
            $tenant = Tenant::find($this->tenantId);

            if (! $tenant) {
                return;
            }

            tenancy()->initialize($tenant);

            try {
                AccessCode::find($this->accessCodeId)?->update(['send_error' => $e->getMessage()]);
            } finally {
                tenancy()->end();
            }
        } catch (\Throwable $inner) {
            Log::error("SendAccessCodeViaMail::failed() could not write send_error: " . $inner->getMessage());
        }
    }
}
