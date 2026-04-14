<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly SupportTicket $ticket,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Support #" . $this->ticket->id . "] " . $this->ticket->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support_ticket_received',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
