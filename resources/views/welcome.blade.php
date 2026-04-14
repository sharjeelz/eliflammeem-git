<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>ElifLammeem — AI-Powered School Complaint Management System</title>
    <meta name="description" content="ElifLammeem helps schools track, manage, and resolve parent and teacher complaints faster with AI analysis, smart routing, and a parent-friendly portal. Trusted by schools in Malaysia." />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="{{ url('/') }}" />

    {{-- Open Graph --}}
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url('/') }}" />
    <meta property="og:title" content="ElifLammeem — AI-Powered School Complaint Management" />
    <meta property="og:description" content="Track, manage, and resolve school complaints faster with AI analysis, smart routing, and a parent-friendly portal." />
    <meta property="og:site_name" content="ElifLammeem" />

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="ElifLammeem — AI-Powered School Complaint Management" />
    <meta name="twitter:description" content="Track, manage, and resolve school complaints faster with AI analysis, smart routing, and a parent-friendly portal." />

    {{-- JSON-LD Structured Data --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "ElifLammeem",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "AI-powered school complaint tracking and parent communication platform. Helps schools resolve issues faster with intelligent sentiment analysis, smart routing, and a parent-facing portal.",
        "url": "{{ url('/') }}",
        "offers": {
            "@@type": "AggregateOffer",
            "priceCurrency": "MYR",
            "offerCount": "4",
            "lowPrice": "0"
        },
        "provider": {
            "@@type": "Organization",
            "name": "ElifLammeem",
            "url": "{{ url('/') }}"
        }
    }
    </script>

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
                    animation: {
                        'pulse-slow': 'pulse 6s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .gradient-text { background: linear-gradient(135deg, #7c3aed 0%, #3b82f6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero-glow { position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 1200px; height: 600px; background: radial-gradient(circle at 50% 0%, rgba(124, 58, 237, 0.15) 0%, transparent 70%); pointer-events: none; z-index: -1; }
        .bento-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 1.5rem; }
    </style>
</head>
<body class="bg-[#fafafa] text-slate-900 antialiased font-sans selection:bg-primary-100 selection:text-primary-700">

    <div class="hero-glow"></div>

    {{-- ─── NAVIGATION ────────────────────────────────────────────────────────── --}}
    <header class="fixed top-4 inset-x-0 z-50 px-4 sm:px-6 lg:px-8">
        <nav class="max-w-5xl mx-auto glass rounded-2xl px-4 py-3 flex items-center justify-between shadow-lg shadow-black/[0.03]">
            <a href="/" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-primary-600 to-blue-500 flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <span class="font-bold text-slate-900 text-xl tracking-tight">ElifLammeem</span>
            </a>

            <div class="hidden md:flex items-center gap-8">
                <a href="#features" class="text-sm font-semibold text-slate-600 hover:text-primary-600 transition-colors">Features</a>
                <a href="#pricing" class="text-sm font-semibold text-slate-600 hover:text-primary-600 transition-colors">Pricing</a>
            </div>

            <div class="flex items-center gap-3">
                <a href="#pricing" class="bg-slate-900 text-white text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-slate-800 transition-all shadow-md active:scale-95">
                    Get Started
                </a>
            </div>
        </nav>
    </header>

    {{-- ─── HERO SECTION ──────────────────────────────────────────────────────── --}}
    <main>
        <section class="relative pt-32 pb-20 sm:pt-48 sm:pb-32 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                <div class="inline-flex items-center gap-2 bg-primary-50 border border-primary-100 text-primary-700 text-[13px] font-bold px-4 py-1.5 rounded-full mb-8 animate-float">
                    Built for Schools · Secure · Multi-Tenant
                </div>

                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold text-slate-900 leading-[1.1] tracking-tight mb-8">
                    School Issue Reporting,<br class="hidden lg:block" />
                    <span class="gradient-text">Done Right.</span>
                </h1>

                <p class="text-slate-500 text-lg sm:text-xl max-w-2xl mx-auto leading-relaxed mb-12 font-medium">
                    A secure platform that lets parents and teachers raise concerns — 
                    and gives school staff the tools to track, assign, and resolve them fast.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="#pricing" class="w-full sm:w-auto bg-primary-600 text-white font-bold text-lg px-8 py-4 rounded-2xl hover:bg-primary-700 hover:shadow-xl hover:shadow-primary-200 transition-all active:scale-[0.98]">
                        See Pricing &darr;
                    </a>
                    <a href="#features" class="w-full sm:w-auto bg-white border border-slate-200 text-slate-600 font-bold text-lg px-8 py-4 rounded-2xl hover:bg-slate-50 transition-all">
                        Explore Features
                    </a>
                </div>
            </div>
        </section>

        {{-- ─── FEATURES (BENTO GRID) ────────────────────────────────────────────── --}}
        <section id="features" class="py-24 sm:py-32 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-primary-600 font-bold text-sm uppercase tracking-[0.2em] mb-4">What we offer</h2>
                    <h3 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 leading-tight">
                        Everything a school needs to manage parent and teacher complaints — all in one beautifully designed platform.
                    </h3>
                </div>

                <div class="bento-grid">
                    {{-- Big Card: AI Analysis --}}
                    <div class="col-span-12 lg:col-span-8 group relative bg-slate-50 rounded-[2.5rem] p-10 overflow-hidden border border-slate-100 hover:border-primary-200 transition-all">
                        <div class="relative z-10 flex flex-col h-full">
                            <div class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <h4 class="text-2xl font-bold text-slate-900 mb-4">AI Sentiment Analysis</h4>
                            <p class="text-slate-500 font-medium max-w-md leading-relaxed">
                                Every submitted issue is automatically analysed for urgency and sentiment, helping staff prioritise faster and respond more effectively.
                            </p>
                        </div>
                        <div class="absolute bottom-[-10%] right-[-5%] w-1/2 aspect-square bg-primary-200/20 blur-[100px] rounded-full"></div>
                    </div>

                    {{-- Card: Access Code --}}
                    <div class="col-span-12 md:col-span-6 lg:col-span-4 group bg-slate-900 rounded-[2.5rem] p-10 border border-slate-800 hover:border-slate-700 transition-all">
                        <div class="w-14 h-14 rounded-2xl bg-slate-800 flex items-center justify-center mb-8 group-hover:rotate-6 transition-transform">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-4">Access Code Portal</h4>
                        <p class="text-slate-400 font-medium leading-relaxed">
                            Parents and teachers submit issues using a personal access code — no accounts or passwords needed for quick reporting.
                        </p>
                    </div>

                    {{-- Card: Multi-Branch --}}
                    <div class="col-span-12 md:col-span-6 lg:col-span-4 group bg-white rounded-[2.5rem] p-10 border border-slate-200 hover:border-primary-200 transition-all shadow-sm">
                        <div class="w-14 h-14 rounded-2xl bg-primary-50 flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h4 class="text-2xl font-bold text-slate-900 mb-4">Multi-Branch Support</h4>
                        <p class="text-slate-500 font-medium leading-relaxed">
                            Manage multiple branches with ease. Branch managers see only their branch's issues and staff, ensuring clean data separation.
                        </p>
                    </div>

                    {{-- Card: Two-Way Messaging --}}
                    <div class="col-span-12 lg:col-span-8 group relative bg-primary-50 rounded-[2.5rem] p-10 overflow-hidden border border-primary-100 hover:border-primary-200 transition-all">
                        <div class="relative z-10 flex flex-col md:flex-row gap-10 items-center">
                            <div class="flex-1">
                                <div class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                                    <svg class="w-7 h-7 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                </div>
                                <h4 class="text-2xl font-bold text-slate-900 mb-4">Two-Way Messaging</h4>
                                <p class="text-slate-500 font-medium leading-relaxed">
                                    Staff reply directly inside the issue thread. Contacts can respond back — keeping all communication history in one secure, tracked location.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Public AI Chatbot --}}
                    <div class="col-span-12 md:col-span-6 lg:col-span-4 group relative bg-slate-900 rounded-[2.5rem] p-10 border border-slate-800 hover:border-primary-600 transition-all overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary-900/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative z-10">
                            <div class="w-14 h-14 rounded-2xl bg-primary-600 flex items-center justify-center mb-8 group-hover:scale-110 transition-transform shadow-lg shadow-primary-500/30">
                                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/>
                                </svg>
                            </div>
                            <div class="inline-flex items-center gap-1.5 bg-primary-600/20 text-primary-400 text-xs font-semibold px-3 py-1 rounded-full mb-4 border border-primary-600/30">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                AI-Powered
                            </div>
                            <h4 class="text-2xl font-bold text-white mb-4">Public AI Chatbot</h4>
                            <p class="text-slate-400 font-medium leading-relaxed">
                                Parents get instant answers 24/7 from an AI trained on your school's own documents, FAQs, and policies — without ever contacting staff.
                            </p>
                        </div>
                    </div>

                    {{-- Card: REST API & Data Import --}}
                    <div class="col-span-12 md:col-span-6 group relative bg-white rounded-[2.5rem] p-10 border border-slate-200 hover:border-primary-200 transition-all shadow-sm overflow-hidden">
                        <div class="w-14 h-14 rounded-2xl bg-slate-900 flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                        </div>
                        <h4 class="text-2xl font-bold text-slate-900 mb-4">REST API & Bulk Import</h4>
                        <p class="text-slate-500 font-medium leading-relaxed mb-6">
                            Import parents, teachers, and staff in bulk via CSV or our REST API. Keep your roster in sync automatically — no manual data entry needed.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-600 text-xs font-semibold px-3 py-1.5 rounded-full">CSV / Excel upload</span>
                            <span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-600 text-xs font-semibold px-3 py-1.5 rounded-full">REST API</span>
                            <span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-600 text-xs font-semibold px-3 py-1.5 rounded-full">Auto deactivation</span>
                        </div>
                    </div>

                    {{-- Card: White-Label Customisation --}}
                    <div class="col-span-12 md:col-span-6 group relative bg-gradient-to-br from-primary-600 to-primary-700 rounded-[2.5rem] p-10 border border-primary-500 hover:border-primary-400 transition-all overflow-hidden">
                        <div class="absolute bottom-[-20%] right-[-10%] w-2/3 aspect-square bg-white/5 rounded-full blur-3xl"></div>
                        <div class="relative z-10">
                            <div class="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                </svg>
                            </div>
                            <h4 class="text-2xl font-bold text-white mb-4">White-Label Customisation</h4>
                            <p class="text-white/75 font-medium leading-relaxed mb-6">
                                Your school's logo, brand colours, and name — everywhere. The parent portal and chatbot look like they were built just for you, not off-the-shelf software.
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1.5 bg-white/15 text-white text-xs font-semibold px-3 py-1.5 rounded-full">Custom logo</span>
                                <span class="inline-flex items-center gap-1.5 bg-white/15 text-white text-xs font-semibold px-3 py-1.5 rounded-full">Brand colours</span>
                                <span class="inline-flex items-center gap-1.5 bg-white/15 text-white text-xs font-semibold px-3 py-1.5 rounded-full">Custom domain</span>
                            </div>
                        </div>
                    </div>

                    {{-- Full-width: Bring Your Own Integrations --}}
                    <div class="col-span-12 group relative bg-slate-50 rounded-[2.5rem] p-10 md:p-14 border border-slate-200 hover:border-primary-200 transition-all overflow-hidden">
                        <div class="relative z-10 flex flex-col md:flex-row items-center gap-10 md:gap-16">
                            <div class="flex-1">
                                <div class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                                    <svg class="w-7 h-7 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                </div>
                                <h4 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-4">Bring Your Own Integrations</h4>
                                <p class="text-slate-500 font-medium leading-relaxed max-w-xl">
                                    Already have an email provider, WhatsApp Business account, or SMS gateway? Connect your own credentials and keep full control — your messages, your deliverability, your costs.
                                </p>
                            </div>
                            <div class="flex-shrink-0 flex flex-col gap-4 w-full md:w-auto">
                                <div class="flex items-center gap-4 bg-white rounded-2xl px-6 py-4 border border-slate-200 shadow-sm">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-800">Email</div>
                                        <div class="text-xs text-slate-400">SMTP · SendGrid · Mailgun</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 bg-white rounded-2xl px-6 py-4 border border-slate-200 shadow-sm">
                                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-800">WhatsApp</div>
                                        <div class="text-xs text-slate-400">WhatsApp Business API</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 bg-white rounded-2xl px-6 py-4 border border-slate-200 shadow-sm">
                                    <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-800">SMS</div>
                                        <div class="text-xs text-slate-400">Any SMS gateway(Twillio)/ Mesegat(KSA)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Full-width: Custom Development CTA --}}
                    <div class="col-span-12 group relative bg-slate-950 rounded-[2.5rem] p-10 md:p-14 border border-slate-800 hover:border-slate-700 transition-all overflow-hidden">
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-1/2 h-px bg-gradient-to-r from-transparent via-primary-500 to-transparent"></div>
                        <div class="absolute bottom-[-30%] left-1/2 -translate-x-1/2 w-1/2 aspect-square bg-primary-600/10 blur-[120px] rounded-full"></div>
                        <div class="relative z-10 flex flex-col md:flex-row items-center gap-10">
                            <div class="flex-shrink-0">
                                <div class="w-16 h-16 rounded-2xl bg-primary-600/20 border border-primary-600/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 text-center md:text-left">
                                <div class="inline-flex items-center gap-1.5 bg-primary-600/15 text-primary-400 text-xs font-semibold px-3 py-1 rounded-full mb-4 border border-primary-600/20">
                                    Built for you
                                </div>
                                <h4 class="text-2xl md:text-3xl font-extrabold text-white mb-3">Need something we don't have yet?</h4>
                                <p class="text-slate-400 font-medium leading-relaxed max-w-2xl">
                                    Every school is different. If you need a custom feature, integration, or workflow that isn't on this list — we're open to building it together. Tell us what you need and we'll make it happen.
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="{{ url('/contact') }}"
                                   class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-500 text-white font-semibold px-8 py-4 rounded-2xl transition-colors shadow-lg shadow-primary-600/20 whitespace-nowrap">
                                    Let's talk
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ─── PRICING ──────────────────────────────────────────────────────────── --}}
        @php
        $supportEmail = config('mail.support_address', config('mail.from.address'));
        $pricingMeta = [
            'starter'    => ['popular' => false, 'dark' => false, 'cta' => 'Get Started',   'cta_href' => url('/contact?package=starter')],
            'growth'     => ['popular' => true,  'dark' => false, 'cta' => 'Get Growth',    'cta_href' => url('/contact?package=growth')],
            'pro'        => ['popular' => false, 'dark' => false, 'cta' => 'Get Pro',       'cta_href' => url('/contact?package=pro')],
            'enterprise' => ['popular' => false, 'dark' => true,  'cta' => 'Contact Us',   'cta_href' => url('/contact?package=enterprise')],
        ];

        $featureLines = [
            ['type' => 'always', 'label' => 'Issue tracking & messaging'],
            ['type' => 'always', 'label' => 'Access code parent portal'],
            ['type' => 'always', 'label' => 'Anonymous submissions'],
            ['type' => 'always', 'label' => 'File attachments'],
            ['type' => 'always', 'label' => 'Email notifications'],
            ['type' => 'feat',   'col' => 'feat_ai_analysis',      'label' => 'AI issue analysis'],
            ['type' => 'feat',   'col' => 'feat_ai_trends',        'label' => 'AI trend detection'],
            ['type' => 'feat',   'col' => 'feat_chatbot',          'label' => 'Public AI chatbot'],
            ['type' => 'feat',   'col' => 'feat_broadcasting',     'label' => 'SMS & email broadcasting'],
            ['type' => 'feat',   'col' => 'feat_whatsapp',         'label' => 'WhatsApp integration'],
            ['type' => 'feat',   'col' => 'feat_document_library', 'label' => 'Document library & FAQs'],
            ['type' => 'feat',   'col' => 'feat_reports_full',     'label' => 'Full reports & analytics'],
            ['type' => 'feat',   'col' => 'feat_csat',             'label' => 'CSAT surveys'],
            ['type' => 'api',    'col' => 'feat_api_access',        'label' => 'REST API access'],
        ];
        @endphp

        <section id="pricing" class="py-24 sm:py-32 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20">
                    <h2 class="text-primary-600 font-bold text-sm uppercase tracking-[0.2em] mb-4">Pricing</h2>
                    <h3 class="text-4xl sm:text-5xl font-extrabold text-slate-900 mb-6">Choose the right plan</h3>
                    <p class="text-slate-500 font-medium max-w-xl mx-auto text-lg">
                        Start for free, upgrade when you need more. No hidden fees.
                    </p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($plans as $plan)
                        @php $m = $pricingMeta[$plan->key] ?? $pricingMeta['enterprise']; @endphp

                        <div class="relative group rounded-[2rem] p-8 flex flex-col border transition-all duration-300 {{ $m['dark'] ? 'bg-slate-900 border-slate-800' : ($m['popular'] ? 'bg-white border-primary-500 shadow-xl shadow-primary-500/5 -translate-y-2' : 'bg-white border-slate-200 hover:border-primary-300 shadow-sm hover:shadow-lg') }}">
                            
                            @if($m['popular'])
                                <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-primary-600 text-white text-[11px] font-bold px-4 py-1.5 rounded-full uppercase tracking-widest shadow-lg shadow-primary-500/30">
                                    Most Popular
                                </div>
                            @endif

                            <div class="mb-8">
                                <h4 class="font-bold text-xl mb-1 {{ $m['dark'] ? 'text-white' : 'text-slate-900' }}">{{ $plan->label }}</h4>
                                <p class="text-sm font-medium {{ $m['dark'] ? 'text-slate-400' : 'text-slate-500' }}">{{ $plan->tagline ?? '' }}</p>
                            </div>

                            <div class="mb-8">
                                @php $formatted = $plan->formattedPrice(); @endphp
                                @if($formatted !== null)
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-4xl font-extrabold tracking-tight {{ $m['dark'] ? 'text-white' : 'text-slate-900' }}">{{ $formatted }}</span>
                                        <span class="text-sm font-semibold {{ $m['dark'] ? 'text-slate-400' : 'text-slate-500' }}">/mo</span>
                                    </div>
                                @else
                                    <span class="text-3xl font-extrabold {{ $m['dark'] ? 'text-white' : 'text-slate-900' }}">Contact us</span>
                                @endif
                            </div>

                            <ul class="space-y-4 mb-10 flex-1">
                                <li class="flex items-center gap-3 text-sm font-medium {{ $m['dark'] ? 'text-slate-300' : 'text-slate-600' }}">
                                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    {{ $plan->max_branches === null ? 'Unlimited branches' : $plan->max_branches . ' branch' . ($plan->max_branches > 1 ? 'es' : '') }}
                                </li>
                                <li class="flex items-center gap-3 text-sm font-medium {{ $m['dark'] ? 'text-slate-300' : 'text-slate-600' }}">
                                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    {{ $plan->max_users === null ? 'Unlimited staff' : 'Up to ' . $plan->max_users . ' staff' }}
                                </li>
                                @foreach($featureLines as $f)
                                    @php
                                        $on = $f['type'] === 'always' || (bool)($plan->{$f['col']} ?? false);
                                        $lineLabel = $f['label'];
                                        if ($f['type'] === 'api' && $on) {
                                            $daily = $plan->feat_api_daily_limit ?? null;
                                            $lineLabel .= $daily === null ? ' (unlimited)' : ' (' . number_format($daily) . '/day)';
                                        }
                                    @endphp
                                    <li class="flex items-center gap-3 text-sm font-medium {{ $on ? ($m['dark'] ? 'text-slate-300' : 'text-slate-600') : 'text-slate-400 opacity-60' }}">
                                        @if($on)
                                            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <svg class="w-5 h-5 text-slate-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @endif
                                        {{ $lineLabel }}
                                    </li>
                                @endforeach
                            </ul>

                            <a href="{{ $m['cta_href'] }}" class="w-full text-center py-4 rounded-2xl font-bold text-sm transition-all duration-300 {{ $m['dark'] ? 'bg-white text-slate-900 hover:bg-slate-100' : ($m['popular'] ? 'bg-primary-600 text-white hover:bg-primary-700 shadow-lg shadow-primary-200' : 'bg-slate-100 text-slate-900 hover:bg-slate-200') }}">
                                {{ $m['cta'] }}
                            </a>
                        </div>
                    @endforeach
                </div>
                
                <p class="text-center mt-12 text-slate-400 text-sm font-medium">
                    All paid plans are provisioned manually — contact us to get set up.
                </p>
            </div>
        </section>
    </main>

    {{-- ─── FOOTER ───────────────────────────────────────────────────────────── --}}
    <footer class="bg-[#fafafa] border-t border-slate-200 pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 pt-10 border-t border-slate-200">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center shadow-lg shadow-primary-200">
                         <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-slate-900 text-lg tracking-tight">ElifLammeem</span>
                </div>
                <div class="flex items-center gap-6 text-sm font-medium text-slate-500">
                    <a href="{{ url('/terms') }}" class="hover:text-primary-600 transition-colors">Terms &amp; Conditions</a>
                    <a href="{{ url('/privacy') }}" class="hover:text-primary-600 transition-colors">Privacy Policy</a>
                    <a href="{{ url('/contact') }}" class="hover:text-primary-600 transition-colors">Contact Us</a>
                </div>
                <p class="text-slate-400 text-sm font-medium">&copy; {{ date('Y') }} ElifLammeem. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>
