<?php

namespace App\Mail;

use App\Services\TenantMailer;
use Illuminate\Mail\Mailable;

/**
 * Base class for all tenant-scoped mailables.
 * Swaps in the school's custom SMTP mailer at send-time (queue worker),
 * falling back to the global default when no custom SMTP is configured.
 */
abstract class TenantMail extends Mailable
{
    protected string $tenantId = '';

    public function send($mailer): mixed
    {
        if ($this->tenantId && TenantMailer::configure($this->tenantId)) {
            $mailer = app('mail.manager')->mailer('tenant_smtp');
        }

        return parent::send($mailer);
    }
}
