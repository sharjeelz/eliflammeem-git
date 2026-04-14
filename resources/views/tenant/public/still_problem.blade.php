@php
    $school      = \App\Models\School::where('tenant_id', tenant('id'))->first();
    $homeName    = $school?->name ?? 'School Portal';
    $homeLogoUrl = $school?->logo_url;
    $hex         = $school?->setting('primary_color') ?: '#4338ca';
    $hex         = preg_match('/^#[0-9a-fA-F]{6}$/', $hex) ? $hex : '#4338ca';
    [$r, $g, $b] = [hexdec(substr($hex,1,2)), hexdec(substr($hex,3,2)), hexdec(substr($hex,5,2))];
    $dark = sprintf('#%02x%02x%02x', (int)($r*0.78), (int)($g*0.78), (int)($b*0.78));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Issue Feedback — {{ $homeName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --p: {{ $hex }}; --p-dark: {{ $dark }}; }
        .gradient-hero { background: linear-gradient(135deg, var(--p-dark) 0%, var(--p) 100%); }
    </style>
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

        @if($success)
            <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 mb-3">We've Got Your Feedback</h1>
            <p class="text-slate-600 text-sm leading-relaxed">{{ $message }}</p>

            @if(!empty($issue))
                <div class="mt-5 bg-slate-50 rounded-xl p-4 text-left border border-slate-200">
                    <div class="text-xs text-slate-400 uppercase font-semibold tracking-wide mb-1">Your Issue</div>
                    <div class="font-semibold text-slate-800 text-sm">{{ $issue->title }}</div>
                    <div class="text-xs text-slate-400 mt-1">Tracking ID: {{ $issue->public_id }}</div>
                </div>
            @endif
        @else
            <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 mb-3">Link Unavailable</h1>
            <p class="text-slate-500 text-sm">{{ $message }}</p>
        @endif

        <a href="{{ url('/') }}"
           class="inline-block mt-8 gradient-hero text-white font-semibold text-sm px-7 py-3 rounded-xl hover:opacity-90 transition-opacity shadow">
            Back to Portal
        </a>
    </div>

    <p class="text-xs text-slate-400 mt-5">{{ $homeName }}</p>
</body>
</html>
