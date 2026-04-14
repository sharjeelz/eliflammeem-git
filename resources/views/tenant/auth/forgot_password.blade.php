@php
    $hex  = $school?->setting('primary_color') ?: '#4338ca';
    $hex  = preg_match('/^#[0-9a-fA-F]{6}$/', $hex) ? $hex : '#4338ca';
    [$r, $g, $b] = [hexdec(substr($hex,1,2)), hexdec(substr($hex,3,2)), hexdec(substr($hex,5,2))];
    $dark  = sprintf('#%02x%02x%02x', (int)($r*0.78), (int)($g*0.78), (int)($b*0.78));
    $light = sprintf('#%02x%02x%02x', (int)(255-((255-$r)*0.12)), (int)(255-((255-$g)*0.12)), (int)(255-((255-$b)*0.12)));
    $name  = $school?->name ?? tenant()->name ?? 'School Portal';
    $logo  = $school?->logo_url;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forgot Password — {{ $name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --p: {{ $hex }}; --p-dark: {{ $dark }}; --p-light: {{ $light }}; }
        .bg-primary    { background-color: var(--p); }
        .text-primary  { color: var(--p) !important; }
        .border-primary { border-color: var(--p) !important; }
        .gradient-brand { background: linear-gradient(145deg, var(--p-dark) 0%, var(--p) 100%); }
        .focus-ring:focus {
            outline: none;
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--p) 30%, transparent);
            border-color: var(--p);
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--p-dark), var(--p));
            transition: opacity .15s;
        }
        .btn-primary-custom:hover { opacity: .88; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 flex">

{{-- Left panel --}}
<div class="hidden lg:flex lg:w-5/12 xl:w-1/2 gradient-brand flex-col justify-between p-12 relative overflow-hidden">
    <div class="absolute -top-24 -left-24 w-80 h-80 rounded-full bg-white/5"></div>
    <div class="absolute -bottom-32 -right-20 w-96 h-96 rounded-full bg-black/10"></div>
    <div class="relative">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
            @if($logo)
                <img src="{{ $logo }}" alt="{{ $name }}" class="h-12 w-auto object-contain bg-white/20 rounded-xl px-2 py-1">
            @else
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-white font-extrabold text-xl select-none">
                    {{ strtoupper(substr($name, 0, 1)) }}
                </div>
            @endif
            <span class="text-white font-bold text-lg tracking-tight">{{ $name }}</span>
        </a>
    </div>
    <div class="relative">
        <h1 class="text-3xl xl:text-4xl font-extrabold text-white leading-snug mb-4">
            Forgot your<br>password?
        </h1>
        <p class="text-white/70 text-base leading-relaxed max-w-sm">
            No problem. Enter your email and we'll send you a link to reset it.
        </p>
    </div>
    <div class="relative"></div>
</div>

{{-- Right panel --}}
<div class="flex-1 flex flex-col justify-center items-center px-6 py-16">

    <div class="lg:hidden flex items-center gap-3 mb-10">
        @if($logo)
            <img src="{{ $logo }}" alt="{{ $name }}" class="h-10 w-auto object-contain">
        @else
            <div class="w-10 h-10 rounded-xl gradient-brand flex items-center justify-center text-white font-extrabold text-base">
                {{ strtoupper(substr($name, 0, 1)) }}
            </div>
        @endif
        <span class="font-bold text-slate-900 text-base">{{ $name }}</span>
    </div>

    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-2xl shadow-slate-200 border border-slate-100 p-8 sm:p-10">

            <div class="mb-8">
                <h2 class="text-2xl font-extrabold text-slate-900 mb-1">Reset Password</h2>
                <p class="text-slate-500 text-sm">Enter your email and we'll send you a reset link.</p>
            </div>

            {{-- Success --}}
            @if(session('status'))
            <div class="mb-6 flex items-start gap-3 rounded-xl bg-green-50 border border-green-200 px-4 py-3.5 text-sm text-green-700">
                <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('status') }} Check your inbox — if the email exists you'll receive the link shortly.</span>
            </div>
            @endif

            {{-- Errors --}}
            @if($errors->any())
            <div class="mb-6 flex items-start gap-3 rounded-xl bg-red-50 border border-red-200 px-4 py-3.5 text-sm text-red-700">
                <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('tenant.admin.password.email') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Email address
                    </label>
                    <input id="email" type="email" name="email"
                           value="{{ old('email') }}"
                           autocomplete="email" required
                           placeholder="you@school.edu"
                           class="focus-ring w-full rounded-xl border border-slate-300 bg-slate-50
                                  px-4 py-3 text-sm text-slate-900 placeholder-slate-400
                                  transition-colors @error('email') border-red-400 bg-red-50 @enderror">
                </div>

                <button id="submit-btn" type="submit"
                        class="btn-primary-custom w-full text-white font-bold text-sm py-3.5 rounded-xl shadow-lg mt-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    Send Reset Link
                </button>

                @if(session('status'))
                <p id="resend-hint" class="text-center text-xs text-slate-400 mt-3">
                    Resend in <span id="countdown">30</span>s
                </p>
                @endif
            </form>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('tenant.login') }}" class="text-sm text-slate-500 hover:text-slate-800 transition-colors">
                ← Back to Sign In
            </a>
        </div>
    </div>
</div>

@if(session('status'))
<script>
(function () {
    var btn       = document.getElementById('submit-btn');
    var countdown = document.getElementById('countdown');
    var hint      = document.getElementById('resend-hint');
    var seconds   = 30;

    btn.disabled = true;

    var timer = setInterval(function () {
        seconds--;
        if (countdown) countdown.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(timer);
            btn.disabled = false;
            if (hint) hint.textContent = 'You can resend now.';
        }
    }, 1000);
})();
</script>
@endif

</body>
</html>
