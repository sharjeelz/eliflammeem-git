<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ElifLammeem — School Leadership Briefing</title>
    <meta name="robots" content="noindex, nofollow" />

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
        .print-only { display: none; }

        @media print {
            .no-print   { display: none !important; }
            .print-only { display: block !important; }
            .avoid-break { page-break-inside: avoid; break-inside: avoid; }
            .page-break-after { page-break-after: always; break-after: page; }
            @page { margin: 1.5cm 1.8cm; size: A4 portrait; }
            body { font-size: 11pt; color: #000; background: #fff; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased font-sans">

    {{-- PRINT HEADER --}}
    <div class="print-only mb-6 pb-4 border-b border-slate-300">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <span class="font-bold text-lg text-primary-700">ElifLammeem</span>
            </div>
            <span class="text-sm text-slate-500 font-medium">Confidential Leadership Briefing</span>
        </div>
    </div>

    {{-- SCREEN NAV --}}
    <header class="no-print sticky top-0 z-50 bg-white/80 backdrop-blur border-b border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 group">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-primary-600 to-blue-500 flex items-center justify-center shadow group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <span class="font-bold text-slate-800">ElifLammeem</span>
            </a>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="inline-flex items-center gap-1.5 text-sm text-slate-600 hover:text-slate-900 border border-slate-300 rounded-lg px-3 py-1.5 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Download PDF
                </button>
                <a href="{{ route('central.contact') }}" class="inline-flex items-center gap-1.5 text-sm bg-primary-600 text-white rounded-lg px-4 py-1.5 hover:bg-primary-700 transition-colors font-medium">
                    Schedule a Demo
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-10 sm:py-16">

        {{-- ─── HERO ─────────────────────────────────────────────────────────────── --}}
        <section class="avoid-break mb-14 text-center">
            <p class="inline-block text-xs font-semibold tracking-widest uppercase text-primary-600 bg-primary-50 rounded-full px-4 py-1.5 mb-5">
                Confidential Briefing · Prepared for School Leadership
            </p>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-900 leading-tight mb-5">
                What happens to every parent concern<br class="hidden sm:block"> at your school?
            </h1>
            <p class="text-lg text-slate-600 max-w-3xl mx-auto mb-10 leading-relaxed">
                ElifLammeem gives principals, vice principals, and board members complete visibility into how issues are raised, tracked, resolved — and whether parents are satisfied.
            </p>

            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-10 max-w-3xl mx-auto">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <div class="text-2xl font-bold text-primary-600 mb-1">100%</div>
                    <div class="text-xs text-slate-500 leading-snug">of issues tracked with timestamps</div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <div class="text-2xl font-bold text-primary-600 mb-1">Live</div>
                    <div class="text-xs text-slate-500 leading-snug">real-time dashboard — no manual reports</div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <div class="text-2xl font-bold text-primary-600 mb-1">Auto</div>
                    <div class="text-xs text-slate-500 leading-snug">CSAT surveys sent on every closure</div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <div class="text-2xl font-bold text-primary-600 mb-1">AI</div>
                    <div class="text-xs text-slate-500 leading-snug">flags urgent issues before staff open them</div>
                </div>
            </div>

            <a href="{{ route('central.contact') }}" class="no-print inline-flex items-center gap-2 bg-primary-600 text-white font-semibold rounded-xl px-7 py-3.5 hover:bg-primary-700 transition-colors shadow-lg shadow-primary-200 text-base">
                Schedule a Demo
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </section>

        <hr class="border-slate-200 mb-14">

        {{-- ─── PROBLEM SECTIONS ─────────────────────────────────────────────────── --}}

        {{-- 1. Complaints get lost --}}
        <section class="avoid-break mb-12">
            <div class="flex items-start gap-5">
                <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">1. Complaints get lost</h2>
                    <div class="space-y-3">
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-red-600 mb-2">The Problem</span>
                            <p class="text-slate-700 text-sm leading-relaxed">A parent reports a bus safety issue at the front desk. A sticky note is written. Three weeks later — no record, no assignee, no resolution. The parent calls again. The school has no answer.</p>
                        </div>
                        <div class="rounded-xl border border-primary-200 bg-primary-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-primary-600 mb-2">How ElifLammeem Solves This</span>
                            <p class="text-slate-700 text-sm leading-relaxed">Every issue gets a unique reference number, a status that moves from New → In Progress → Resolved → Closed, and a named assignee. Every action is logged. Nothing falls through the cracks.</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-amber-600 mb-2">Real School Example</span>
                            <p class="text-slate-700 text-sm leading-relaxed">Al-Noor Academy handled 47 complaints in their first month. Every one was documented with timestamps. When the board requested a compliance report, it was generated in one click — no manual compilation.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. No visibility --}}
        <section class="avoid-break mb-12">
            <div class="flex items-start gap-5">
                <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">2. Principals have no visibility</h2>
                    <div class="space-y-3">
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-red-600 mb-2">The Problem</span>
                            <p class="text-slate-700 text-sm leading-relaxed">The principal has no idea how many open issues exist, which branch is struggling, or whether staff are actually resolving things. Every status update requires chasing someone by phone.</p>
                        </div>
                        <div class="rounded-xl border border-primary-200 bg-primary-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-primary-600 mb-2">How ElifLammeem Solves This</span>
                            <p class="text-slate-700 text-sm leading-relaxed">A real-time dashboard shows open counts by branch, average resolution time, urgency flags, and staff workload. The Reports page provides date-filtered trend charts, SLA cards, and per-branch breakdowns — always up to date.</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-amber-600 mb-2">Real School Example</span>
                            <p class="text-slate-700 text-sm leading-relaxed">The vice principal at Bright Futures spotted 12 aging Facilities issues on the dashboard and intervened before parents escalated to social media. That intervention would have been impossible without the system.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. Parents don't feel heard --}}
        <section class="avoid-break mb-12">
            <div class="flex items-start gap-5">
                <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-pink-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">3. Parents don't feel heard</h2>
                    <div class="space-y-3">
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-red-600 mb-2">The Problem</span>
                            <p class="text-slate-700 text-sm leading-relaxed">No update for days. Parents call repeatedly. Dissatisfied parents share negative experiences in WhatsApp groups, damaging the school's reputation before leadership even knows there's a problem.</p>
                        </div>
                        <div class="rounded-xl border border-primary-200 bg-primary-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-primary-600 mb-2">How ElifLammeem Solves This</span>
                            <p class="text-slate-700 text-sm leading-relaxed">Automatic email and WhatsApp updates fire on every status change. An AI-generated acknowledgment reflects the urgency of the specific complaint. A CSAT satisfaction survey (1–5 stars) is sent automatically on closure — giving the school a measurable quality score.</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-amber-600 mb-2">Real School Example</span>
                            <p class="text-slate-700 text-sm leading-relaxed">A parent at Bright Futures gave 5 stars and wrote: "First time a school told me what was happening with my child's complaint." The school now tracks a 4.3/5 CSAT average — shared with the board quarterly as a parent satisfaction metric.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 4. Staff overwhelmed --}}
        <section class="avoid-break mb-12">
            <div class="flex items-start gap-5">
                <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-orange-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">4. Staff are overwhelmed with no triage</h2>
                    <div class="space-y-3">
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-red-600 mb-2">The Problem</span>
                            <p class="text-slate-700 text-sm leading-relaxed">One staff member handles everything. Urgent health and safety issues sit unseen next to cafeteria complaints. There is no triage — critical issues are discovered by accident, if at all.</p>
                        </div>
                        <div class="rounded-xl border border-primary-200 bg-primary-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-primary-600 mb-2">How ElifLammeem Solves This</span>
                            <p class="text-slate-700 text-sm leading-relaxed">AI scores every incoming issue for urgency — Escalate, Monitor, or Normal — and routes it to the correct staff member based on their assigned category. Critical issues trigger immediate in-app and email notifications to the branch manager before anyone opens the queue.</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-amber-600 mb-2">Real School Example</span>
                            <p class="text-slate-700 text-sm leading-relaxed">A student injury was reported at Al-Fajr School. The AI flagged it "Critical — Health &amp; Safety" within seconds of submission. The branch manager received a notification before any staff member had even opened the issue — response time was under four minutes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. Hard to reach parents --}}
        <section class="avoid-break mb-12">
            <div class="flex items-start gap-5">
                <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-teal-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">5. Reaching parents is slow and unreliable</h2>
                    <div class="space-y-3">
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-red-600 mb-2">The Problem</span>
                            <p class="text-slate-700 text-sm leading-relaxed">Staff manually compile outdated phone numbers from spreadsheets. Hours of effort. Many parents never receive the message. A single announcement takes all day and still misses half the audience.</p>
                        </div>
                        <div class="rounded-xl border border-primary-200 bg-primary-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-primary-600 mb-2">How ElifLammeem Solves This</span>
                            <p class="text-slate-700 text-sm leading-relaxed">A live contact roster with bulk email, SMS, and WhatsApp lets you reach the whole school, a specific branch, or a filtered group in a few clicks. Access codes can be sent en masse — parents activate their own portal account without any manual setup.</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-amber-600 mb-2">Real School Example</span>
                            <p class="text-slate-700 text-sm leading-relaxed">Crestview School sent WhatsApp access codes to 380 parents in a single bulk action. Parents submitted holiday permission requests through the portal — eliminating over 200 front-desk calls and half a day of administrative work each term.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 6. No audit trail --}}
        <section class="avoid-break mb-12">
            <div class="flex items-start gap-5">
                <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">6. No audit trail when disputes arise</h2>
                    <div class="space-y-3">
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-red-600 mb-2">The Problem</span>
                            <p class="text-slate-700 text-sm leading-relaxed">A parent threatens legal action. The school has no record of who received the complaint, what was said, when it was escalated, or who resolved it. The school is exposed with nothing to defend itself.</p>
                        </div>
                        <div class="rounded-xl border border-primary-200 bg-primary-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-primary-600 mb-2">How ElifLammeem Solves This</span>
                            <p class="text-slate-700 text-sm leading-relaxed">Every action — assignment, status change, internal comment, public reply, closure — is logged with a precise timestamp and the name of the actor. Staff accounts are soft-deleted, never erased, so the record is permanent even after staff turnover.</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-amber-600 mb-2">Real School Example</span>
                            <p class="text-slate-700 text-sm leading-relaxed">The principal at Riverside Academy printed the complete activity log: six distinct actions over three days, each attributed to a named staff member. The dispute was resolved without any legal escalation — the paper trail was the defence.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 7. Same problems repeat --}}
        <section class="avoid-break mb-12">
            <div class="flex items-start gap-5">
                <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-yellow-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">7. The same problems repeat every term</h2>
                    <div class="space-y-3">
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-red-600 mb-2">The Problem</span>
                            <p class="text-slate-700 text-sm leading-relaxed">Bus delays. Cafeteria hygiene. Textbook shortages. The same complaints arrive every term. No one connects the dots. Root causes are never addressed because no one sees the pattern.</p>
                        </div>
                        <div class="rounded-xl border border-primary-200 bg-primary-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-primary-600 mb-2">How ElifLammeem Solves This</span>
                            <p class="text-slate-700 text-sm leading-relaxed">AI Trend Detection automatically surfaces recurring themes across all incoming issues. The "Hot Topics" widget on the dashboard shows spikes in real time. Related complaints are grouped so a branch manager can resolve them in a single coordinated action.</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-amber-600 mb-2">Real School Example</span>
                            <p class="text-slate-700 text-sm leading-relaxed">The system detected that 11 of 14 new complaints in one week mentioned "bus" and "late." The branch manager arranged an immediate meeting with the transport contractor. Bus-related complaints dropped 60% the following month — a root-cause fix, not just complaint management.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 8. Can't prove improvement --}}
        <section class="avoid-break mb-14">
            <div class="flex items-start gap-5">
                <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">8. You can't prove improvement to the board</h2>
                    <div class="space-y-3">
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-red-600 mb-2">The Problem</span>
                            <p class="text-slate-700 text-sm leading-relaxed">The board asks: "Are we getting better?" There is no answer. No historical data, no baseline, no metrics. Every board meeting is anecdotal — improvement is claimed but never demonstrated.</p>
                        </div>
                        <div class="rounded-xl border border-primary-200 bg-primary-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-primary-600 mb-2">How ElifLammeem Solves This</span>
                            <p class="text-slate-700 text-sm leading-relaxed">The Reports page shows: total issues resolved, average resolution time, CSAT scores over time, SLA compliance percentage, and performance by branch and by staff member — filterable by day, week, or month. Export to PDF in one click.</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <span class="inline-block text-xs font-bold uppercase tracking-wider text-amber-600 mb-2">Real School Example</span>
                            <p class="text-slate-700 text-sm leading-relaxed">Al-Noor Academy presented the board with hard numbers: average resolution time dropped from 8.4 days to 2.1 days, and CSAT improved from 3.1 to 4.4 out of 5 — in six months. The board approved full expansion to all three branches.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ─── CLOSING CTA (screen only) ───────────────────────────────────────── --}}
        <section class="no-print avoid-break rounded-3xl bg-gradient-to-br from-primary-600 to-primary-800 text-white text-center px-6 py-14">
            <h2 class="text-2xl sm:text-3xl font-bold mb-4">Ready to see it in action?</h2>
            <p class="text-primary-100 max-w-xl mx-auto mb-8 leading-relaxed">
                Schedule a 30-minute walkthrough with your team. We'll show exactly how ElifLammeem fits your school's structure — branches, roles, and all.
            </p>
            <a href="{{ route('central.contact') }}" class="inline-flex items-center gap-2 bg-white text-primary-700 font-bold rounded-xl px-8 py-4 hover:bg-primary-50 transition-colors shadow-xl text-base">
                Schedule a Demo
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </section>

        {{-- PRINT FOOTER --}}
        <div class="print-only mt-10 pt-6 border-t border-slate-300 text-center">
            <p class="text-sm text-slate-500">ElifLammeem · Confidential — Prepared for School Leadership · {{ url('/') }}</p>
        </div>

    </main>

    {{-- SCREEN FOOTER --}}
    <footer class="no-print mt-8 border-t border-slate-200 py-8 text-center text-sm text-slate-400">
        <a href="/" class="hover:text-primary-600 transition-colors">ElifLammeem</a>
        &nbsp;·&nbsp;
        <a href="{{ route('central.terms') }}" class="hover:text-primary-600 transition-colors">Terms</a>
        &nbsp;·&nbsp;
        <a href="{{ route('central.privacy') }}" class="hover:text-primary-600 transition-colors">Privacy</a>
        &nbsp;·&nbsp;
        <a href="{{ route('central.contact') }}" class="hover:text-primary-600 transition-colors">Contact</a>
    </footer>

</body>
</html>
