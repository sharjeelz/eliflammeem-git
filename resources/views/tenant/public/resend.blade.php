@extends('layouts.public')

@section('content')

<div class="max-w-md mx-auto">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">Get your access code</h1>
        <p class="mt-1 text-sm text-slate-500">
            Enter any one of the details below and we'll send your access code to your registered email or phone.
        </p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="px-5 py-5">
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="post" action="{{ route('tenant.public.resend.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        School email <span class="text-slate-400 font-normal">(recommended for teachers)</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="your@school.edu"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <div class="flex-1 border-t border-slate-200"></div><span>or</span><div class="flex-1 border-t border-slate-200"></div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Phone number
                        </label>
                        <input name="phone" value="{{ old('phone') }}"
                               placeholder="+966 5XX XXX XXX"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            School / Student ID <span class="text-slate-400 font-normal">(parents)</span>
                        </label>
                        <input name="external_id" value="{{ old('external_id') }}"
                               placeholder="e.g. STU-001"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <p class="text-xs text-slate-400">Provide at least one. We'll send the code to your registered contact details.</p>

                @if(!app()->environment('local'))
                <div>
                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
                    @error('cf-turnstile-response')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <div class="flex items-center gap-4 pt-1">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                        {{ __('public.send_code') }}
                    </button>
                    <a href="{{ url('/') }}" class="text-sm text-slate-400 hover:text-slate-600 transition-colors">
                        {{ __('public.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
