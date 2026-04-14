<?php

namespace App\Mail;

use App\Models\SupportTicket;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $ticketsUrl;

    public function __construct(
        public readonly SupportTicket $ticket,
        public readonly string $previousStatus,
    ) {
        $this->ticketsUrl = $this->buildTicketsUrl();
    }

    public function envelope(): Envelope
    {
        $subjects = [
            'in_progress' => "We're Working On Your Ticket #{$this->ticket->id}",
            'resolved'    => "Your Support Ticket #{$this->ticket->id} Has Been Resolved",
            'open'        => "Your Support Ticket #{$this->ticket->id} Has Been Re-Opened",
        ];

        return new Envelope(
            subject: $subjects[$this->ticket->status] ?? "Update on Your Support Ticket #{$this->ticket->id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support_ticket_status_changed',
        );
    }

    public function attachments(): array
    {
        return [];
    }

    private function buildTicketsUrl(): string
    {
        $tenant   = Tenant::find($this->ticket->tenant_id);
        $domain   = $tenant?->domains()->first();
        $protocol = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'http';
        $port     = parse_url(config('app.url'), PHP_URL_PORT);

        return $domain
            ? "{$protocol}://{$domain->domain}" . ($port ? ":{$port}" : '') . '/admin/support-tickets'
            : url('admin/support-tickets');
    }
}
