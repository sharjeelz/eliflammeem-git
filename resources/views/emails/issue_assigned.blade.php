@extends('emails.layout')

@section('title', 'Issue Assigned to You')
@section('header-title', 'Issue Assigned to You')
@section('header-subtitle', 'A new issue needs your attention at ' . $schoolName)
@section('footer-note', 'You received this email because an issue was assigned to you at ' . $schoolName . '.')

@section('content')
<span class="pill">{{ $schoolName }}</span>

<p>Hi <strong>{{ $assignee->name }}</strong>,</p>
<p>A new issue has been assigned to you. Please review the details below and take action as soon as possible.</p>

<div class="label">Issue</div>
<div class="value"><strong>{{ $issue->title }}</strong></div>

@if($issue->description)
<div class="label">Description</div>
<div class="value">{{ \Illuminate\Support\Str::limit($issue->description, 300) }}</div>
@endif

<div class="label">Priority</div>
<div class="value">{{ ucfirst($issue->priority ?? 'normal') }}</div>

<div class="label">Status</div>
<div class="value">
    <span class="badge badge-{{ $issue->status }}">{{ ucfirst(str_replace('_', ' ', $issue->status)) }}</span>
</div>

@if($issue->branch)
<div class="label">Branch</div>
<div class="value">{{ $issue->branch->name }}</div>
@endif

<div class="support">
    <p><strong>Need help?</strong></p>
    <p>Reach us at <a href="mailto:{{ config('mail.support_address', config('mail.from.address')) }}">{{ config('mail.support_address', config('mail.from.address')) }}</a></p>
</div>
@endsection
