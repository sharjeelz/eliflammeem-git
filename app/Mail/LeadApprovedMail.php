<?php

namespace App\Mail;

use App\Models\AppSetting;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeadApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Lead $lead) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your enquiry has been approved — next steps',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lead_approved',
            with: [
                'contactEmail' => AppSetting::get('contact_email'),
            ],
        );
    }
}
