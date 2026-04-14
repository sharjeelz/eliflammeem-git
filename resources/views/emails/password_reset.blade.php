@extends('emails.layout')

@section('title', 'Reset Your Password')
@section('header-title', 'Reset Your Password')
@section('header-subtitle', $schoolName)
@section('footer-note', 'You received this email because a password reset was requested for your account at ' . $schoolName . '. If you did not request this, no action is needed.')

@section('content')
<p>Hi <strong>{{ $name }}</strong>,</p>

<p>
    We received a request to reset the password for your account at
    <strong>{{ $schoolName }}</strong>.
    Click the button below to choose a new password.
</p>

<div class="btn-wrap">
    <a href="{{ $resetUrl }}" class="btn">Reset My Password &rarr;</a>
</div>

<p style="text-align:center; font-size:13px; color:#6b7280;">
    This link expires in <strong>60 minutes</strong>.
</p>

<hr />

<p style="font-size:13px; color:#6b7280;">
    If the button above doesn't work, copy and paste this URL into your browser:
</p>
<p style="font-size:12px; color:#4f46e5; word-break:break-all;">{{ $resetUrl }}</p>

<div class="support">
    <p><strong>Didn't request a password reset?</strong></p>
    <p>
        Ignore this email — your password will remain unchanged.
        If you're concerned, contact us at
        <a href="mailto:{{ config('mail.support_address', config('mail.from.address')) }}">{{ config('mail.support_address', config('mail.from.address')) }}</a>
    </p>
</div>
@endsection
