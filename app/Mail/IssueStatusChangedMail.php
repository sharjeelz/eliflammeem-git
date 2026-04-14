<?php

namespace App\Mail;

use App\Models\Issue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IssueStatusChangedMail extends TenantMail
{
    use Queueable, SerializesModels;

    public ?string $reopenUrl = null;

    public function __construct(
        public Issue $issue,
        public string $fromStatus,
        public string $toStatus,
        public string $schoolName,
        public string $contactName,
        public string $portalBaseUrl = '',
    ) {
        $this->tenantId = $issue->tenant_id;

        if ($toStatus === 'resolved' && $issue->reopen_token) {
            // Use the portal base URL captured at dispatch time (HTTP context) so the
            // link points to the tenant subdomain, not APP_URL (central domain).
            $base = rtrim($this->portalBaseUrl, '/') ?: config('app.url');
            $this->reopenUrl = "{$base}/issues/{$issue->public_id}/still-problem?token={$issue->reopen_token}";
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Update on your issue: {$this->issue->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.issue_status_changed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
