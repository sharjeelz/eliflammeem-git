@extends('emails.layout')

@section('title', 'Support Ticket #' . $ticket->id . ' Resolved')
@section('header-title', 'Your Support Ticket Has Been Resolved')
@section('header-subtitle', 'Eliflameem Platform Support')
@section('footer-note', 'This email was sent because your support ticket was resolved. If you need further help, please submit a new ticket from your admin panel.')

@section('content')

<p>Hi <strong>{{ $ticket->user_name }}</strong>,</p>
<p>Great news — your support ticket has been reviewed and marked as resolved by our team.</p>

<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:20px 24px;margin:20px 0;">
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
        <tr>
            <td style="color:#64748b;font-weight:600;padding:6px 0;width:130px;">Ticket #</td>
            <td style="color:#1e293b;padding:6px 0;font-family:monospace;font-size:15px;font-weight:700;">#{{ $ticket->id }}</td>
        </tr>
        <tr>
            <td style="color:#64748b;font-weight:600;padding:6px 0;">Subject</td>
            <td style="color:#1e293b;padding:6px 0;font-weight:600;">{{ $ticket->subject }}</td>
        </tr>
        <tr>
            <td style="color:#64748b;font-weight:600;padding:6px 0;">Submitted</td>
            <td style="color:#1e293b;padding:6px 0;">{{ $ticket->created_at->format('d M Y, h:i A') }}</td>
        </tr>
    </table>
</div>

@if($ticket->admin_notes)
<div class="label">Notes from our team</div>
<div style="background:#fff;border:1px solid #e2e8f0;border-left:4px solid #22c55e;border-radius:0 8px 8px 0;padding:16px 20px;color:#334155;line-height:1.7;white-space:pre-wrap;font-size:14px;margin-bottom:20px;">{{ $ticket->admin_notes }}</div>
@else
<p style="color:#64748b;">If you need more details about the resolution, please reply to this email or submit a new ticket from your admin panel.</p>
@endif

<div class="btn-wrap">
    <a href="{{ url('admin/support-tickets') }}" class="btn">View All My Tickets &rarr;</a>
</div>

@endsection
