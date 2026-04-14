@extends('emails.layout')

@section('title', 'Your ' . $schoolName . ' Portal is Ready')
@section('header-title', 'Welcome aboard, ' . $schoolName . '!')
@section('header-subtitle', 'Your school issue-management portal is live and ready to use.')
@section('footer-note', 'You received this email because a new school portal was provisioned for ' . $schoolName . '.')

@push('styles')
<style>
    .steps-title { font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 18px; }
    .step        { display: flex; gap: 14px; margin-bottom: 16px; align-items: flex-start; }
    .step-num    { flex-shrink: 0; width: 28px; height: 28px; background: #eef2ff; border-radius: 50%;
                   text-align: center; line-height: 28px; font-size: 12px; font-weight: 800; color: #4338ca; }
    .step-text   { font-size: 14px; color: #374151; line-height: 1.6; padding-top: 4px; }
    .step-text strong { color: #111827; }
</style>
@endpush

@section('content')
<span class="pill">{{ $schoolName }}</span>

<p>Hi <strong>{{ $adminName }}</strong>,</p>

<p>
    Thank you for choosing <strong>{{ config('app.name') }}</strong>. We're genuinely excited to have
    <strong>{{ $schoolName }}</strong> on board and are committed to making your experience
    exceptional from day one.
</p>

<p>
    Your dedicated portal has been fully configured — categories, roles, and your first
    admin account are all set up and ready. Everything below is what you need to get started right now.
</p>

{{-- Credentials --}}
<div class="creds-title">Your Portal Details</div>
<div class="creds">
    <div class="cred-row">
        <div class="cred-label">School Portal</div>
        <div class="cred-value"><a href="{{ $portalUrl }}">{{ $portalUrl }}</a></div>
    </div>
    <div class="cred-row">
        <div class="cred-label">Admin Panel</div>
        <div class="cred-value"><a href="{{ $adminLoginUrl }}">{{ $adminLoginUrl }}</a></div>
    </div>
    <div class="cred-row">
        <div class="cred-label">Email</div>
        <div class="cred-value">{{ $adminEmail }}</div>
    </div>
</div>

<div class="pw-notice">
    &#128274; <strong>Set your password to get started.</strong> Click the button below to create your
    secure password. This link expires in 60 minutes — if it expires, use the "Forgot Password" link
    on the admin login page.
</div>

<div class="btn-wrap">
    <a href="{{ $resetUrl }}" class="btn">Set My Password &rarr;</a>
</div>

<hr />

{{-- What's next --}}
<div class="steps-title">Getting Started — What to Do First</div>

<div class="step">
    <div class="step-num">1</div>
    <div class="step-text"><strong>Set your password</strong> — use the "Set My Password" button above to create your secure password and log in to the admin panel.</div>
</div>
<div class="step">
    <div class="step-num">2</div>
    <div class="step-text"><strong>Add your branches</strong> — go to <em>Settings → Branches</em> to set up the school branches you want to manage issues for.</div>
</div>
<div class="step">
    <div class="step-num">3</div>
    <div class="step-text"><strong>Invite your staff</strong> — create user accounts for your team under <em>Settings → Users</em> and assign them roles (Branch Manager or Staff).</div>
</div>
<div class="step">
    <div class="step-num">4</div>
    <div class="step-text"><strong>Import parents &amp; teachers</strong> — upload your contact list via CSV under <em>Contacts</em> and generate access codes to share with them.</div>
</div>
<div class="step">
    <div class="step-num">5</div>
    <div class="step-text"><strong>Customise your portal</strong> — add your school logo and set your brand colour under <em>Settings → School Profile</em> so the parent-facing portal matches your identity.</div>
</div>

<hr />

<div class="support">
    <p><strong>We're here whenever you need us.</strong></p>
    <p>
        Our support team is available to help you with setup, training, and any questions you have.
        Don't hesitate to reach out — you're never on your own.
    </p>
    <p style="margin-top:12px;">
        &#128231; Email us at <a href="mailto:{{ config('mail.support_address', config('mail.from.address')) }}">{{ config('mail.support_address', config('mail.from.address')) }}</a>
    </p>
</div>
@endsection
