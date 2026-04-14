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
    <title>Two-Factor Authentication — {{ $name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --p: {{ $hex }}; --p-dark: {{ $dark }}; --p-light: {{ $light }}; }
        .gradient-brand { background: linear-gradient(145deg, var(--p-dark) 0%, var(--p) 100%); }
        .focus-ring:focus {
            outline: none;
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--p) 30%, transparent);
            border-color: var(--p);
        }
        .btn-brand {
            background: linear-gradient(135deg, var(--p-dark), var(--p));
            transition: opacity .15s;
        }
        .btn-brand:hover { opacity: .88; }
        .text-brand { color: var(--p) !important; }
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
        <div class="w-16 h-16 rounded-2xl bg-white/15 flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
        </div>
        <h2 class="text-white font-extrabold text-3xl leading-tight mb-3">
            Two-Factor<br>Authentication
        </h2>
        <p class="text-white/70 text-base leading-relaxed">
            Your account is protected with an extra layer of security. Enter the code from your authenticator app to continue.
        </p>
    </div>

    <p class="relative text-white/40 text-sm">&copy; {{ date('Y') }} {{ $name }}</p>
</div>

{{-- Right panel — challenge form --}}
<div class="flex-1 flex items-center justify-center p-6 sm:p-10">
    <div class="w-full max-w-md">

        {{-- Mobile logo --}}
        <div class="lg:hidden mb-8 flex items-center gap-3">
            @if($logo)
                <img src="{{ $logo }}" alt="{{ $name }}" class="h-10 w-auto object-contain">
            @else
                <div class="w-10 h-10 rounded-xl gradient-brand flex items-center justify-center text-white font-extrabold text-lg">
                    {{ strtoupper(substr($name, 0, 1)) }}
                </div>
            @endif
            <span class="font-bold text-slate-800 text-lg">{{ $name }}</span>
        </div>

        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Verify your identity</h1>
            <p class="text-slate-500 text-sm">Open your authenticator app and enter the 6-digit code.</p>
        </div>

        @if($errors->any())
        <div class="mb-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $errors->first() }}
        </div>
        @endif

        {{-- TOTP code form --}}
        <div id="totp-panel">
            <form method="POST" action="{{ route('tenant.admin.two-factor.challenge.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Authentication Code</label>
                    <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                           autofocus maxlength="8" placeholder="000 000"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-center text-2xl tracking-widest font-mono focus-ring transition">
                </div>

                <button type="submit" class="w-full btn-brand text-white font-bold py-3.5 rounded-2xl text-base shadow-md">
                    Verify &rarr;
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-500">
                Lost access to your app?
                <button onclick="toggleRecovery()" class="text-brand font-semibold hover:underline">Use a recovery code</button>
            </p>
        </div>

        {{-- Recovery code form (hidden by default) --}}
        <div id="recovery-panel" class="hidden">
            <form method="POST" action="{{ route('tenant.admin.two-factor.challenge.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Recovery Code</label>
                    <input type="text" name="recovery_code" autocomplete="off"
                           autofocus placeholder="xxxxxxxx-xxxxxxxx"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 font-mono focus-ring transition">
                    <p class="mt-1.5 text-xs text-slate-400">Each recovery code can only be used once.</p>
                </div>

                <button type="submit" class="w-full btn-brand text-white font-bold py-3.5 rounded-2xl text-base shadow-md">
                    Verify with Recovery Code &rarr;
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-500">
                Have your app?
                <button onclick="toggleRecovery()" class="text-brand font-semibold hover:underline">Use authenticator code</button>
            </p>
        </div>

        <p class="mt-8 text-center text-xs text-slate-400">
            <a href="{{ route('tenant.login') }}" class="hover:text-slate-600 transition-colors">← Back to login</a>
        </p>
    </div>
</div>

<script>
function toggleRecovery() {
    var totp     = document.getElementById('totp-panel');
    var recovery = document.getElementById('recovery-panel');
    var isTotp   = !totp.classList.contains('hidden');
    totp.classList.toggle('hidden', isTotp);
    recovery.classList.toggle('hidden', !isTotp);
    // Focus the relevant input
    var el = (isTotp ? recovery : totp).querySelector('input');
    if (el) el.focus();
}
</script>
</body>
</html>
