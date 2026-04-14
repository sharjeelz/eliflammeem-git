@extends('layouts.public')

@section('content')

@if($schoolInactive ?? false)
<div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-5 py-4 flex items-start gap-3">
    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <div>
        <p class="font-semibold text-red-800 text-sm mb-0.5">{{ __('public.anon_suspended_title') }}</p>
        <p class="text-red-700 text-xs leading-relaxed">{{ __('public.anon_suspended_msg') }}</p>
    </div>
</div>
@elseif(!$allowAnonymous)
<div class="mb-6 rounded-xl bg-amber-50 border border-amber-200 px-5 py-4 flex items-start gap-3">
    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <div>
        <p class="font-semibold text-amber-800 text-sm mb-0.5">{{ __('public.anon_disabled_title') }}</p>
        <p class="text-amber-700 text-xs leading-relaxed">{{ __('public.anon_disabled_msg') }}</p>
    </div>
</div>
@endif

{{-- Confidentiality notice --}}
<div class="mb-6 rounded-xl bg-slate-800 px-5 py-4 flex items-start gap-3 text-white">
    <svg class="w-5 h-5 text-slate-300 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
    </svg>
    <div>
        <p class="font-semibold text-sm mb-0.5">{{ __('public.anon_confidential_title') }}</p>
        <p class="text-slate-300 text-xs leading-relaxed">{{ __('public.anon_confidential_msg') }}</p>
    </div>
</div>

@if ($errors->any())
    <div class="mb-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3.5 text-sm text-red-700 flex items-start gap-3">
        <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $errors->first() }}
    </div>
@endif

<div class="mb-6">
    <h1 class="text-xl font-bold text-slate-800">{{ __('public.anon_heading') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('public.anon_subheading') }}</p>
</div>

@if($allowAnonymous && !($schoolInactive ?? false))
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <form method="POST" action="{{ route('tenant.public.anonymous.store') }}" enctype="multipart/form-data"
          class="px-6 py-6 space-y-5">
        @csrf

        {{-- Honeypot — invisible to humans, bots fill it --}}
        <div style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true" tabindex="-1">
            <label for="hp_anon_website">Leave this empty</label>
            <input type="text" id="hp_anon_website" name="website" value="" autocomplete="off" tabindex="-1">
        </div>

        @if($categories->count())
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">{{ __('public.category') }}</label>
            <select name="category_id"
                    class="w-full rounded-xl border @error('category_id') border-red-400 bg-red-50 @else border-slate-300 @enderror px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                <option value="">{{ __('public.select_category_optional') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        @endif

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                {{ __('public.your_message') }} <span class="text-red-500">*</span>
            </label>
            <textarea name="description" rows="5"
                      placeholder="{{ __('public.message_placeholder') }}"
                      class="w-full rounded-xl border @error('description') border-red-400 bg-red-50 @else border-slate-300 @enderror px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none transition">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                {{ __('public.attachments') }} <span class="text-slate-400 font-normal">{{ __('public.attachments_helper') }}</span>
            </label>
            <div class="border border-dashed @error('attachments') border-red-400 bg-red-50 @else border-slate-300 @enderror rounded-xl p-4 text-center bg-slate-50">
                <input type="file" name="attachments[]" multiple
                       class="w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                <p class="mt-2 text-xs text-slate-400">{{ __('public.attachments_types') }}</p>
            </div>
            @error('attachments')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('attachments.*')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Cloudflare Turnstile CAPTCHA (skipped in local dev) --}}
        @if(!app()->environment('local'))
        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
        @error('cf-turnstile-response')
            <p class="text-xs text-red-600 -mt-3">{{ $message }}</p>
        @enderror
        @endif

        <button type="submit"
                class="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold text-base py-3.5 rounded-2xl transition-colors shadow-md mt-2">
            {{ __('public.submit_anonymously') }}
        </button>
    </form>
</div>

<p class="text-center text-xs text-slate-400 mt-4">
    {{ __('public.tracking_notice') }}
</p>
@endif

{{-- Track existing anonymous issue --}}
<div class="mt-8 bg-slate-50 border border-slate-200 rounded-2xl px-6 py-5">
    <p class="text-sm font-semibold text-slate-700 mb-1">{{ __('public.already_submitted') }}</p>
    <p class="text-xs text-slate-400 mb-3">{{ __('public.tracking_id_desc') }}</p>
    <form onsubmit="event.preventDefault(); var id = this.tracking_id.value.trim(); if(id) window.location = '{{ url('/status') }}/' + id.toUpperCase();"
          class="flex gap-2">
        <input name="tracking_id" placeholder="{{ __('public.tracking_placeholder') }}"
               class="flex-1 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
               maxlength="8" autocomplete="off">
        <button type="submit"
                class="bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors whitespace-nowrap">
            {{ __('public.track_btn') }}
        </button>
    </form>
</div>

<div class="mt-4 text-center">
    <a href="{{ url('/') }}" class="text-sm text-blue-600 hover:text-blue-800 transition-colors">{{ __('public.back_to_portal') }}</a>
</div>

@endsection
