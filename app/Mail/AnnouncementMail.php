<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnnouncementMail extends TenantMail
{
    use Queueable, SerializesModels;

    public function __construct(
        public string  $mailSubject,
        public string  $body,
        public string  $contactName,
        public string  $schoolName,
        string         $tenantId = '',
    ) {
        $this->tenantId = $tenantId;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailSubject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.announcement');
    }

    public function attachments(): array
    {
        return [];
    }
}
