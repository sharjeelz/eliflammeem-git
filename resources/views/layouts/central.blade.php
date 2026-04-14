<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ElifLammeem') — AI-Powered School Complaint Management</title>
    <meta name="description" content="@yield('description', 'ElifLammeem — AI-powered school complaint tracking and parent communication platform.')" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="{{ url()->current() }}" />

    {{-- Open Graph --}}
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="@yield('title', 'ElifLammeem') — AI-Powered School Complaint Management" />
    <meta property="og:description" content="@yield('description', 'AI-powered school complaint tracking and parent communication platform.')" />
    <meta property="og:site_name" content="ElifLammeem" />

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" content="@yield('title', 'ElifLammeem') — ElifLammeem" />
    <meta name="twitter:description" content="@yield('description', 'AI-powered school complaint tracking and parent communication platform.')" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Instrument Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f5f3ff', 100: '#ede9fe', 200: '#ddd6fe', 300: '#c4b5fd',
                            400: '#a78bfa', 500: '#8b5cf6', 600: '#7c3aed', 700: '#6d28d9',
                            800: '#5b21b6', 900: '#4c1d95', 950: '#2e1065',
                        },
                    },
                }
            }
        }
    </script>
    <style>
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .gradient-text { background: linear-gradient(135deg, #7c3aed 0%, #3b82f6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .prose h2 { font-size: 1.25rem; font-weight: 700; color: #111827; margin-top: 2rem; margin-bottom: 0.75rem; }
        .prose p { color: #4b5563; line-height: 1.75; margin-bottom: 1rem; }
        .prose ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
        .prose li { color: #4b5563; line-height: 1.75; margin-bottom: 0.25rem; }
        .prose strong { color: #374151; font-weight: 600; }
        .prose a { color: #7c3aed; text-decoration: underline; }
    </style>

    {{-- Cloudflare Turnstile --}}
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    @stack('head')
</head>
<body class="bg-[#fafafa] text-slate-900 antialiased font-sans selection:bg-primary-100 selection:text-primary-700 flex flex-col min-h-screen">

    {{-- ─── NAVIGATION ──────────────────────────────────────────────────────────── --}}
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur border-b border-slate-200/80 shadow-sm">
        <nav class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-primary-600 to-blue-500 flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <span class="font-bold text-slate-900 text-xl tracking-tight">ElifLammeem</span>
            </a>

            <div class="flex items-center gap-6 text-sm font-semibold">
                <a href="/" class="text-slate-500 hover:text-primary-600 transition-colors">Home</a>
                <a href="{{ url('/contact') }}" class="text-slate-500 hover:text-primary-600 transition-colors">Contact</a>
            </div>
        </nav>
    </header>

    {{-- ─── MAIN CONTENT ─────────────────────────────────────────────────────────── --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- ─── FOOTER ───────────────────────────────────────────────────────────────── --}}
    <footer class="bg-white border-t border-slate-200 py-8 mt-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-5 text-sm font-medium text-slate-500">
                <a href="{{ url('/terms') }}" class="hover:text-primary-600 transition-colors">Terms &amp; Conditions</a>
                <a href="{{ url('/privacy') }}" class="hover:text-primary-600 transition-colors">Privacy Policy</a>
                <a href="{{ url('/contact') }}" class="hover:text-primary-600 transition-colors">Contact Us</a>
            </div>
            <p class="text-slate-400 text-sm">&copy; {{ date('Y') }} ElifLammeem. All rights reserved.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
