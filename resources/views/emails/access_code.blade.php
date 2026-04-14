@extends('emails.layout')

@section('title', 'Your Access Code')
@section('header-title', 'Your Access Code')
@section('header-subtitle', 'Use this code to access the ' . ($schoolName ?? config('app.name')) . ' portal')
@section('footer-note', 'You received this email because an access code was requested for your account.')

@push('styles')
<style>
    .code-box   { background: #f1f5f9; border: 2px dashed #c7d2fe; border-radius: 12px;
                  text-align: center; padding: 24px; margin: 24px 0; }
    .code-box span { font-family: 'Courier New', Courier, monospace; font-size: 32px;
                     font-weight: 800; letter-spacing: 6px; color: #3730a3; }
</style>
@endpush

@section('content')
<p>Hello{{ $contactName ? ', ' . $contactName : '' }},</p>
<p>
    Your access code for <strong>{{ $schoolName ?? config('app.name') }}</strong> is:
</p>

<div class="code-box">
    <span>{{ $code }}</span>
</div>

<p>Enter this code on the portal to submit or track your issues. This code expires in <strong>7 days</strong>.</p>

<div class="support">
    <p><strong>Need help?</strong></p>
    <p>
        Contact your school or reach out to us at
        <a href="mailto:{{ config('mail.support_address', config('mail.from.address')) }}">{{ config('mail.support_address', config('mail.from.address')) }}</a>
    </p>
</div>
@endsection
