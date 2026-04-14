@extends('layouts.central')

@section('title', 'Contact Us')
@section('description', 'Get in touch with the ElifLammeem team to learn more or request a demo.')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">

    {{-- Page Header --}}
    <div class="mb-12">
        <p class="text-primary-600 font-bold text-sm uppercase tracking-[0.2em] mb-3">Get in touch</p>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight">
            Contact Us
        </h1>
        <p class="mt-4 text-slate-500 text-lg font-medium">
            Tell us about your school and we'll get back to you within one business day.
        </p>
    </div>

    {{-- Success message --}}
    @if(session('ok'))
        <div class="mb-8 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-6 py-4 font-medium">
            {{ session('ok') }}
        </div>
    @endif

    {{-- Error summary --}}
    @if($errors->any())
        <div class="mb-8 bg-red-50 border border-red-200 text-red-700 rounded-xl px-6 py-4 text-sm font-medium">
            Please correct the errors below and try again.
        </div>
    @endif

    <form method="POST" action="{{ url('/contact') }}" class="space-y-6">
        @csrf

        {{-- Honeypot --}}
        <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">

        {{-- Full Name --}}
        <div>
            <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                class="w-full rounded-xl border @error('name') border-red-400 bg-red-50 @else border-slate-300 @enderror px-4 py-3 text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                placeholder="Jane Smith"
                autocomplete="name"
            >
            @error('name')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                class="w-full rounded-xl border @error('email') border-red-400 bg-red-50 @else border-slate-300 @enderror px-4 py-3 text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                placeholder="jane@school.edu"
                autocomplete="email"
            >
            @error('email')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Phone --}}
        <div>
            <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1.5">Phone <span class="text-slate-400 font-normal">(optional)</span></label>
            <input
                type="tel"
                id="phone"
                name="phone"
                value="{{ old('phone') }}"
                class="w-full rounded-xl border @error('phone') border-red-400 bg-red-50 @else border-slate-300 @enderror px-4 py-3 text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                placeholder="+60 12 345 6789"
                autocomplete="tel"
            >
            @error('phone')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- School Name --}}
        <div>
            <label for="school_name" class="block text-sm font-semibold text-slate-700 mb-1.5">School Name <span class="text-slate-400 font-normal">(optional)</span></label>
            <input
                type="text"
                id="school_name"
                name="school_name"
                value="{{ old('school_name') }}"
                class="w-full rounded-xl border @error('school_name') border-red-400 bg-red-50 @else border-slate-300 @enderror px-4 py-3 text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                placeholder="Greenfield International School"
            >
            @error('school_name')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- City --}}
        <div>
            <label for="city" class="block text-sm font-semibold text-slate-700 mb-1.5">City <span class="text-slate-400 font-normal">(optional)</span></label>
            <input
                type="text"
                id="city"
                name="city"
                value="{{ old('city') }}"
                class="w-full rounded-xl border @error('city') border-red-400 bg-red-50 @else border-slate-300 @enderror px-4 py-3 text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                placeholder="Kuala Lumpur"
            >
            @error('city')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Package --}}
        <div>
            <label for="package" class="block text-sm font-semibold text-slate-700 mb-1.5">Package Interest <span class="text-red-500">*</span></label>
            <select
                id="package"
                name="package"
                class="w-full rounded-xl border @error('package') border-red-400 bg-red-50 @else border-slate-300 @enderror px-4 py-3 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition bg-white"
            >
                @php $selectedPackage = old('package', request('package')); @endphp
                <option value="" disabled {{ $selectedPackage ? '' : 'selected' }}>Select a package...</option>
                <option value="starter"    {{ $selectedPackage === 'starter'    ? 'selected' : '' }}>Starter</option>
                <option value="growth"     {{ $selectedPackage === 'growth'     ? 'selected' : '' }}>Growth</option>
                <option value="pro"        {{ $selectedPackage === 'pro'        ? 'selected' : '' }}>Pro</option>
                <option value="enterprise" {{ $selectedPackage === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                <option value="custom"     {{ $selectedPackage === 'custom'     ? 'selected' : '' }}>Custom / Not Sure</option>
            </select>
            @error('package')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Message --}}
        <div>
            <label for="message" class="block text-sm font-semibold text-slate-700 mb-1.5">Message <span class="text-slate-400 font-normal">(optional)</span></label>
            <textarea
                id="message"
                name="message"
                rows="4"
                class="w-full rounded-xl border @error('message') border-red-400 bg-red-50 @else border-slate-300 @enderror px-4 py-3 text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition resize-none"
                placeholder="Tell us a bit about your school and what you're looking for..."
                maxlength="1000"
            >{{ old('message') }}</textarea>
            @error('message')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Turnstile (hidden in local) --}}
        @if(!app()->environment('local'))
        <div>
            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
            @error('cf-turnstile-response')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
        @endif

        {{-- Submit --}}
        <div class="pt-2">
            <button
                type="submit"
                class="w-full bg-primary-600 text-white font-bold text-base px-8 py-4 rounded-2xl hover:bg-primary-700 hover:shadow-xl hover:shadow-primary-200 transition-all active:scale-[0.98]"
            >
                Send Enquiry
            </button>
        </div>

        <p class="text-center text-xs text-slate-400 font-medium">
            By submitting this form you agree to our
            <a href="{{ url('/terms') }}" class="underline hover:text-primary-600">Terms &amp; Conditions</a>
            and
            <a href="{{ url('/privacy') }}" class="underline hover:text-primary-600">Privacy Policy</a>.
        </p>
    </form>
</div>
@endsection
