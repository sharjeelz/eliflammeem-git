<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends TenantMail
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $resetUrl,
        public readonly string $schoolName,
        string $tenantId = '',
    ) {
        $this->tenantId = $tenantId;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password — ' . $this->schoolName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password_reset',
        );
    }
}
