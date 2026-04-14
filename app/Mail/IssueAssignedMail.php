<?php

namespace App\Mail;

use App\Models\Issue;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IssueAssignedMail extends TenantMail
{
    use Queueable, SerializesModels;

    public function __construct(
        public Issue $issue,
        public User $assignee,
        public string $schoolName,
    ) {
        $this->tenantId = $issue->tenant_id;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Issue Assigned: {$this->issue->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.issue_assigned',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
