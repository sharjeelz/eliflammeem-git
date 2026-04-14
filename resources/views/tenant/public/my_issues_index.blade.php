@extends('layouts.public')

@section('content')

<div class="mb-6">
    <h1 class="text-xl font-bold text-slate-800">{{ __('public.my_issues') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('public.my_issues_desc') }}</p>
</div>

@if($spamCount > 0)
<div class="mb-4 rounded-xl border px-5 py-4 flex items-start gap-3
    {{ $spamCount >= 5 ? 'bg-red-50 border-red-200' : 'bg-amber-50 border-amber-200' }}">
    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 {{ $spamCount >= 5 ? 'text-red-500' : 'text-amber-500' }}"
         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <div>
        @if($spamCount >= 5)
            <p class="text-sm font-semibold text-red-700">{{ __('public.access_restricted') }}</p>
            <p class="text-xs text-red-500 mt-0.5">{{ $spamCount }} of your submissions were flagged as spam. You can no longer submit new issues.</p>
        @else
            <p class="text-sm font-semibold text-amber-700">{{ __('public.spam_warning') }}</p>
            <p class="text-xs text-amber-600 mt-0.5">{{ $spamCount }} of your submission{{ $spamCount > 1 ? 's have' : ' has' }} been flagged as spam. After 5 flagged submissions your access will be revoked.</p>
        @endif
    </div>
</div>
@endif

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    @if($issues->isEmpty())
        <div class="px-5 py-14 text-center">
            <p class="text-sm text-slate-400">{{ __('public.no_issues_yet') }}</p>
            <a href="{{ url('/') }}"
               class="mt-3 inline-block text-sm text-blue-600 hover:text-blue-800 transition-colors">
                {{ __('public.submit_one_now') }}
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left">Code</th>
                        <th class="px-4 py-3 text-left">{{ __('public.title') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('public.status') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('public.branch') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('public.date') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($issues as $i)
                        @php
                            $statusColors = [
                                'new'         => 'bg-blue-100 text-blue-700',
                                'in_progress' => 'bg-amber-100 text-amber-700',
                                'resolved'    => 'bg-green-100 text-green-700',
                                'closed'      => 'bg-slate-100 text-slate-600',
                            ];
                            $color = $statusColors[$i->status] ?? 'bg-slate-100 text-slate-600';
                            $statusLabel = match($i->status) {
                                'new'         => __('public.status_new'),
                                'in_progress' => __('public.status_in_progress'),
                                'resolved'    => __('public.status_resolved'),
                                'closed'      => __('public.status_closed'),
                                default       => ucfirst(str_replace('_', ' ', $i->status)),
                            };
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $i->public_id }}</td>
                            <td class="px-4 py-3 text-slate-700 max-w-[180px] truncate">
                                {{ $i->title }}
                                @if($i->is_spam)
                                    <span class="ml-1 text-xs font-medium px-1.5 py-0.5 rounded bg-red-100 text-red-600">{{ __('public.spam_badge') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $color }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $i->branch->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $i->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right flex items-center justify-end gap-3">
                                <a href="{{ route('tenant.public.status', ['public_id' => $i->public_id]) }}"
                                   class="text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors">
                                    {{ __('public.view_arrow') }}
                                </a>
                                @if($i->status !== 'closed' && !$i->is_spam)
                                    <form method="post" action="{{ route('tenant.public.issue.close', ['public_id' => $i->public_id]) }}"
                                          onsubmit="return confirm('{{ __('public.close_issue') }}?')">
                                        @csrf
                                        <input type="hidden" name="code" value="{{ $code }}">
                                        <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors">
                                            {{ __('public.close') }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($issues->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $issues->links() }}
            </div>
        @endif
    @endif
</div>

<div class="mt-5 text-center">
    <a href="{{ url('/') }}" class="text-sm text-slate-400 hover:text-slate-600 transition-colors">
        {{ __('public.back_to_portal') }}
    </a>
</div>

@endsection
