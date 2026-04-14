@extends('layouts.public')

@section('content')
<div class="text-center py-16">
    <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z" />
        </svg>
    </div>
    <h2 class="text-lg font-semibold text-slate-700 mb-2">{{ __('public.chatbot_unavailable') }}</h2>
    <p class="text-sm text-slate-500 max-w-sm mx-auto">
        {{ __('public.chatbot_unavailable_msg') }}
    </p>
    <a href="{{ url('/') }}" class="mt-6 inline-block text-sm text-blue-600 hover:text-blue-800">
        {{ __('public.back_to_portal') }}
    </a>
</div>
@endsection
