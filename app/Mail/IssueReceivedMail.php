<?php

namespace App\Mail;

use App\Models\Issue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IssueReceivedMail extends TenantMail
{
    use Queueable, SerializesModels;

    public function __construct(
        public Issue $issue,
        public string $contactName,
        public string $schoolName,
        public string $trackingUrl,
    ) {
        $this->tenantId = $issue->tenant_id;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "We received your issue — Tracking ID: {$this->issue->public_id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.issue_received',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
