@extends('emails.layout')

@php
    $statusConfig = [
        'in_progress' => [
            'title'    => "We're On It",
            'subtitle' => 'Your ticket is now in progress',
            'colour'   => '#4f46e5',
            'bg'       => '#eef2ff',
            'border'   => '#c7d2fe',
            'icon'     => '🔧',
            'intro'    => 'Good news — our team has picked up your support ticket and is actively working on it.',
        ],
        'resolved' => [
            'title'    => 'Ticket Resolved',
            'subtitle' => 'Your support request has been resolved',
            'colour'   => '#16a34a',
            'bg'       => '#f0fdf4',
            'border'   => '#bbf7d0',
            'icon'     => '✅',
            'intro'    => 'Your support ticket has been reviewed and marked as resolved by our team.',
        ],
        'open' => [
            'title'    => 'Ticket Re-Opened',
            'subtitle' => 'Your support ticket has been re-opened',
            'colour'   => '#d97706',
            'bg'       => '#fffbeb',
            'border'   => '#fde68a',
            'icon'     => '🔄',
            'intro'    => 'Your support ticket has been re-opened. Our team will follow up with you shortly.',
        ],
    ];
    $conf = $statusConfig[$ticket->status] ?? [
        'title'    => 'Ticket Updated',
        'subtitle' => 'Your support ticket status has changed',
        'colour'   => '#64748b',
        'bg'       => '#f8fafc',
        'border'   => '#e2e8f0',
        'icon'     => '📋',
        'intro'    => 'There has been an update to your support ticket.',
    ];
@endphp

@section('title', $conf['title'])
@section('header-title', $conf['icon'] . ' ' . $conf['title'])
@section('header-subtitle', 'Eliflameem Platform Support')
@section('footer-note', 'You received this email because you submitted a support ticket from your school admin panel.')

@section('content')

<p>Hi <strong>{{ $ticket->user_name }}</strong>,</p>
<p>{{ $conf['intro'] }}</p>

<div style="background:{{ $conf['bg'] }};border:1px solid {{ $conf['border'] }};border-radius:10px;padding:20px 24px;margin:20px 0;">
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
            <td style="color:#64748b;font-weight:600;padding:6px 0;">Status</td>
            <td style="padding:6px 0;">
                <span style="background:{{ $conf['colour'] }}1a;color:{{ $conf['colour'] }};font-weight:700;padding:2px 10px;border-radius:12px;font-size:12px;text-transform:uppercase;letter-spacing:.5px;">
                    {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                </span>
            </td>
        </tr>
        <tr>
            <td style="color:#64748b;font-weight:600;padding:6px 0;">Submitted</td>
            <td style="color:#1e293b;padding:6px 0;">{{ $ticket->created_at->format('d M Y, h:i A') }}</td>
        </tr>
    </table>
</div>

@if($ticket->admin_notes)
<div class="label">Notes from our team</div>
<div style="background:#fff;border:1px solid #e2e8f0;border-left:4px solid {{ $conf['colour'] }};border-radius:0 8px 8px 0;padding:16px 20px;color:#334155;line-height:1.7;white-space:pre-wrap;font-size:14px;margin-bottom:20px;">{{ $ticket->admin_notes }}</div>
@endif

<div class="btn-wrap">
    <a href="{{ $ticketsUrl }}" class="btn">View My Tickets &rarr;</a>
</div>

@endsection
