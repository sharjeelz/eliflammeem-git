@extends('emails.layout')

@section('title', 'How did we do? — ' . $schoolName)
@section('header-title', 'How did we do?')
@section('header-subtitle', 'Share your experience with ' . $schoolName)
@section('footer-note', 'You received this email because your issue was closed at ' . $schoolName . '.')

@push('styles')
<style>
    .issue-box  { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
                  padding: 14px 20px; margin: 16px 0 24px; font-weight: 600; font-size: 15px; color: #111827; }
    .hint       { text-align: center; color: #6b7280; font-size: 13px; margin: 24px 0 10px; }
    .ratings    { display: flex; justify-content: center; gap: 10px; margin: 4px 0 8px; }
    .rating-btn { display: inline-block; width: 52px; height: 52px; line-height: 52px; border-radius: 50%;
                  text-align: center; font-size: 20px; font-weight: 800; text-decoration: none; color: #fff; }
    .r1 { background: #ef4444; } .r2 { background: #f97316; } .r3 { background: #f59e0b; }
    .r4 { background: #84cc16; } .r5 { background: #22c55e; }
    .scale      { display: flex; justify-content: space-between; font-size: 11px; color: #9ca3af; margin: 6px 4px 28px; }
</style>
@endpush

@section('content')
<span class="pill">{{ $schoolName }}</span>

<p>Hi <strong>{{ $contactName }}</strong>,</p>
<p>Your issue has been closed. We'd love to know how we did — it only takes <strong>one click</strong>.</p>

<div class="issue-box">{{ $issue->title }}</div>

<p class="hint">Tap a number to submit your rating:</p>
<div class="ratings">
    @for ($i = 1; $i <= 5; $i++)
        <a href="{{ $portalBaseUrl }}/csat/{{ $token }}/{{ $i }}" class="rating-btn r{{ $i }}">{{ $i }}</a>
    @endfor
</div>
<div class="scale">
    <span>Not satisfied</span>
    <span>Very satisfied</span>
</div>

<div class="support">
    <p><strong>Still have concerns?</strong></p>
    <p>Submit a new issue on the portal or contact us at <a href="mailto:{{ config('mail.support_address', config('mail.from.address')) }}">{{ config('mail.support_address', config('mail.from.address')) }}</a></p>
</div>
@endsection
