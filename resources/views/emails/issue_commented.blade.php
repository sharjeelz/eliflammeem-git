@extends('emails.layout')

@section('title', 'New Comment on Issue')
@section('header-title', 'New Comment on Issue')
@section('header-subtitle', $schoolName)
@section('footer-note', 'You received this email because a comment was added to an issue assigned to you at ' . $schoolName . '.')

@push('styles')
<style>
    .comment-box { background: #f8fafc; border-left: 4px solid #4f46e5; padding: 16px 20px;
                   border-radius: 0 8px 8px 0; margin: 4px 0 24px; font-style: italic; color: #374151; font-size: 15px; }
</style>
@endpush

@section('content')
<span class="pill">{{ $schoolName }}</span>

<p>Hi,</p>
<p>A new comment was added to an issue assigned to you.</p>

<div class="label">Issue</div>
<div class="value"><strong>{{ $issue->title }}</strong></div>

<div class="label">Comment by {{ $actor->name }}</div>
<div class="comment-box">{{ $preview }}</div>

<div class="support">
    <p><strong>Need help?</strong></p>
    <p>Reach us at <a href="mailto:{{ config('mail.support_address', config('mail.from.address')) }}">{{ config('mail.support_address', config('mail.from.address')) }}</a></p>
</div>
@endsection
