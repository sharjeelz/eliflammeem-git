<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyTrendDigestMail extends TenantMail
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array $trends,
        public readonly string $schoolName,
        public readonly string $adminName,
        public readonly string $issuesUrl,
        string $tenantId,
    ) {
        $this->tenantId = $tenantId;
    }

    public function envelope(): Envelope
    {
        $trendCount = count($this->trends);
        $subject = $trendCount > 0
            ? "Daily Trend Report - {$trendCount} pattern" . ($trendCount > 1 ? 's' : '') . " detected"
            : "Daily Trend Report - No patterns detected";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily_trend_digest',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
