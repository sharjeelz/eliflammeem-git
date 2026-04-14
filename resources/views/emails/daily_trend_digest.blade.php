@extends('emails.layout')

@section('title', 'Daily Trend Report')
@section('header-title', 'Daily Trend Report')
@section('header-subtitle', 'Pattern detection summary for ' . $schoolName)
@section('footer-note', 'You received this email because you are an administrator at ' . $schoolName . '.')

@section('content')
<span class="pill">{{ $schoolName }}</span>

<p>Hi <strong>{{ $adminName }}</strong>,</p>

@if(count($trends) > 0)
    <p>We detected the following patterns in issue submissions over the last 7 days. These trends may indicate systemic issues requiring your attention:</p>

    <div class="creds-title">DETECTED TRENDS (Last 7 Days)</div>
    <div class="creds">
        @foreach($trends as $trend)
        <div class="cred-row">
            <div class="cred-label">{{ ucfirst(str_replace('_', ' ', $trend['theme'])) }}</div>
            <div class="cred-value">
                <strong>{{ $trend['count'] }}</strong> {{ $trend['count'] === 1 ? 'issue' : 'issues' }}
                <a href="{{ $trend['url'] }}" style="margin-left: 8px; font-size: 13px; color: #2563eb; text-decoration: none;">View →</a>
            </div>
        </div>
        @endforeach
    </div>

    <p>These patterns are identified using AI analysis of issue themes. We recommend reviewing these issues to identify any underlying problems that may need to be addressed at an institutional level.</p>

    <div class="btn-wrap">
        <a href="{{ $issuesUrl }}" class="btn">View All Issues</a>
    </div>
@else
    <p>Good news! No significant patterns were detected in issue submissions over the last 7 days.</p>
    
    <p>This suggests that issues are diverse and not clustering around specific themes. Continue monitoring your issue dashboard for any changes.</p>

    <div class="btn-wrap">
        <a href="{{ $issuesUrl }}" class="btn">View Dashboard</a>
    </div>
@endif

<hr>

<p style="font-size: 13px; color: #6b7280;">
    <strong>About Trend Detection:</strong><br>
    This digest analyzes themes extracted by AI from issue descriptions. A trend is identified when 3 or more issues share the same theme within a 7-day period. This helps you spot systemic issues before they escalate.
</p>

<div class="support">
    <p><strong>Need help?</strong></p>
    <p>Reach us at <a href="mailto:{{ config('mail.support_address', config('mail.from.address')) }}">{{ config('mail.support_address', config('mail.from.address')) }}</a></p>
</div>
@endsection
