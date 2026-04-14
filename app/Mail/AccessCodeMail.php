<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccessCodeMail extends TenantMail
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public ?string $schoolName,
        public ?string $contactName = null,
        string $tenantId = '',
    ) {
        $this->tenantId = $tenantId;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Access Code — ' . ($this->schoolName ?? config('app.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.access_code',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
