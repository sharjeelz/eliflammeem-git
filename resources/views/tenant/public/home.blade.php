@php
    $homeSchool      = \App\Models\School::where('tenant_id', tenant('id'))->first();
    $homeLogoUrl     = $homeSchool?->logo_url;
    $homeName        = $homeSchool?->name ?? tenant()->name ?? 'School Portal';
    $homeWelcome     = $homeSchool?->setting('welcome_message') ?: __('public.welcome_default');
    $homeEmail       = $homeSchool?->setting('contact_email');
    $homePhone       = $homeSchool?->setting('contact_phone');
    $homeAddress     = $homeSchool?->setting('address');
    $homeWebsite     = $homeSchool?->setting('website_url');
    $homeAllowSubmit    = (bool) ($homeSchool?->setting('allow_new_issues', true) ?? true);
    $homeAllowAnonymous = (bool) ($homeSchool?->setting('allow_anonymous_issues', true) ?? true);
    $homeChatbotEnabled = (bool) ($homeSchool?->setting('chatbot_enabled', false) ?? false);
    $homeSchoolInactive = $homeSchool?->status === 'inactive';

    $hex = $homeSchool?->setting('primary_color') ?: '#4338ca';
    $hex = preg_match('/^#[0-9a-fA-F]{6}$/', $hex) ? $hex : '#4338ca';
    [$r, $g, $b] = [hexdec(substr($hex,1,2)), hexdec(substr($hex,3,2)), hexdec(substr($hex,5,2))];
    $dark      = sprintf('#%02x%02x%02x', (int)($r*0.78), (int)($g*0.78), (int)($b*0.78));
    $light     = sprintf('#%02x%02x%02x', (int)(255-((255-$r)*0.12)), (int)(255-((255-$g)*0.12)), (int)(255-((255-$b)*0.12)));
    $lightMid  = sprintf('#%02x%02x%02x', (int)(255-((255-$r)*0.22)), (int)(255-((255-$g)*0.22)), (int)(255-((255-$b)*0.22)));
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" @if(app()->getLocale() === 'ur') dir="rtl" @endif>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $homeName }} — {{ __('public.issue_portal') }}</title>
    <meta name="description" content="Submit and track school concerns at {{ $homeName }}." />
    <script src="https://cdn.tailwindcss.com"></script>
    @if(!app()->environment('local'))
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
    <style>
        :root {
            --p:       {{ $hex }};
            --p-dark:  {{ $dark }};
            --p-light: {{ $light }};
            --p-mid:   {{ $lightMid }};
        }
        .grad      { background: linear-gradient(135deg, var(--p-dark) 0%, var(--p) 100%); }
        .grad-text {
            background: linear-gradient(135deg, var(--p-dark), var(--p));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .text-p      { color: var(--p) !important; }
        .bg-p-light  { background-color: var(--p-light) !important; }
        .border-p    { border-color: var(--p-mid) !important; }
        input:focus, textarea:focus, select:focus {
            outline: none;
            box-shadow: 0 0 0 2px var(--p);
            border-color: transparent;
        }
        /* Ensure body fills the viewport so footer always stays at the very bottom */
        html, body { height: 100%; }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        /* The two-column wrapper grows to fill all space between navbar and footer */
        #page-body {
            flex: 1 0 auto;
        }
        footer {
            flex-shrink: 0;
        }
    </style>
    @if(app()->getLocale() === 'ur')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body, input, textarea, select, button, p, h1, h2, h3, h4, h5, span, label, a {
            font-family: 'Noto Nastaliq Urdu', serif !important;
            line-height: 2.4 !important;
        }
        body { direction: rtl; text-align: right; }
        input, textarea { text-align: right; }
        p, li, label, span, div { line-height: 2.4; }
        h1 { line-height: 2.2; } h2, h3 { line-height: 2.2; }
        input, textarea, select { line-height: 2.2; padding-top: 0.6rem; padding-bottom: 0.6rem; }
    </style>
    @endif
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

