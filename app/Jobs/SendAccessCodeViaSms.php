<?php

namespace App\Jobs;

use App\Models\AccessCode;
use App\Models\Tenant;
use App\Services\TenantSms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAccessCodeViaSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 30;

    public function __construct(
        public readonly string $tenantId,
        public readonly int    $accessCodeId,
        public readonly string $phone,
        public readonly string $message,
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
            TenantSms::send($this->phone, $this->message, $this->tenantId);
            AccessCode::find($this->accessCodeId)?->update(['sent_at' => now()]);
        } finally {
            tenancy()->end();
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SendAccessCodeViaSms failed for access_code={$this->accessCodeId} phone={$this->phone}: " . $e->getMessage());

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
            Log::error("SendAccessCodeViaSms::failed() could not write send_error: " . $inner->getMessage());
        }
    }
}
