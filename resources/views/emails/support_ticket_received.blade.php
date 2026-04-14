@extends('emails.layout')

@section('title', 'Support Ticket #' . $ticket->id)
@section('header-title', 'New Support Ticket')
@section('header-subtitle', $ticket->tenant_name ?? $ticket->tenant_id)
@section('footer-note', 'This ticket was submitted from the admin panel by ' . $ticket->user_name . '. Reply directly to their email to respond.')

@section('content')

<p>A new support ticket has been submitted from a school admin panel.</p>

<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px 24px;margin:20px 0;">
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
        <tr>
            <td style="color:#64748b;font-weight:600;padding:6px 0;width:130px;">Ticket #</td>
            <td style="color:#1e293b;padding:6px 0;font-family:monospace;font-size:15px;font-weight:700;">#{{ $ticket->id }}</td>
        </tr>
        <tr>
            <td style="color:#64748b;font-weight:600;padding:6px 0;">School</td>
            <td style="color:#1e293b;padding:6px 0;">{{ $ticket->tenant_name ?? $ticket->tenant_id }}</td>
        </tr>
        <tr>
            <td style="color:#64748b;font-weight:600;padding:6px 0;">Submitted by</td>
            <td style="color:#1e293b;padding:6px 0;">{{ $ticket->user_name }} &lt;{{ $ticket->user_email }}&gt;</td>
        </tr>
        <tr>
            <td style="color:#64748b;font-weight:600;padding:6px 0;">Type</td>
            <td style="color:#1e293b;padding:6px 0;">{{ ucwords(str_replace('_', ' ', $ticket->type)) }}</td>
        </tr>
        <tr>
            <td style="color:#64748b;font-weight:600;padding:6px 0;">Priority</td>
            <td style="padding:6px 0;">
                @php
                    $colours = ['urgent'=>'#dc2626','high'=>'#ea580c','medium'=>'#d97706','low'=>'#16a34a'];
                    $c = $colours[$ticket->priority] ?? '#64748b';
                @endphp
                <span style="background:{{ $c }}1a;color:{{ $c }};font-weight:700;padding:2px 10px;border-radius:12px;font-size:12px;text-transform:uppercase;letter-spacing:.5px;">
                    {{ ucfirst($ticket->priority) }}
                </span>
            </td>
        </tr>
        <tr>
            <td style="color:#64748b;font-weight:600;padding:6px 0;">Subject</td>
            <td style="color:#1e293b;padding:6px 0;font-weight:600;">{{ $ticket->subject }}</td>
        </tr>
    </table>
</div>

<div class="label">Message</div>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:16px 20px;color:#334155;line-height:1.7;white-space:pre-wrap;font-size:14px;">{{ $ticket->message }}</div>

<div class="btn-wrap" style="margin-top:24px;">
    <a href="mailto:{{ $ticket->user_email }}" class="btn">Reply to {{ $ticket->user_name }} &rarr;</a>
</div>

@endsection