{{-- ─── NAVBAR (fixed) ─────────────────────────────────────────────────────── --}}
<header class="fixed top-0 inset-x-0 z-50 bg-white/95 backdrop-blur-sm border-b border-slate-200 shadow-sm">
    {{-- Main nav row --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between gap-4">
        <a href="#" class="flex items-center gap-3 flex-shrink-0">
            @if($homeLogoUrl)
                <img src="{{ $homeLogoUrl }}" alt="{{ $homeName }}" class="h-9 w-auto object-contain">
            @else
                <div class="w-8 h-8 rounded-xl grad flex items-center justify-center text-white font-extrabold text-sm shadow-md">
                    {{ strtoupper(substr($homeName, 0, 1)) }}
                </div>
            @endif
            <span class="font-bold text-slate-900 text-sm hidden sm:block">{{ $homeName }}</span>
        </a>

        {{-- Nav links --}}
        <div class="flex items-center gap-1">
            @if($homeChatbotEnabled && !$homeSchoolInactive)
            <a href="{{ route('tenant.public.chatbot') }}"
               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/>
                </svg>
                {{ __('public.ask_ai') }}
            </a>
            @endif

            <a href="{{ url('admin/login') }}"
               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                {{ __('public.go_to_staff_login') }}
            </a>

            <div class="w-px h-4 bg-slate-200 mx-1"></div>

            <a href="{{ route('tenant.public.locale', 'en') }}"
               class="px-2 py-1 rounded text-xs {{ app()->getLocale() === 'en' ? 'grad text-white' : 'text-slate-500 hover:text-slate-700' }}">EN</a>
            <a href="{{ route('tenant.public.locale', 'ur') }}"
               class="px-2 py-1 rounded text-xs font-medium {{ app()->getLocale() === 'ur' ? 'grad text-white' : 'text-slate-500 hover:text-slate-700' }}">اردو</a>
        </div>
    </div>

</header>

{{-- ─── PAGE BODY: two-column grid, grows to fill space between navbar & footer ── --}}
<div id="page-body" class="pt-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- CSS Grid: sidebar fixed at 360px, main column takes the rest.
             On mobile (< lg) it collapses to a single column, sidebar on top. --}}
        <div class="grid grid-cols-1 lg:grid-cols-[360px_1fr] gap-8 items-start">

            {{-- ══ LEFT SIDEBAR ════════════════════════════════════════════════ --}}
            {{-- sticky top-20 = navbar (64px) + 16px breathing room --}}
            <aside class="flex flex-col gap-4 lg:sticky lg:top-20 lg:self-start">

                {{-- School branding card --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="grad px-6 py-8 text-center">
                        @if($homeLogoUrl)
                            <img src="{{ $homeLogoUrl }}" alt="{{ $homeName }}" class="h-16 w-auto object-contain mx-auto mb-4">
                        @else
                            <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center mx-auto mb-4">
                                <span class="text-white font-extrabold text-3xl">{{ strtoupper(substr($homeName, 0, 1)) }}</span>
                            </div>
                        @endif
                        <h1 class="text-white font-extrabold text-xl leading-snug">{{ $homeName }}</h1>
                        <p class="text-white/75 text-sm mt-2 leading-relaxed">{{ $homeWelcome }}</p>
                        <div class="inline-flex items-center gap-1.5 mt-4 bg-white/15 text-white/90 text-xs font-medium px-3 py-1.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                            {{ __('public.hero_badge') }}
                        </div>
                    </div>

                    {{-- Contact info --}}
                    @if($homeEmail || $homePhone || $homeAddress)
                    <div class="px-5 py-4 border-t border-slate-100 space-y-2">
                        @if($homeAddress)
                            <p class="text-sm text-slate-500 leading-relaxed">{{ $homeAddress }}</p>
                        @endif
                        @if($homeEmail)
                            <a href="mailto:{{ $homeEmail }}" class="flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800 transition-colors">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $homeEmail }}
                            </a>
                        @endif
                        @if($homePhone)
                            <a href="tel:{{ $homePhone }}" class="flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800 transition-colors">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ $homePhone }}
                            </a>
                        @endif
                    </div>
                    @endif
                </div>


                {{-- Quick action links --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">

                    {{-- Share a compliment --}}
                    @if(!$homeSchoolInactive)
                    <a href="{{ route('tenant.public.compliment') }}"
                       class="flex items-center gap-3 px-5 py-4 hover:bg-amber-50 transition-colors group">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-200 transition-colors">
                            <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-slate-800 group-hover:text-amber-700">{{ __('public.share_compliment') }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ __('public.compliment_subheading') }}</div>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @endif



                    {{-- School website --}}
                    @if($homeWebsite)
                    <a href="{{ $homeWebsite }}" target="_blank" rel="noopener"
                       class="flex items-center gap-3 px-5 py-4 hover:bg-slate-50 transition-colors group">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-slate-800">School Website</div>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @endif

                </div>{{-- /quick action links --}}

                {{-- Announcements card (only when announcements exist) --}}
                @if(isset($announcements) && $announcements->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg grad flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">
                            {{ app()->getLocale() === 'ur' ? 'تازہ خبریں' : "What We've Done" }}
                        </span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($announcements as $ann)
                        <div class="px-5 py-3.5">
                            <div class="text-sm font-semibold text-slate-800 leading-snug">{{ $ann->title }}</div>
                            @if($ann->body)
                                <div class="text-xs text-slate-500 mt-1 leading-relaxed line-clamp-2">{{ $ann->body }}</div>
                            @endif
                            <div class="flex items-center gap-2 mt-1.5">
                                @if($ann->category)
                                    <span class="text-xs font-medium text-p">{{ $ann->category->name }}</span>
                                    <span class="text-slate-300">·</span>
                                @endif
                                <span class="text-xs text-slate-400">{{ $ann->published_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </aside>{{-- /left sidebar --}}

            {{-- ══ RIGHT MAIN CONTENT ══════════════════════════════════════════ --}}
            <main class="flex flex-col gap-6 min-w-0">

                {{-- Flash alerts --}}
                @if (session('error'))
                <div class="rounded-xl bg-red-50 border border-red-200 px-5 py-4 text-sm text-red-700 flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
                @endif

                @if (session('ok'))
                <div class="rounded-xl bg-green-50 border border-green-200 px-5 py-4 text-sm text-green-700 flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('ok') }}
                </div>
                @endif

                {{-- ── SUBMIT FORM ──────────────────────────────────────────── --}}
                <div id="submit" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="grad px-6 py-5 flex items-center gap-3">
                        <svg class="w-5 h-5 text-white/80 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span class="text-white font-bold text-lg">{{ __('public.submit_section_title') }}</span>
                        <span class="ml-auto text-white/60 text-sm">{{ __('public.for_parents_teachers') }}</span>
                    </div>

                    @if($homeSchoolInactive)
                    <div class="px-6 py-12 text-center">
                        <p class="text-slate-600 font-semibold text-base mb-1">{{ __('public.portal_suspended') }}</p>
                        <p class="text-slate-400 text-sm">{{ __('public.school_suspended_msg') }}</p>
                    </div>
                    @elseif(!$homeAllowSubmit)
                    <div class="px-6 py-12 text-center">
                        <p class="text-amber-600 font-semibold text-base mb-1">{{ __('public.submissions_closed') }}</p>
                        <p class="text-slate-400 text-sm">{{ __('public.submissions_closed_msg') }}</p>
                    </div>
                    @else

                    {{-- AI Chatbot teaser (above form) --}}
                    @if($homeChatbotEnabled)
                    <div class="mx-6 mt-5 rounded-xl bg-p-light border border-p px-4 py-3 flex items-center gap-3">
                        <svg class="w-5 h-5 text-p flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z" />
                        </svg>
                        <p class="text-sm text-slate-700 flex-1">Have questions?
                            <a href="{{ route('tenant.public.chatbot') }}" class="text-p font-semibold hover:underline">Ask our AI assistant →</a>
                        </p>
                    </div>
                    @endif

                    <form method="post" action="{{ route('tenant.public.submit') }}" enctype="multipart/form-data"
                          class="px-6 py-7 space-y-6">
                        @csrf

                        {{-- Honeypot: hidden from real users, bots fill it --}}
                        <div style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true" tabindex="-1">
                            <label for="hp_website">Leave this empty</label>
                            <input type="text" id="hp_website" name="website" value="" autocomplete="off" tabindex="-1">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                {{ __('public.your_message') }} <span class="text-red-500">*</span>
                            </label>
                            <textarea name="description" rows="5"
                                      placeholder="{{ __('public.message_placeholder') }}"
                                      class="w-full rounded-xl border @error('description') border-red-400 bg-red-50 @else border-slate-300 @enderror px-4 py-2.5 text-sm resize-none transition">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($categories->count())
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ __('public.category') }}</label>
                            <select name="category_id" class="w-full rounded-xl border @error('category_id') border-red-400 bg-red-50 @else border-slate-300 @enderror px-4 py-2.5 text-sm bg-white transition">
                                <option value="">{{ __('public.select_category') }}</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                {{ __('public.attachments') }}
                                <span class="text-slate-400 font-normal">{{ __('public.attachments_helper') }}</span>
                            </label>
                            <div class="border border-dashed @error('attachments') border-red-400 bg-red-50 @else border-slate-300 @enderror rounded-xl p-5 text-center hover:border-slate-400 transition-colors bg-slate-50">
                                <input type="file" name="attachments[]" multiple
                                       class="w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-p-light file:text-p cursor-pointer">
                                <p class="mt-2 text-xs text-slate-400">{{ __('public.attachments_types') }}</p>
                            </div>
                            @error('attachments')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('attachments.*')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Access code + anonymous toggle --}}
                        <div class="rounded-xl border @error('code') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror p-4 space-y-3">
                            <div id="code-field">
                                <label class="block text-sm font-semibold text-slate-700 mb-2">
                                    Access code or School/Student ID <span class="text-red-500">*</span>
                                </label>
                                <input name="code" id="code-input" value="{{ old('code') }}" autocomplete="one-time-code"
                                       placeholder="Enter your access code or student ID"
                                       class="w-full rounded-xl border @error('code') border-red-400 bg-red-50 @else border-slate-300 bg-white @enderror px-4 py-2.5 text-sm transition">
                                @error('code')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-sm text-slate-400">
                                    Teachers: no student ID?
                                    <button type="button" onclick="document.getElementById('get-code-panel').classList.toggle('hidden')"
                                            class="text-p hover:underline font-medium focus:outline-none">
                                        Get your access code
                                    </button>.
                                </p>

                            </div>

                            @if($homeAllowAnonymous)
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <input type="checkbox" name="anonymous" id="anonymous-checkbox" value="1"
                                       @checked(old('anonymous'))
                                       class="w-4 h-4 rounded border-slate-300 cursor-pointer"
                                       style="accent-color: var(--p)">
                                <span class="text-sm font-medium text-slate-700">
                                    @if(app()->getLocale() === 'ur')
                                        گمنام جمع کروائیں <span class="text-slate-400 font-normal">(کوڈ کی ضرورت نہیں)</span>
                                    @else
                                        Submit anonymously <span class="text-slate-400 font-normal">(no code required)</span>
                                    @endif
                                </span>
                            </label>
                            <div id="anon-note" class="hidden text-xs text-slate-500 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span style="color:red">
                                @if(app()->getLocale() === 'ur')
                                    آپ کی شناخت محفوظ ہے۔
                                    یہ پیغام براہ راست انتظامیہ تک پہنچے گا، جیسے کہ پرنسپل، وائس پرنسپل، برانچ مینیجر وغیرہ، اور وہ اس پر ضروری کارروائی کریں گے۔
                                @else
                                    Your identity is protected.
                                    This Message will directly go to the administration e.g Principal, Vice Principal, Branch Manager, etc. and they will take necessary action on it.
                                @endif
                                </span>
                            </div>
                            @endif
                        </div>

                        {{-- Turnstile widget (skipped in local dev) --}}
                        @if(!app()->environment('local'))
                        <div>
                            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
                            @error('cf-turnstile-response')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif

                        <button type="submit" class="w-full grad text-white font-bold text-base py-3.5 rounded-2xl hover:opacity-90 transition-opacity shadow-lg">
                            Send Feedback
                        </button>
                        <p class="text-center text-sm text-slate-400">{{ __('public.submission_notice') }}</p>
                    </form>

                    {{-- ── GET MY CODE PANEL (outside the main form to avoid nested-form breakage) ── --}}
                    <div id="get-code-panel" class="hidden px-6 pb-5">
                        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                            <p class="text-xs font-semibold text-blue-700 mb-3 uppercase tracking-wide">Get your access code</p>
                            <form method="POST" action="{{ url('/resend-code') }}" class="space-y-3">
                                @csrf
                                {{-- Teachers: enter email --}}
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">
                                        School email <span class="text-slate-400 font-normal">(teachers)</span>
                                    </label>
                                    <input type="email" name="email" value="{{ old('resend_email') }}"
                                           placeholder="your@school.edu"
                                           class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:outline-none">
                                </div>
                                <div class="flex items-center gap-2 text-xs text-slate-400">
                                    <div class="flex-1 border-t border-slate-200"></div>
                                    <span>or</span>
                                    <div class="flex-1 border-t border-slate-200"></div>
                                </div>
                                {{-- Parents: enter phone or school/student ID --}}
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Phone number</label>
                                        <input type="text" name="phone" value="{{ old('resend_phone') }}"
                                               placeholder="+966 5XX XXX XXX"
                                               class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">
                                            School / Student ID <span class="text-slate-400 font-normal">(parents)</span>
                                        </label>
                                        <input type="text" name="external_id" value="{{ old('resend_external_id') }}"
                                               placeholder="e.g. STU-001"
                                               class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:outline-none">
                                    </div>
                                </div>
                                @if(!app()->environment('local'))
                                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
                                @endif
                                <button type="submit"
                                        class="w-full rounded-xl bg-blue-600 text-white text-sm font-semibold py-2.5 hover:bg-blue-700 transition-colors">
                                    Send my access code
                                </button>
                            </form>
                        </div>
                    </div>

                    <script>
                    (function () {
                        var cb   = document.getElementById('anonymous-checkbox');
                        var field = document.getElementById('code-field');
                        var note  = document.getElementById('anon-note');
                        var input = document.getElementById('code-input');
                        if (!cb) return;

                        function toggle() {
                            if (cb.checked) {
                                field.style.display  = 'none';
                                if (note) note.classList.remove('hidden');
                                if (input) input.removeAttribute('required');
                            } else {
                                field.style.display  = '';
                                if (note) note.classList.add('hidden');
                            }
                        }

                        cb.addEventListener('change', toggle);
                        toggle(); // run on page load (for old() repopulation)
                    })();
                    </script>
                    @endif
                </div>{{-- /submit form --}}

                {{-- ── TRACK SECTION ────────────────────────────────────────── --}}
                <div id="track" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    @if(session('track_error'))
                    <div class="mx-6 mt-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        {{ session('track_error') }}
                    </div>
                    @endif
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                        <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span class="font-bold text-slate-900 text-lg">{{ __('public.track_section_title') }}</span>
                    </div>
                    <div class="px-6 py-6 space-y-4">
                        <p class="text-slate-500 text-sm">{{ __('public.track_section_desc') }}</p>
                        <form onsubmit="event.preventDefault(); var c=this.code.value.trim(); if(c){ window.location='{{ url('/status/by-code') }}/'+encodeURIComponent(c); }"
                              class="flex gap-3">
                            <input name="code" placeholder="{{ __('public.enter_access_code') }}" autocomplete="one-time-code"
                                   class="flex-1 min-w-0 rounded-xl border border-slate-300 px-4 py-2.5 text-sm transition">
                            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors whitespace-nowrap">
                                {{ __('public.view_my_issues') }}
                            </button>
                        </form>
                        <p class="text-sm text-slate-400">
                            {{ __('public.have_tracking_id') }}
                            <button type="button"
                                    onclick="document.getElementById('track-by-id-row').classList.toggle('hidden')"
                                    class="text-p hover:underline font-medium bg-transparent border-0 p-0 cursor-pointer text-sm">
                                {{ __('public.track_by_id') }}
                            </button>
                        </p>
                        <div id="track-by-id-row" class="hidden flex gap-2">
                            <input id="track-id-input" placeholder="{{ __('public.enter_tracking_id') }}"
                                   class="flex-1 min-w-0 rounded-xl border border-slate-300 px-4 py-2.5 text-sm transition">
                            <button type="button"
                                    onclick="var id=document.getElementById('track-id-input').value.trim();if(id)window.location='{{ url('/status') }}/'+encodeURIComponent(id);"
                                    class="bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors whitespace-nowrap">
                                {{ __('public.go') }}
                            </button>
                        </div>
                    </div>
                </div>{{-- /track section --}}

            </main>{{-- /right main --}}

        </div>{{-- /grid --}}
    </div>{{-- /max-w-7xl --}}
</div>{{-- /page-body --}}

{{-- ─── FOOTER ──────────────────────────────────────────────────────────────── --}}
<footer class="bg-slate-950 text-slate-500 py-8 mt-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <span class="text-slate-300 font-semibold text-sm">{{ $homeName }}</span>
        <nav class="flex items-center gap-5 text-xs flex-wrap justify-center">
            @if($homeAllowSubmit && !$homeSchoolInactive)
            <a href="#submit" class="hover:text-slate-300 transition-colors">{{ __('public.submit_issue') }}</a>
            @endif
            <a href="#track" class="hover:text-slate-300 transition-colors">{{ __('public.track_issue') }}</a>
            <a href="{{ url('admin/login') }}" class="hover:text-slate-300 transition-colors">{{ __('public.staff_login') }}</a>
            <a href="{{ route('tenant.manual') }}" target="_blank" class="hover:text-slate-300 transition-colors">User Manual</a>
            @if($homeWebsite)
            <a href="{{ $homeWebsite }}" target="_blank" rel="noopener" class="hover:text-slate-300 transition-colors">Website</a>
            @endif
        </nav>
        <p class="text-xs text-slate-600">&copy; {{ date('Y') }} {{ $homeName }}</p>
    </div>
</footer>

</body>
</html>
