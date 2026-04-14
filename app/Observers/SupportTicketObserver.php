<?php

namespace App\Observers;

use App\Mail\SupportTicketStatusChangedMail;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Mail;

class SupportTicketObserver
{
    public function updating(SupportTicket $ticket): void
    {
        if (! $ticket->isDirty('status') || ! $ticket->user_email) {
            return;
        }

        $previous = $ticket->getOriginal('status');
        $next     = $ticket->status;

        // Fire for any meaningful status change (never re-fire the same status)
        if ($previous === $next) {
            return;
        }

        Mail::to($ticket->user_email)->queue(new SupportTicketStatusChangedMail($ticket, $previous));
    }
}
