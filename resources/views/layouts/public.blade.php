@php
    $publicSchool  = \App\Models\School::where('tenant_id', tenant('id'))->first();
    $publicLogoUrl = $publicSchool?->logo_url;
    $publicName    = $publicSchool?->name ?? tenant()->name ?? 'School Portal';

    // Primary color system
    $hex  = $publicSchool?->setting('primary_color') ?: '#4338ca';
    $hex  = preg_match('/^#[0-9a-fA-F]{6}$/', $hex) ? $hex : '#4338ca';
    [$r, $g, $b] = [hexdec(substr($hex,1,2)), hexdec(substr($hex,3,2)), hexdec(substr($hex,5,2))];
    $dark  = sprintf('#%02x%02x%02x', (int)($r*0.78), (int)($g*0.78), (int)($b*0.78));
    $light = sprintf('#%02x%02x%02x', (int)(255-((255-$r)*0.12)), (int)(255-((255-$g)*0.12)), (int)(255-((255-$b)*0.12)));
@endphp
@php $isRtl = app()->getLocale() === 'ur'; @endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" @if($isRtl) dir="rtl" @endif class="h-full bg-slate-50">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $publicName }} — {{ __('public.issue_portal') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <style>
        :root {
            --p:       {{ $hex }};
            --p-dark:  {{ $dark }};
            --p-light: {{ $light }};
        }

        /* Solid primary — buttons, chat bubbles */
        .bg-blue-600                { background-color: var(--p) !important; }
        .hover\:bg-blue-700:hover   { background-color: var(--p-dark) !important; }

        /* Light tint — "new" status badge background */
        .bg-blue-100                { background-color: var(--p-light) !important; }

        /* Primary text — links, badge labels */
        .text-blue-600,
        .text-blue-700              { color: var(--p) !important; }
        .hover\:text-blue-800:hover { color: var(--p-dark) !important; }

        /* Focus rings */
        .focus\:ring-blue-500:focus { --tw-ring-color: var(--p) !important; }

        /* Header initial badge */
        .portal-badge               { background-color: var(--p); }
    </style>
    @if($isRtl)
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body, input, textarea, select, button, p, h1, h2, h3, h4, h5, span, label, a {
            font-family: 'Noto Nastaliq Urdu', serif !important;
            line-height: 2.4 !important;
        }
        body { direction: rtl; text-align: right; }
        .flex { flex-direction: row-reverse; }
        .items-center { align-items: center; }
        input, textarea { text-align: right; }
        /* Nastaliq script needs generous vertical space — prevent overlapping */
        p, li, label, span, div { line-height: 2.4; }
        h1 { line-height: 2.2; }
        h2, h3 { line-height: 2.2; }
        input, textarea, select { line-height: 2.2; padding-top: 0.6rem; padding-bottom: 0.6rem; }
    </style>
    @endif
</head>
<body class="h-full flex flex-col min-h-screen">

    <header class="bg-white border-b border-slate-200">
        <div class="max-w-2xl mx-auto px-4 h-14 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                @if($publicLogoUrl)
                    <img src="{{ $publicLogoUrl }}" alt="{{ $publicName }}"
                         class="h-8 w-auto object-contain">
                @else
                    <div class="portal-badge w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold select-none">
                        {{ strtoupper(substr($publicName, 0, 1)) }}
                    </div>
                @endif
                <span class="font-semibold text-slate-800 text-sm">{{ $publicName }}</span>
            </a>
            <div class="flex items-center gap-1 text-xs">
                <a href="{{ route('tenant.public.locale', 'en') }}"
                   class="px-2 py-1 rounded {{ app()->getLocale() === 'en' ? 'bg-blue-600 text-white' : 'text-slate-500 hover:text-slate-700' }}">
                    EN
                </a>
                <a href="{{ route('tenant.public.locale', 'ur') }}"
                   class="px-2 py-1 rounded font-medium {{ app()->getLocale() === 'ur' ? 'bg-blue-600 text-white' : 'text-slate-500 hover:text-slate-700' }}">
                    اردو
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1 py-10">
        <div class="max-w-2xl mx-auto px-4">
            @yield('content')
        </div>
    </main>

    <footer class="py-5 border-t border-slate-100 text-center text-xs text-slate-400">
        {{ $publicName }} &middot; {{ __('public.issue_tracking') }}
    </footer>

    @stack('scripts')
</body>
</html>
