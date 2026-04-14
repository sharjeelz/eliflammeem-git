<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserWelcomeMail extends TenantMail
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $role,
        public readonly string $schoolName,
        public readonly string $loginUrl,
        string $tenantId = '',
    ) {
        $this->tenantId = $tenantId;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Welcome to {$this->schoolName} — Your Account is Ready",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user_welcome',
        );
    }
}
