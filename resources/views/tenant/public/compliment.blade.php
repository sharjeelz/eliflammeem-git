@extends('layouts.public')

@section('content')

{{-- Flash success --}}
@if(session('ok'))
<div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-2xl px-5 py-4 text-sm font-medium">
    {{ session('ok') }}
</div>
@endif

<div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-5" style="background: linear-gradient(135deg, var(--p-dark) 0%, var(--p) 100%);">
        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
    </div>
    <h1 class="text-3xl font-extrabold text-slate-900 mb-2">{{ __('public.share_compliment') }}</h1>
    <p class="text-slate-500 text-base">{{ __('public.compliment_subheading') }}</p>
</div>

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
    <form method="POST" action="{{ route('tenant.public.compliment.store') }}" class="flex flex-col gap-5">
        @csrf

        {{-- Honeypot --}}
        <div style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true" tabindex="-1">
            <label for="hp_comp_website">Leave this empty</label>
            <input type="text" id="hp_comp_website" name="website" value="" autocomplete="off" tabindex="-1">
        </div>

        {{-- Access Code --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                {{ __('public.access_code') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" name="code" value="{{ old('code') }}"
                placeholder="{{ __('public.access_code_placeholder') }}"
                class="w-full border {{ $errors->has('code') ? 'border-red-400 bg-red-50' : 'border-slate-300' }} rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent transition font-mono tracking-wider uppercase"
                style="--tw-ring-color: var(--p);"
                autocomplete="off" required>
            @error('code')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-slate-400">{{ __('public.compliment_code_helper') }}</p>
        </div>

        {{-- Category --}}
        @if($categories->isNotEmpty())
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">{{ __('public.compliment_related') }}</label>
            <select name="category_id"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 transition bg-white"
                style="--tw-ring-color: var(--p);">
                <option value="">{{ __('public.compliment_select_area') }}</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Message --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                {{ __('public.your_message') }} <span class="text-red-500">*</span>
            </label>
            <textarea name="message" rows="5" required
                placeholder="{{ __('public.compliment_placeholder') }}"
                class="w-full border {{ $errors->has('message') ? 'border-red-400 bg-red-50' : 'border-slate-300' }} rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 transition resize-none"
                style="--tw-ring-color: var(--p);">{{ old('message') }}</textarea>
            @error('message')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Cloudflare Turnstile (skipped in local dev) --}}
        @if(!app()->environment('local'))
        <div>
            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
            @error('cf-turnstile-response')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        @endif

        <button type="submit"
            class="w-full text-white font-semibold text-sm py-3 rounded-xl hover:opacity-90 transition-opacity shadow-md"
            style="background: linear-gradient(135deg, var(--p-dark) 0%, var(--p) 100%);">
            {{ __('public.compliment_submit') }}
        </button>
    </form>
</div>

<div class="text-center mt-6">
    <a href="{{ route('tenant.public.home') }}" class="text-sm text-slate-500 hover:text-slate-700 font-medium">
        {{ __('public.back_to_portal') }}
    </a>
</div>

@endsection
