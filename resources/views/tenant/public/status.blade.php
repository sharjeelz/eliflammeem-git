@extends('layouts.public')

@section('content')

@if(session('ok'))
    <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
        {{ session('ok') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        {{ $errors->first() }}
    </div>
@endif

<div class="mb-6">
    <h1 class="text-xl font-bold text-slate-800">{{ __('public.issue_status') }}</h1>
    <p class="mt-1 text-sm text-slate-500">
        {{ __('public.issue_code') }}
        <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 text-xs">{{ $issue->public_id }}</code>
    </p>
</div>

@php
    $statusColors = [
        'new'         => 'bg-blue-100 text-blue-700',
        'in_progress' => 'bg-amber-100 text-amber-700',
        'resolved'    => 'bg-green-100 text-green-700',
        'closed'      => 'bg-slate-100 text-slate-600',
    ];
    $color = $statusColors[$issue->status] ?? 'bg-slate-100 text-slate-600';
    $contactClass = 'App\Models\RosterContact';
@endphp

{{-- Issue header --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-4">
        <h2 class="font-semibold text-slate-800 truncate">{{ $issue->title }}</h2>
        <span class="shrink-0 text-xs font-medium px-2.5 py-1 rounded-full {{ $color }}">
            {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
        </span>
    </div>

    <div class="px-5 py-5 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide mb-1">{{ __('public.branch') }}</p>
                <p class="text-sm text-slate-700">{{ $issue->branch->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide mb-1">{{ __('public.submitted') }}</p>
                <p class="text-sm text-slate-700">{{ $issue->created_at->format('d M Y, H:i') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- AI Acknowledgment --}}
@php
    $aiResult       = $issue->aiAnalysis?->result ?? [];
    $acknowledgment = data_get($aiResult, 'acknowledgment');

    // Response time: prefer live category SLA, then frozen issue SLA, then priority
    $slaHours = $issue->issueCategory?->default_sla_hours ?: $issue->sla_hours;
    if ($slaHours) {
        $h = (int) $slaHours;
        if ($h < 24) {
            $responseTime = "within {$h} " . ($h === 1 ? 'hour' : 'hours');
        } elseif ($h % 24 === 0) {
            $days = $h / 24;
            $responseTime = "within {$days} " . ($days === 1 ? 'day' : 'days');
        } else {
            $responseTime = "within {$h} hours";
        }
    } else {
        $responseTime = match($issue->priority) {
            'urgent' => 'within 4 hours',
            'high'   => 'within 24 hours',
            'medium' => 'within 3 days',
            'low'    => 'within 7 days',
            default  => 'within 5 days',
        };
    }
@endphp
@if($acknowledgment)
<div class="mb-4 rounded-xl bg-blue-50 border border-blue-200 px-5 py-4">
    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">{{ __('public.system_noted') }}</p>
    <p class="text-sm text-slate-700 leading-relaxed">"{{ $acknowledgment }}"</p>
    <p class="text-xs text-slate-400 mt-2">
        <svg class="w-3.5 h-3.5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Expected response: {{ $responseTime }}
    </p>
</div>
@endif

{{-- Anonymous notice --}}
@if($issue->is_anonymous)
<div class="mb-4 rounded-xl bg-slate-800 px-5 py-4 flex items-start gap-3 text-white">
    <svg class="w-5 h-5 text-slate-300 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
    </svg>
    <div>
        <p class="text-sm font-semibold mb-0.5">{{ __('public.anonymous_submission') }}</p>
        <p class="text-slate-300 text-xs leading-relaxed">{{ __('public.anon_status_notice') }}</p>
    </div>
</div>
@endif

{{-- Spam notice --}}
@if($issue->is_spam)
<div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-5 py-4 flex items-start gap-3">
    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <div>
        <p class="text-sm font-semibold text-red-700">{{ __('public.spam_title') }}</p>
        <p class="text-xs text-red-500 mt-0.5">{{ __('public.spam_msg') }}</p>
    </div>
</div>
@endif

{{-- Conversation thread --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4">
    <div class="px-5 py-3 border-b border-slate-100">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('public.conversation') }}</p>
    </div>

    <div class="px-5 py-4 space-y-4">
        {{-- Original description as first message (from contact) --}}
        <div class="flex justify-end">
            <div class="max-w-[85%]">
                <p class="text-xs text-slate-400 text-right mb-1">{{ __('public.you') }} &middot; {{ $issue->created_at->format('d M, H:i') }}</p>
                <div class="bg-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-3 text-sm whitespace-pre-line">
                    {{ $issue->description }}
                </div>

                {{-- Original submission attachments --}}
                @if($issue->attachments->isNotEmpty())
                <div class="mt-2 flex flex-wrap gap-2 justify-end">
                    @foreach($issue->attachments as $att)
                    @php
                        $isImage = str_starts_with($att->mime, 'image/');
                        $sizeKb  = round($att->size / 1024);
                        $label   = basename($att->path);
                    @endphp
                    @if($isImage)
                        <a href="{{ $att->storage_url }}" target="_blank" rel="noopener"
                           class="block rounded-xl overflow-hidden border-2 border-white/30 shadow-sm hover:opacity-90 transition-opacity">
                            <img src="{{ $att->storage_url }}" alt="{{ $label }}"
                                 class="h-24 w-24 object-cover">
                        </a>
                    @else
                        <a href="{{ $att->storage_url }}" target="_blank" rel="noopener"
                           class="flex items-center gap-2 bg-white/15 hover:bg-white/25 transition-colors
                                  text-white text-xs font-medium px-3 py-2 rounded-xl border border-white/20">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <span class="truncate max-w-[120px]">{{ $label }}</span>
                            <span class="text-white/60 flex-shrink-0">{{ $sizeKb }}KB</span>
                        </a>
                    @endif
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Messages --}}
        @foreach($issue->messages as $msg)
        @php
            $isContact = $msg->author_type === $contactClass
                || ($issue->is_anonymous && !empty($msg->meta['anonymous_followup']));
            $name = $msg->authorDisplayName();
            $time = $msg->created_at->format('d M, H:i');
        @endphp

        @if($isContact)
        {{-- Contact message → right --}}
        <div class="flex justify-end">
            <div class="max-w-[80%]">
                <p class="text-xs text-slate-400 text-right mb-1">{{ __('public.you') }} &middot; {{ $time }}</p>
                <div class="bg-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-3 text-sm whitespace-pre-line">
                    {{ $msg->message }}
                </div>
            </div>
        </div>
        @else
        {{-- Staff / Admin message → left --}}
        <div class="flex justify-start">
            <div class="max-w-[80%]">
                <p class="text-xs text-slate-400 mb-1">{{ $name }} &middot; {{ $time }}</p>
                <div class="bg-slate-100 text-slate-800 rounded-2xl rounded-tl-sm px-4 py-3 text-sm whitespace-pre-line">
                    {{ $msg->message }}
                </div>
            </div>
        </div>
        @endif
        @endforeach

        @if($issue->messages->isEmpty())
        <p class="text-sm text-slate-400 text-center py-2">{{ __('public.no_replies_yet') }}</p>
        @endif
    </div>

    {{-- Reply / follow-up / close / reopen --}}
    @if(!$issue->is_spam)

        @if($issue->is_anonymous)
            {{-- Anonymous follow-up form --}}
            @if($issue->status !== 'closed')
            <div class="px-5 pb-5 border-t border-slate-100 pt-4">
                <p class="text-xs text-slate-400 mb-3">
                    <svg class="w-3.5 h-3.5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    {{ __('public.reply_anonymously_notice') }}
                </p>
                <form method="POST" action="{{ route('tenant.public.issue.anonymous_followup', ['public_id' => $issue->public_id]) }}">
                    @csrf
                    <textarea name="message" rows="3" required maxlength="3000"
                              placeholder="{{ __('public.additional_info_placeholder') }}"
                              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('message') }}</textarea>
                    @if(!app()->environment('local'))
                    <div class="mt-2">
                        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
                        @error('cf-turnstile-response')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif
                    <div class="mt-2 flex justify-end">
                        <button type="submit"
                                class="rounded-lg bg-slate-800 hover:bg-slate-700 px-4 py-2 text-sm font-medium text-white transition-colors">
                            {{ __('public.send_anonymously') }}
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="px-5 pb-5 pt-4 text-center text-sm text-slate-400">
                {{ __('public.issue_closed_msg') }}
            </div>
            @endif

        @else
            {{-- Regular contact reply/close/reopen --}}
            @if($issue->status !== 'closed' && !empty($code))
            <div class="px-5 pb-5 border-t border-slate-100 pt-4">
                <form method="POST" action="{{ route('tenant.public.issue.reply', ['public_id' => $issue->public_id]) }}">
                    @csrf
                    <input type="hidden" name="code" value="{{ $code }}">
                    <textarea name="message" rows="3" required maxlength="3000"
                              placeholder="{{ __('public.write_reply') }}"
                              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('message') }}</textarea>
                    <div class="mt-2 flex justify-end">
                        <button type="submit"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                            {{ __('public.send_reply') }}
                        </button>
                    </div>
                </form>
            </div>
            @elseif($issue->status === 'closed' && ($issue->meta['unassigned_reason'] ?? null) === 'contact_branch_changed')
            <div class="px-5 pb-5 pt-4">
                <div class="rounded-xl bg-amber-50 border border-amber-200 px-5 py-4 text-center">
                    <svg class="w-6 h-6 text-amber-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
                    </svg>
                    <p class="text-sm font-semibold text-amber-800 mb-1">This issue has been closed by the school</p>
                    <p class="text-xs text-amber-700">Your record has been updated. Please contact the school to receive a new access code if you have a new concern.</p>
                </div>
            </div>
            @elseif($issue->status === 'closed' && !empty($code))
            <div class="px-5 pb-5 pt-4">
                <div class="rounded-xl bg-slate-50 border border-slate-200 px-5 py-4 text-center">
                    <p class="text-sm text-slate-500 mb-1">{{ __('public.issue_closed_reopen') }}</p>
                    <p class="text-xs text-slate-400 mb-4">{{ __('public.reopen_notice') }}</p>
                    <form method="POST"
                          action="{{ route('tenant.public.issue.reopen', ['public_id' => $issue->public_id]) }}"
                          onsubmit="return confirm('{{ __('public.reopen_issue') }}?')">
                        @csrf
                        <input type="hidden" name="code" value="{{ $code }}">
                        <button type="submit"
                                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            {{ __('public.reopen_issue') }}
                        </button>
                    </form>
                </div>
            </div>
            @elseif($issue->status === 'closed')
            <div class="px-5 pb-5 pt-4 text-center text-sm text-slate-400">
                {{ __('public.issue_closed_msg') }}
            </div>
            @endif
        @endif

    @endif
</div>

{{-- Footer actions --}}
<div class="flex items-center justify-between gap-4">
    <div>
        @if(!empty($code))
            <a href="{{ route('tenant.public.status.by_code', ['code' => $code]) }}"
               class="text-sm text-blue-600 hover:text-blue-800 transition-colors">
                {{ __('public.back_to_my_issues') }}
            </a>
        @else
            <a href="{{ url('/') }}" class="text-sm text-blue-600 hover:text-blue-800 transition-colors">
                {{ __('public.back') }}
            </a>
        @endif
    </div>

    @if($issue->status !== 'closed' && !empty($code) && !$issue->is_spam && !$issue->is_anonymous)
        <form method="post" action="{{ route('tenant.public.issue.close', ['public_id' => $issue->public_id]) }}"
              id="closeIssueForm" class="mt-4 p-4 border border-red-200 rounded-lg bg-red-50">
            @csrf
            <input type="hidden" name="code" value="{{ $code }}">
            <p class="text-sm font-semibold text-gray-700 mb-3">Why are you closing this issue?</p>
            <div class="space-y-2 mb-4">
                @foreach(\App\Http\Controllers\Public\IssueStatusController::CONTACT_CLOSE_REASONS as $value => $label)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="close_reason" value="{{ $value }}"
                               class="text-red-500" required
                               {{ old('close_reason') === $value ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @error('close_reason')
                <p class="text-xs text-red-600 mb-2">{{ $message }}</p>
            @enderror
            <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium transition-colors">
                {{ __('public.close_issue') }}
            </button>
        </form>
    @endif
</div>

@endsection
