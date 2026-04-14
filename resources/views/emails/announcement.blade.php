@extends('emails.layout')

@section('title', $mailSubject)
@section('header-title', $mailSubject)
@section('header-subtitle', 'A message from ' . $schoolName)
@section('footer-note', 'You received this message because you are registered as a contact at ' . $schoolName . '.')

@section('content')
<p>Hello{{ $contactName ? ', ' . $contactName : '' }},</p>

<div style="background:#f8fafc;border-left:4px solid #4f46e5;border-radius:4px;padding:20px 24px;margin:24px 0;font-size:15px;line-height:1.8;color:#1f2937;white-space:pre-wrap;">{{ $body }}</div>

<div class="support">
    <p><strong>Questions?</strong></p>
    <p>
        Contact <strong>{{ $schoolName }}</strong> or reach out at
        <a href="mailto:{{ config('mail.support_address', config('mail.from.address')) }}">{{ config('mail.support_address', config('mail.from.address')) }}</a>
    </p>
</div>
@endsection
