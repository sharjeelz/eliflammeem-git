<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketResolvedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly SupportTicket $ticket,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Support Ticket #{$this->ticket->id} Has Been Resolved",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support_ticket_resolved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
