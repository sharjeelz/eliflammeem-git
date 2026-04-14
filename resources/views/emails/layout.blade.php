<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', config('app.name'))</title>
    <style>
        /* ── Reset & base ─────────────────────────────────────────────── */
        body            { margin: 0; padding: 0; background: #f0f2f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: #1a202c; }
        .outer          { padding: 48px 16px; }
        .wrap           { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 40px rgba(0,0,0,.10); }

        /* ── Header ───────────────────────────────────────────────────── */
        .header         { background: linear-gradient(135deg, #1e1b4b 0%, #3730a3 50%, #4f46e5 100%); padding: 48px 48px 40px; text-align: center; }
        .header-logo    { display: inline-block; background: rgba(255,255,255,.15); border-radius: 14px; padding: 12px 22px; margin-bottom: 24px; }
        .header-logo span { color: #fff; font-size: 16px; font-weight: 800; letter-spacing: -.3px; }
        .header h1      { margin: 0 0 8px; color: #ffffff; font-size: 26px; font-weight: 800; letter-spacing: -.5px; line-height: 1.25; }
        .header p       { margin: 0; color: rgba(255,255,255,.72); font-size: 15px; line-height: 1.5; }

        /* ── Body ─────────────────────────────────────────────────────── */
        .body           { padding: 44px 48px; }
        p               { margin: 0 0 18px; font-size: 15px; line-height: 1.7; color: #374151; }
        hr              { border: none; border-top: 1px solid #e5e7eb; margin: 36px 0; }

        /* ── Pill / tag ───────────────────────────────────────────────── */
        .pill           { display: inline-block; background: #eef2ff; color: #4338ca; font-size: 13px; font-weight: 700; padding: 4px 12px; border-radius: 20px; margin-bottom: 24px; }

        /* ── Info rows (label + value) ────────────────────────────────── */
        .label          { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; margin: 24px 0 6px; }
        .value          { font-size: 15px; color: #111827; font-weight: 500; }

        /* ── Status badges ────────────────────────────────────────────── */
        .badge          { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700; }
        .badge-new          { background: #e8f5e9; color: #2e7d32; }
        .badge-in_progress  { background: #e3f2fd; color: #1565c0; }
        .badge-resolved     { background: #f3e5f5; color: #6a1b9a; }
        .badge-closed       { background: #eceff1; color: #546e7a; }

        /* ── Credential table ─────────────────────────────────────────── */
        .creds-title    { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; margin: 32px 0 12px; }
        .creds          { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 28px; }
        .cred-row       { display: flex; align-items: flex-start; padding: 14px 20px; border-bottom: 1px solid #e2e8f0; }
        .cred-row:last-child { border-bottom: none; }
        .cred-label     { width: 110px; flex-shrink: 0; font-size: 13px; font-weight: 600; color: #6b7280; padding-top: 1px; }
        .cred-value     { font-size: 14px; font-weight: 600; color: #111827; word-break: break-all; }
        .cred-value a   { color: #4f46e5; text-decoration: none; }
        .cred-value.mono{ font-family: 'Courier New', Courier, monospace; letter-spacing: .5px; background: #f1f5f9; padding: 2px 8px; border-radius: 5px; }

        /* ── Warning / notice box ─────────────────────────────────────── */
        .pw-notice      { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 18px; margin: 0 0 28px; font-size: 13px; color: #92400e; line-height: 1.5; }
        .pw-notice strong { color: #78350f; }

        /* ── CTA button ───────────────────────────────────────────────── */
        .btn-wrap       { text-align: center; margin: 32px 0; }
        .btn            { display: inline-block; background: linear-gradient(135deg, #3730a3, #4f46e5); color: #ffffff !important; text-decoration: none; padding: 15px 40px; border-radius: 10px; font-size: 15px; font-weight: 700; letter-spacing: -.2px; }

        /* ── Support block ────────────────────────────────────────────── */
        .support        { background: #f8fafc; border-radius: 10px; padding: 22px 28px; margin: 28px 0 0; }
        .support p      { margin: 0 0 6px; font-size: 14px; color: #374151; }
        .support p:last-child { margin: 0; }
        .support a      { color: #4f46e5; text-decoration: none; font-weight: 600; }

        /* ── Footer ───────────────────────────────────────────────────── */
        .footer         { background: #1e1b4b; padding: 28px 48px; text-align: center; }
        .footer p       { margin: 0 0 6px; font-size: 12px; color: rgba(255,255,255,.45); line-height: 1.6; }
        .footer p:last-child { margin: 0; }

        @media (max-width: 600px) {
            .body, .header, .footer { padding-left: 24px; padding-right: 24px; }
            .cred-label { width: 90px; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="outer">
<div class="wrap">

    {{-- ── Header ─────────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-logo">
            <span>&#9733; {{ config('app.name') }}</span>
        </div>
        <h1>@yield('header-title', config('app.name'))</h1>
        @hasSection('header-subtitle')
            <p>@yield('header-subtitle')</p>
        @endif
    </div>

    {{-- ── Body content ────────────────────────────────────────────── --}}
    <div class="body">
        @yield('content')
    </div>

    {{-- ── Footer ─────────────────────────────────────────────────── --}}
    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>@yield('footer-note')</p>
    </div>

</div>
</div>
</body>
</html>
