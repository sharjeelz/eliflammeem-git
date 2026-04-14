@php
    $homeLogoUrl = $school?->logo_url;
    $homeName    = $school?->name ?? 'School Portal';
    $hex         = $school?->setting('primary_color') ?: '#4338ca';
    $hex         = preg_match('/^#[0-9a-fA-F]{6}$/', $hex) ? $hex : '#4338ca';
    [$r, $g, $b] = [hexdec(substr($hex,1,2)), hexdec(substr($hex,3,2)), hexdec(substr($hex,5,2))];
    $dark = sprintf('#%02x%02x%02x', (int)($r*0.78), (int)($g*0.78), (int)($b*0.78));

    $ratingColors = [1 => '#ef4444', 2 => '#f97316', 3 => '#f59e0b', 4 => '#84cc16', 5 => '#22c55e'];
    $ratingLabels = [
        1 => __('public.rating_1'),
        2 => __('public.rating_2'),
        3 => __('public.rating_3'),
        4 => __('public.rating_4'),
        5 => __('public.rating_5'),
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" @if(app()->getLocale() === 'ur') dir="rtl" @endif>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ __('public.thank_you') }} — {{ $homeName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --p: {{ $hex }}; --p-dark: {{ $dark }}; }
        .gradient-hero { background: linear-gradient(135deg, var(--p-dark) 0%, var(--p) 100%); }
    </style>
    @if(app()->getLocale() === 'ur')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body, input, textarea, select, button { font-family: 'Noto Nastaliq Urdu', serif !important; }
        body { direction: rtl; text-align: right; }
    </style>
    @endif
</head>
<body class="bg-slate-50 min-h-screen flex flex-col items-center justify-center p-6">

    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 max-w-md w-full p-10 text-center">

        {{-- Logo --}}
        <div class="flex justify-center mb-6">
            @if($homeLogoUrl)
                <img src="{{ $homeLogoUrl }}" alt="{{ $homeName }}" class="h-10 object-contain">
            @else
                <div class="w-12 h-12 rounded-2xl gradient-hero flex items-center justify-center text-white font-extrabold text-lg shadow">
                    {{ strtoupper(substr($homeName, 0, 1)) }}
                </div>
            @endif
        </div>

        @if($already)
            {{-- Already rated --}}
            <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 mb-2">{{ __('public.feedback_already') }}</h1>
            <p class="text-slate-500">{{ __('public.feedback_already_msg') }}</p>
        @else
            {{-- Success --}}
            @php $color = $ratingColors[$rating] ?? '#22c55e'; $label = $ratingLabels[$rating] ?? ''; @endphp
            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5 text-4xl font-extrabold text-white shadow-lg"
                 style="background-color: {{ $color }}">
                {{ $rating }}
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 mb-2">{{ __('public.thank_you') }}</h1>
            <p class="text-slate-600 mb-1">You rated: <strong>{{ $label }}</strong></p>
            <p class="text-slate-400 text-sm">Your feedback helps {{ $homeName }} keep improving.</p>
        @endif

        <a href="{{ url('/') }}"
           class="inline-block mt-8 gradient-hero text-white font-semibold text-sm px-7 py-3 rounded-xl hover:opacity-90 transition-opacity shadow">
            {{ __('public.back_to_portal_btn') }}
        </a>
    </div>

    <p class="text-xs text-slate-400 mt-5">{{ $homeName }}</p>
</body>
</html>
