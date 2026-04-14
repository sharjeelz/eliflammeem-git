@extends('emails.layout')

@php
    $headerTitle = match($toStatus) {
        'resolved' => 'Your Issue Has Been Resolved',
        'closed'   => 'Your Issue Has Been Closed',
        'in_progress' => 'Your Issue Is In Progress',
        default    => 'Issue Status Updated',
    };
@endphp

@section('title', $headerTitle)
@section('header-title', $headerTitle)
@section('header-subtitle', $schoolName)
@section('footer-note', 'You received this email because your issue status was updated at ' . $schoolName . '.')

@push('styles')
<style>
    .status-row { display: flex; align-items: center; gap: 12px; margin-top: 6px; }
    .arrow      { color: #9ca3af; font-size: 16px; }
    .info-box   { background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 10px;
                  padding: 16px 20px; margin: 24px 0; font-size: 14px; color: #3730a3; line-height: 1.6; }
</style>
@endpush

@section('content')
<span class="pill">{{ $schoolName }}</span>

<p>Hi <strong>{{ $contactName }}</strong>,</p>

@if($toStatus === 'resolved')
    <p>Great news! Your issue has been <strong>resolved</strong> by our team. We hope the outcome meets your expectations.</p>
@elseif($toStatus === 'closed')
    <p>Your issue has been <strong>closed</strong>. If you need further assistance, please don't hesitate to submit a new issue.</p>
@elseif($toStatus === 'in_progress')
    <p>Good news — your issue is now <strong>in progress</strong>. Our team is actively working on it and will keep you updated.</p>
@else
    <p>There has been a status update on your issue.</p>
@endif

<div class="label">Issue</div>
<div class="value"><strong>{{ $issue->title }}</strong></div>

<div class="label">Status Change</div>
<div class="status-row">
    <span class="badge badge-{{ $fromStatus }}">{{ ucfirst(str_replace('_', ' ', $fromStatus)) }}</span>
    <span class="arrow">&#8594;</span>
    <span class="badge badge-{{ $toStatus }}">{{ ucfirst(str_replace('_', ' ', $toStatus)) }}</span>
</div>

@if($toStatus === 'resolved')
<div class="info-box">
    <strong>Is the problem solved?</strong><br>
    If not, let us know and we'll look into it again — no need to submit a new issue.
</div>
@if(!empty($reopenUrl))
<div style="text-align:center;margin:24px 0;">
    <a href="{{ $reopenUrl }}"
       style="display:inline-block;background:#ef4444;color:#ffffff;font-weight:600;
              font-size:14px;padding:12px 28px;border-radius:8px;text-decoration:none;">
        Still a problem? Click here
    </a>
    <p style="font-size:12px;color:#6b7280;margin-top:8px;">
        This link is single-use and tied to your issue only.
    </p>
</div>
@endif
@endif

<div class="support">
    <p><strong>Need help?</strong></p>
    <p>Reach us at <a href="mailto:{{ config('mail.support_address', config('mail.from.address')) }}">{{ config('mail.support_address', config('mail.from.address')) }}</a></p>
</div>
@endsection
