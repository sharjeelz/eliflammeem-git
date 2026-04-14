<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantProvisionedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $schoolName,
        public readonly string $adminName,
        public readonly string $adminEmail,
        public readonly string $resetUrl,
        public readonly string $portalUrl,
        public readonly string $adminLoginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your {$this->schoolName} Portal is Ready",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant_provisioned',
        );
    }
}
