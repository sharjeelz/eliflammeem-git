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
    <title>Staff Login — {{ $name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --p: {{ $hex }}; --p-dark: {{ $dark }}; --p-light: {{ $light }}; }
        .bg-primary    { background-color: var(--p); }
        .bg-primary-d  { background-color: var(--p-dark); }
        .text-primary  { color: var(--p) !important; }
        .border-primary { border-color: var(--p) !important; }
        .gradient-brand {
            background: linear-gradient(145deg, var(--p-dark) 0%, var(--p) 100%);
        }
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

{{-- ─── LEFT PANEL — school branding ──────────────────────────────────────── --}}
<div class="hidden lg:flex lg:w-5/12 xl:w-1/2 gradient-brand flex-col justify-between p-12 relative overflow-hidden">

    {{-- decorative circles --}}
    <div class="absolute -top-24 -left-24 w-80 h-80 rounded-full bg-white/5"></div>
    <div class="absolute -bottom-32 -right-20 w-96 h-96 rounded-full bg-black/10"></div>

    {{-- top: school identity --}}
    <div class="relative">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
            @if($logo)
                <img src="{{ $logo }}" alt="{{ $name }}"
                     class="h-12 w-auto object-contain bg-white/20 rounded-xl px-2 py-1">
            @else
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center
                            text-white font-extrabold text-xl select-none">
                    {{ strtoupper(substr($name, 0, 1)) }}
                </div>
            @endif
            <span class="text-white font-bold text-lg tracking-tight">{{ $name }}</span>
        </a>
    </div>

    {{-- middle: headline --}}
    <div class="relative">
        <div class="inline-flex items-center gap-2 bg-white/15 border border-white/20
                    text-white/90 text-xs font-semibold px-3 py-1.5 rounded-full mb-6">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Staff Portal
        </div>

        <h1 class="text-3xl xl:text-4xl font-extrabold text-white leading-snug mb-4">
            Manage issues.<br>
            Support your school.
        </h1>
        <p class="text-white/70 text-base leading-relaxed max-w-sm">
            Track, assign, and resolve concerns raised by parents and teachers — all in one place.
        </p>
    </div>

    {{-- bottom: feature pills --}}
    <div class="relative flex flex-wrap gap-2">
        @foreach(['Role-based access', 'Real-time notifications', 'AI prioritisation', 'Audit trail'] as $feat)
        <span class="bg-white/10 border border-white/20 text-white/80 text-xs font-medium px-3 py-1.5 rounded-full">
            {{ $feat }}
        </span>
        @endforeach
    </div>
</div>

{{-- ─── RIGHT PANEL — login form ───────────────────────────────────────────── --}}
<div class="flex-1 flex flex-col justify-center items-center px-6 py-16">

    {{-- Mobile logo (shown only on small screens) --}}
    <div class="lg:hidden flex items-center gap-3 mb-10">
        @if($logo)
            <img src="{{ $logo }}" alt="{{ $name }}" class="h-10 w-auto object-contain">
        @else
            <div class="w-10 h-10 rounded-xl gradient-brand flex items-center justify-center
                        text-white font-extrabold text-base">
                {{ strtoupper(substr($name, 0, 1)) }}
            </div>
        @endif
        <span class="font-bold text-slate-900 text-base">{{ $name }}</span>
    </div>

    <div class="w-full max-w-md">

        {{-- Card --}}
        <div class="bg-white rounded-3xl shadow-2xl shadow-slate-200 border border-slate-100 p-8 sm:p-10">

            {{-- Header --}}
            <div class="mb-8">
                <h2 class="text-2xl font-extrabold text-slate-900 mb-1">Staff Sign In</h2>
                <p class="text-slate-500 text-sm">Enter your credentials to access the staff panel.</p>
            </div>

            {{-- Success (e.g. password reset) --}}
            @if(session('status'))
            <div class="mb-6 flex items-start gap-3 rounded-xl bg-green-50 border border-green-200 px-4 py-3.5 text-sm text-green-700">
                <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
            @endif

            {{-- Error alert --}}
            @if ($errors->any())
            <div class="mb-6 flex items-start gap-3 rounded-xl bg-red-50 border border-red-200 px-4 py-3.5 text-sm text-red-700">
                <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $errors->first() }}
            </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ url('admin/login') }}" class="space-y-5">
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

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-semibold text-slate-700">
                            Password
                        </label>
                        <a href="{{ route('tenant.admin.password.request') }}"
                           class="text-xs font-semibold transition-colors hover:opacity-70"
                           style="color: var(--p)">
                            Forgot password?
                        </a>
                    </div>
                    <input id="password" type="password" name="password"
                           autocomplete="current-password" required
                           placeholder="••••••••"
                           class="focus-ring w-full rounded-xl border border-slate-300 bg-slate-50
                                  px-4 py-3 text-sm text-slate-900 placeholder-slate-400 transition-colors">
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="remember" id="remember"
                           class="w-4 h-4 rounded border-slate-300 cursor-pointer"
                           style="accent-color: var(--p)">
                    <label for="remember" class="text-sm text-slate-600 cursor-pointer select-none">
                        Keep me signed in
                    </label>
                </div>

                <button type="submit"
                        class="btn-primary-custom w-full text-white font-bold text-sm
                               py-3.5 rounded-xl shadow-lg mt-2 cursor-pointer">
                    Sign In to Staff Panel
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <div class="mt-6 text-center">
            <a href="{{ url('/') }}"
               class="text-sm text-slate-500 hover:text-slate-800 transition-colors">
                ← Back to {{ $name }} portal
            </a>
        </div>
    </div>
</div>

</body>
</html>
