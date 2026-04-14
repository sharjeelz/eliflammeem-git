<?php

namespace App\Mail;

use App\Models\Issue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CsatSurveyMail extends TenantMail
{
    use Queueable, SerializesModels;

    public function __construct(
        public Issue $issue,
        public string $token,
        public string $schoolName,
        public string $contactName,
        public string $portalBaseUrl,
    ) {
        $this->tenantId = $issue->tenant_id;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "How did we do? — {$this->schoolName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.csat_survey',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
