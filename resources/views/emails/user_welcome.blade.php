@extends('emails.layout')

@section('title', 'Welcome to ' . $schoolName)
@section('header-title', 'Welcome, ' . $name . '!')
@section('header-subtitle', 'Your account at ' . $schoolName . ' is ready.')
@section('footer-note', 'You received this email because an account was created for you at ' . $schoolName . '.')

@section('content')
@php
    $roleLabel = match($role) {
        'branch_manager' => 'Branch Manager',
        'staff'          => 'Staff',
        default          => ucfirst($role),
    };
@endphp

<span class="pill">{{ $roleLabel }}</span>

<p>Hi <strong>{{ $name }}</strong>,</p>

<p>
    You've been added as a <strong>{{ $roleLabel }}</strong> on the
    <strong>{{ $schoolName }}</strong> issue management portal powered by
    <strong>{{ config('app.name') }}</strong>.
</p>

<p>
    Use the credentials below to log in and start managing issues. If you have any
    questions about your role or responsibilities, please reach out to your school administrator.
</p>

<div class="creds-title">Your Login Credentials</div>
<div class="creds">
    <div class="cred-row">
        <div class="cred-label">Portal</div>
        <div class="cred-value"><a href="{{ $loginUrl }}">{{ $loginUrl }}</a></div>
    </div>
    <div class="cred-row">
        <div class="cred-label">Email</div>
        <div class="cred-value">{{ $email }}</div>
    </div>
    <div class="cred-row">
        <div class="cred-label">Password</div>
        <div class="cred-value mono">{{ $password }}</div>
    </div>
</div>

<div class="pw-notice">
    &#128274; <strong>Important:</strong> Please change your password immediately after
    your first login. Go to <em>Profile &rarr; Change Password</em> in the admin panel.
</div>

<div class="btn-wrap">
    <a href="{{ $loginUrl }}" class="btn">Log In Now &rarr;</a>
</div>

<hr />

<div class="support">
    <p><strong>Need help?</strong></p>
    <p>
        Contact your school administrator or reach out to our support team —
        we're happy to help you get set up.
    </p>
    <p style="margin-top:12px;">
        &#128231; Email us at <a href="mailto:{{ config('mail.support_address', config('mail.from.address')) }}">{{ config('mail.support_address', config('mail.from.address')) }}</a>
    </p>
</div>
@endsection
