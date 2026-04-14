@extends('emails.layout')

@section('title', 'Issue Received — ' . $issue->public_id)
@section('header-title', 'We Received Your Issue')
@section('header-subtitle', $schoolName)
@section('footer-note', 'You received this email because you submitted an issue at ' . $schoolName . '. If this wasn\'t you, please ignore this email.')

@push('styles')
<style>
    .tracking-box {
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        border: 1px solid #c7d2fe;
        border-radius: 12px;
        padding: 24px 28px;
        margin: 28px 0;
        text-align: center;
    }
    .tracking-box .tracking-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #6366f1;
        margin-bottom: 8px;
    }
    .tracking-box .tracking-id {
        font-size: 28px;
        font-weight: 800;
        color: #3730a3;
        font-family: 'Courier New', Courier, monospace;
        letter-spacing: 3px;
    }
    .steps { margin: 24px 0; padding: 0; list-style: none; }
    .steps li {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 10px 0;
        font-size: 14px;
        color: #374151;
        line-height: 1.6;
        border-bottom: 1px solid #f3f4f6;
    }
    .steps li:last-child { border-bottom: none; }
    .step-num {
        flex-shrink: 0;
        width: 26px;
        height: 26px;
        background: #4f46e5;
        color: #fff;
        border-radius: 50%;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@section('content')
<span class="pill">{{ $schoolName }}</span>

<p>Hi <strong>{{ $contactName }}</strong>,</p>
<p>Thank you for reaching out. Your issue has been successfully submitted and our team will review it shortly.</p>

<div class="tracking-box">
    <div class="tracking-label">Your Tracking ID</div>
    <div class="tracking-id">{{ $issue->public_id }}</div>
</div>

<div class="label">Issue</div>
<div class="value"><strong>{{ $issue->title }}</strong></div>

<div class="label">Category</div>
<div class="value">{{ $issue->category?->name ?? 'General' }}</div>

<div class="label">Submitted</div>
<div class="value">{{ $issue->created_at->format('d M Y, h:i A') }}</div>

<hr>

<p><strong>What happens next?</strong></p>
<ul class="steps">
    <li>
        <span class="step-num">1</span>
        <span>Your issue is assigned to the relevant team member who will review the details.</span>
    </li>
    <li>
        <span class="step-num">2</span>
        <span>You'll receive an email update whenever the status changes.</span>
    </li>
    <li>
        <span class="step-num">3</span>
        <span>Once resolved, you'll be asked to rate your experience.</span>
    </li>
</ul>

<div class="btn-wrap">
    <a href="{{ $trackingUrl }}" class="btn">Track Your Issue &rarr;</a>
</div>

<p style="text-align:center; font-size:13px; color:#9ca3af;">
    Or copy this link: <a href="{{ $trackingUrl }}" style="color:#4f46e5; word-break:break-all;">{{ $trackingUrl }}</a>
</p>

<div class="support">
    <p><strong>Need help?</strong></p>
    <p>Reach us at <a href="mailto:{{ config('mail.support_address', config('mail.from.address')) }}">{{ config('mail.support_address', config('mail.from.address')) }}</a></p>
</div>
@endsection
