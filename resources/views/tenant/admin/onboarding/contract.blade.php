<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Service Agreement — {{ $school->name ?? 'School' }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-size: 14px;
            line-height: 1.7;
            color: #1a1a2e;
            background: #f0f2f5;
        }

        /* ── Toolbar ── */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #1e3a5f;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        .toolbar a, .toolbar button {
            color: #fff;
            text-decoration: none;
            font-family: 'Inter', 'Helvetica Neue', sans-serif;
            font-size: 13px;
            font-weight: 600;
            background: none;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .toolbar a:hover { background: rgba(255,255,255,0.12); }
        .toolbar button { background: #f5a623; color: #1a1a2e; }
        .toolbar button:hover { background: #e09000; }
        .toolbar-title {
            font-family: 'Inter', 'Helvetica Neue', sans-serif;
            font-size: 14px;
            font-weight: 600;
            opacity: 0.85;
        }

        /* ── Document wrapper ── */
        .document-wrapper {
            max-width: 860px;
            margin: 32px auto 60px;
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 60px 72px;
        }

        /* ── Document header ── */
        .doc-header {
            text-align: center;
            border-bottom: 3px double #1e3a5f;
            padding-bottom: 28px;
            margin-bottom: 32px;
        }
        .doc-header .doc-title {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #1e3a5f;
        }
        .doc-header .doc-subtitle {
            font-size: 14px;
            color: #4a5568;
            margin-top: 6px;
            font-style: italic;
        }
        .doc-header .doc-meta {
            margin-top: 16px;
            font-size: 13px;
            color: #666;
            font-family: 'Courier New', monospace;
        }
        .doc-header .doc-meta span {
            display: inline-block;
            margin: 0 12px;
        }

        /* ── Parties box ── */
        .parties-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border: 1.5px solid #c8d3e0;
            border-radius: 4px;
            margin-bottom: 36px;
            overflow: hidden;
        }
        .party {
            padding: 20px 24px;
        }
        .party:first-child {
            border-right: 1.5px solid #c8d3e0;
            background: #f7f9fc;
        }
        .party-role {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #1e3a5f;
            margin-bottom: 10px;
            font-family: 'Helvetica Neue', sans-serif;
        }
        .party-name {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 4px;
        }
        .party-detail {
            font-size: 13px;
            color: #4a5568;
            line-height: 1.6;
        }

        /* ── Sections ── */
        .section {
            margin-bottom: 28px;
        }
        .section-heading {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #1e3a5f;
            background: #f0f4fa;
            padding: 8px 12px;
            border-left: 4px solid #1e3a5f;
            margin-bottom: 12px;
            font-family: 'Helvetica Neue', sans-serif;
        }
        .section-body {
            padding: 0 4px;
        }
        .section-body p {
            margin-bottom: 10px;
            color: #2d3748;
        }
        .section-body ul, .section-body ol {
            padding-left: 22px;
            margin-bottom: 10px;
            color: #2d3748;
        }
        .section-body li {
            margin-bottom: 5px;
        }
        .section-body .definition-term {
            font-weight: 700;
            color: #1a1a2e;
        }
        .section-body .highlight {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 3px;
            padding: 10px 14px;
            font-style: italic;
            color: #5a4000;
            margin-bottom: 10px;
        }

        /* ── Acceptance box ── */
        .acceptance-box {
            margin-top: 40px;
            border: 2px solid #1e3a5f;
            border-radius: 4px;
            overflow: hidden;
        }
        .acceptance-header {
            background: #1e3a5f;
            color: #fff;
            padding: 10px 20px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-family: 'Helvetica Neue', sans-serif;
        }
        .acceptance-body {
            padding: 20px 24px;
            background: #f7f9fc;
        }
        .acceptance-row {
            display: flex;
            gap: 12px;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .acceptance-label {
            font-weight: 700;
            color: #1e3a5f;
            min-width: 180px;
            font-family: 'Helvetica Neue', sans-serif;
        }
        .acceptance-value {
            color: #2d3748;
            font-family: 'Courier New', monospace;
        }
        .not-accepted-notice {
            margin-top: 40px;
            padding: 16px 20px;
            border: 1.5px dashed #c0c0c0;
            border-radius: 4px;
            background: #fafafa;
            color: #888;
            font-style: italic;
            text-align: center;
            font-size: 13px;
        }

        /* ── Footer ── */
        .doc-footer {
            margin-top: 48px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 11px;
            color: #a0aec0;
            font-family: 'Helvetica Neue', sans-serif;
            letter-spacing: 0.5px;
        }

        /* ── Print styles ── */
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .document-wrapper {
                box-shadow: none;
                margin: 0;
                padding: 40px 48px;
                max-width: 100%;
            }
            .acceptance-box { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

{{-- Toolbar --}}
<div class="toolbar">
    <a id="back-btn" href="{{ route('tenant.admin.onboarding') }}">
        &#8592; Return to Setup
    </a>
    <span class="toolbar-title">Service Agreement &mdash; {{ $school->name ?? 'School' }}</span>
    <button onclick="window.print()">
        &#128438; Save as PDF / Print
    </button>
</div>
<script>
    // If opened in a new tab (no previous history), show "Close Tab" instead of navigating
    if (window.history.length <= 1 || document.referrer === '') {
        var btn = document.getElementById('back-btn');
        btn.textContent = '✕ Close Tab & Return to Wizard';
        btn.href = 'javascript:void(0)';
        btn.onclick = function() { window.close(); };
    }
</script>

@php
    $onboarding = tenancy()->tenant && in_array(tenancy()->tenant->registration_status, ['pending', 'profile_complete']);
@endphp
@if($onboarding)
<div style="background:#fff3cd;border-bottom:2px solid #ffc107;padding:14px 28px;display:flex;align-items:center;gap:14px;font-family:'Inter','Helvetica Neue',sans-serif;font-size:13.5px;color:#664d03;">
    <span style="font-size:20px;">&#128221;</span>
    <span>
        <strong>Almost there!</strong> Review this agreement, then
        <strong>close this tab</strong> and tick the acceptance checkbox in the setup wizard to continue.
    </span>
    <a onclick="window.close(); return false;" href="javascript:void(0)"
       style="margin-left:auto;background:#ffc107;color:#000;padding:6px 16px;border-radius:6px;font-weight:600;text-decoration:none;white-space:nowrap;">
        ✕ Close &amp; Continue Setup
    </a>
</div>
@endif

{{-- Document --}}
<div class="document-wrapper">

    {{-- Document Header --}}
    <div class="doc-header">
        <div class="doc-title">Service Agreement</div>
        <div class="doc-subtitle">Schoolytics Platform &mdash; Hosted Software Services</div>
        <div class="doc-meta">
            <span>
                Date:
                @if($tenant->terms_accepted_at)
                    {{ $tenant->terms_accepted_at->format('d F Y') }}
                @else
                    {{ now()->format('d F Y') }}
                @endif
            </span>
            <span>&nbsp;|&nbsp;</span>
            <span>Reference: SA-{{ $tenant->id }}-{{ now()->year }}</span>
        </div>
    </div>

    {{-- Parties --}}
    <div class="parties-box">
        <div class="party">
            <div class="party-role">Service Provider</div>
            <div class="party-name">Schoolytics</div>
            <div class="party-detail">
                Cloud Software Services<br />
                support@schoolytics.app<br />
                schoolytics.app
            </div>
        </div>
        <div class="party">
            <div class="party-role">School / Client</div>
            <div class="party-name">{{ $school->name ?? '—' }}</div>
            <div class="party-detail">
                {{ $tenant->email ?? '—' }}<br />
                @if(!empty($school->city)){{ $school->city }}<br />@endif
                Plan: {{ ucfirst($tenant->plan ?? 'starter') }}
            </div>
        </div>
    </div>

    {{-- Section 1: Definitions --}}
    <div class="section">
        <div class="section-heading">1. Definitions</div>
        <div class="section-body">
            <p>In this Agreement, the following terms have the meanings set out below:</p>
            <ul>
                <li><span class="definition-term">"Service"</span> — the Schoolytics cloud-hosted platform and all associated features provided under the selected subscription plan.</li>
                <li><span class="definition-term">"Platform"</span> — the web application accessible via the School's dedicated subdomain (e.g., schoolname.schoolytics.app).</li>
                <li><span class="definition-term">"School Data"</span> — all data, content, and information uploaded, submitted, or generated by the School, its staff, parents, teachers, and students through the Platform.</li>
                <li><span class="definition-term">"User"</span> — any staff member, administrator, or branch manager granted an account on the School's Platform instance.</li>
                <li><span class="definition-term">"Access Code"</span> — a one-time or session code issued by the School to parents and teachers to authenticate their use of the public portal.</li>
                <li><span class="definition-term">"Tenant"</span> — the School's isolated instance of the Platform, identified by a unique subdomain and tenant ID.</li>
                <li><span class="definition-term">"Service Provider"</span> — Schoolytics, the entity providing the Platform and Services under this Agreement.</li>
            </ul>
        </div>
    </div>

    {{-- Section 2: Service Description --}}
    <div class="section">
        <div class="section-heading">2. Service Description</div>
        <div class="section-body">
            <p>Schoolytics provides a cloud-hosted school issue tracking and parent communication platform accessible via the School's dedicated subdomain. The Service includes:</p>
            <ul>
                <li>Issue submission by parents and teachers via a public portal using Access Codes.</li>
                <li>Issue tracking, status management, and staff assignment by school administrators.</li>
                <li>AI-powered issue analysis including sentiment analysis, urgency detection, and trend identification.</li>
                <li>Reporting dashboards with KPI metrics, SLA tracking, and CSAT survey results.</li>
                <li>In-app and email notifications for staff and contacts.</li>
                <li>Optional modules depending on plan: AI chatbot, broadcast messaging, WhatsApp integration.</li>
            </ul>
            <p>The Service is provided on a multi-tenant architecture with strict row-level data isolation between schools. The School's data is accessible only to authenticated users of that school's instance.</p>
        </div>
    </div>

    {{-- Section 3: Subscription & Plan --}}
    <div class="section">
        <div class="section-heading">3. Subscription &amp; Plan</div>
        <div class="section-body">
            <p>The School is subscribed to the <strong>{{ ucfirst($tenant->plan ?? 'Starter') }}</strong> plan. Plan features and limits are as described in the Service Provider's plan documentation at the time of signing.</p>
            <ul>
                <li>The Service Provider reserves the right to update plan tiers, features, and pricing with a minimum of 30 days advance written notice to the School's registered email address.</li>
                <li>Downgrading or upgrading plans may affect available features. The School will be notified of any feature changes affecting existing usage.</li>
                <li>The subscription period is as specified on the School's account. If no period is specified, the subscription is month-to-month.</li>
            </ul>
        </div>
    </div>

    {{-- Section 4: Payment Terms --}}
    <div class="section">
        <div class="section-heading">4. Payment Terms</div>
        <div class="section-body">
            <p>Subscription fees are determined by the selected plan and billing cycle agreed at the time of sign-up.</p>
            <ul>
                <li>Payment is due on the 1st of each billing period (monthly or annual).</li>
                <li>Subscriptions auto-renew at the end of each billing period unless the School provides written cancellation notice at least 14 days before renewal.</li>
                <li>Late payment of more than 7 days past the due date may result in temporary service suspension. Access will be restored within 24 hours of payment receipt.</li>
                <li>All fees are non-refundable except where required by applicable law.</li>
            </ul>
        </div>
    </div>

    {{-- Section 5: School Data & Ownership --}}
    <div class="section">
        <div class="section-heading">5. School Data &amp; Ownership</div>
        <div class="section-body">
            <p>All data submitted by the School, its staff, parents, and students through the Platform remains the <strong>exclusive property of the School</strong>. The Service Provider does not claim any ownership rights over School Data.</p>
            <ul>
                <li>The Service Provider processes School Data solely to deliver the Service as described in this Agreement.</li>
                <li>School Data will not be sold, rented, or disclosed to third parties except as required by law or with the School's explicit consent.</li>
                <li>The School may request a complete export of its data at any time. The Service Provider will fulfil such requests within 5 business days.</li>
                <li>Aggregate, anonymised, and de-identified usage statistics may be used by the Service Provider to improve the Platform.</li>
            </ul>
        </div>
    </div>

    {{-- Section 6: Data Protection & Security --}}
    <div class="section">
        <div class="section-heading">6. Data Protection &amp; Security</div>
        <div class="section-body">
            <p>The Service Provider implements industry-standard security measures to protect School Data, including:</p>
            <ul>
                <li>Encrypted data storage and transport (TLS/HTTPS for all connections).</li>
                <li>Role-based access controls limiting data access to authorised Users.</li>
                <li>Regular automated backups with point-in-time recovery capability.</li>
                <li>Activity logging and audit trails for sensitive operations.</li>
            </ul>
            <p>The School is responsible for:</p>
            <ul>
                <li>Safeguarding staff login credentials and ensuring they are not shared or disclosed to unauthorised parties.</li>
                <li>Managing the issuance and revocation of parent/teacher Access Codes responsibly.</li>
                <li>Promptly reporting any suspected security breach or unauthorised access to the Service Provider.</li>
            </ul>
        </div>
    </div>

    {{-- Section 7: AI-Generated Content --}}
    <div class="section">
        <div class="section-heading">7. AI-Generated Content</div>
        <div class="section-body">
            <div class="highlight">
                Important: AI outputs provided by the Platform are advisory only. They do not constitute professional, legal, medical, or psychological advice.
            </div>
            <p>The Platform uses artificial intelligence to analyse submitted issues and provide insights such as sentiment scores, urgency flags, theme detection, and trend alerts. These features are designed to assist school administrators in prioritising and responding to issues effectively.</p>
            <ul>
                <li>AI analysis results are generated automatically and may not always be accurate or complete.</li>
                <li>The School bears sole responsibility for all decisions made in response to AI-generated recommendations.</li>
                <li>The Service Provider does not warrant the accuracy, completeness, or fitness for purpose of any AI-generated output.</li>
                <li>Parents and students are notified that their submitted issues may be analysed by automated AI systems as part of the platform's service.</li>
            </ul>
        </div>
    </div>

    {{-- Section 8: Acceptable Use --}}
    <div class="section">
        <div class="section-heading">8. Acceptable Use</div>
        <div class="section-body">
            <p>The School agrees to use the Platform only for legitimate educational communication and issue management purposes. The following activities are strictly prohibited:</p>
            <ul>
                <li>Sending unsolicited bulk messages (spam) through the Platform.</li>
                <li>Harassment, threats, or abusive communication directed at any party.</li>
                <li>Publishing, uploading, or transmitting illegal, defamatory, or obscene content.</li>
                <li>Attempting to access, probe, or interfere with other schools' tenant data.</li>
                <li>Reverse engineering, decompiling, or attempting to derive the source code of the Platform.</li>
                <li>Using the Platform in any manner that violates applicable laws or regulations.</li>
            </ul>
            <p>Violations of this Acceptable Use policy may result in immediate account suspension without refund. The Service Provider will notify the School of any violation where reasonably practicable before suspension.</p>
        </div>
    </div>

    {{-- Section 9: Uptime & Support --}}
    <div class="section">
        <div class="section-heading">9. Uptime &amp; Support</div>
        <div class="section-body">
            <p>The Service Provider targets a monthly uptime availability of <strong>99.5%</strong>, excluding scheduled maintenance windows.</p>
            <ul>
                <li>Scheduled maintenance that may cause service interruption will be communicated at least 24 hours in advance via the School's registered email address.</li>
                <li>Unplanned outages will be communicated as soon as reasonably possible, with status updates provided until resolution.</li>
                <li>Technical support is provided via email to <strong>support@schoolytics.app</strong> during business hours (Sunday–Thursday, 08:00–17:00 local time).</li>
                <li>Critical security issues will receive priority response within 4 business hours.</li>
            </ul>
        </div>
    </div>

    {{-- Section 10: Term & Termination --}}
    <div class="section">
        <div class="section-heading">10. Term &amp; Termination</div>
        <div class="section-body">
            <p>This Agreement commences on the date of electronic acceptance and continues for the duration of the subscription period, auto-renewing as described in Section 4.</p>
            <ul>
                <li>Either party may terminate this Agreement with <strong>30 days written notice</strong> delivered to the other party's registered email address.</li>
                <li>The Service Provider may terminate immediately upon serious breach of the Acceptable Use policy (Section 8) or non-payment of more than 30 days.</li>
                <li>Upon termination, the School's data will remain available for <strong>export for 14 days</strong> following the termination date.</li>
                <li>After the 14-day export window, all School Data will be permanently and irreversibly deleted from the Service Provider's systems.</li>
                <li>The School may request written confirmation of data deletion after the 14-day period.</li>
            </ul>
        </div>
    </div>

    {{-- Section 11: Limitation of Liability --}}
    <div class="section">
        <div class="section-heading">11. Limitation of Liability</div>
        <div class="section-body">
            <p>The Service is provided on an <strong>"as-is"</strong> and <strong>"as-available"</strong> basis. To the fullest extent permitted by applicable law:</p>
            <ul>
                <li>The Service Provider makes no warranties, express or implied, regarding the Platform's fitness for a particular purpose, accuracy, or uninterrupted availability.</li>
                <li>The Service Provider's total cumulative liability for any claim arising out of or related to this Agreement shall not exceed the total fees paid by the School in the <strong>3 months preceding the claim</strong>.</li>
                <li>The Service Provider shall not be liable for any indirect, consequential, incidental, special, or punitive damages, including loss of data, loss of revenue, or loss of business opportunity.</li>
                <li>Nothing in this Agreement excludes liability for death or personal injury caused by negligence, or for fraud or fraudulent misrepresentation.</li>
            </ul>
        </div>
    </div>

    {{-- Section 12: Governing Law --}}
    <div class="section">
        <div class="section-heading">12. Governing Law &amp; Dispute Resolution</div>
        <div class="section-body">
            <p>This Agreement shall be governed by and construed in accordance with applicable local law in the jurisdiction where the Service Provider is registered.</p>
            <ul>
                <li>In the event of any dispute, controversy, or claim arising out of or relating to this Agreement, the parties agree to first attempt resolution through good-faith negotiation for a period of 30 days.</li>
                <li>If good-faith negotiation fails to resolve the dispute, the matter shall be referred to binding arbitration conducted by a mutually agreed arbitrator.</li>
                <li>Nothing in this clause prevents either party from seeking urgent injunctive relief from a court of competent jurisdiction.</li>
            </ul>
        </div>
    </div>

    {{-- Acceptance Record --}}
    @if($tenant->terms_accepted_at)
        <div class="acceptance-box">
            <div class="acceptance-header">Electronic Acceptance Record</div>
            <div class="acceptance-body">
                <div class="acceptance-row">
                    <span class="acceptance-label">Electronically accepted by:</span>
                    <span class="acceptance-value">{{ $admin->name ?? '—' }} ({{ $tenant->email ?? '—' }})</span>
                </div>
                <div class="acceptance-row">
                    <span class="acceptance-label">On behalf of:</span>
                    <span class="acceptance-value">{{ $school->name ?? '—' }}</span>
                </div>
                <div class="acceptance-row">
                    <span class="acceptance-label">Date &amp; Time:</span>
                    <span class="acceptance-value">{{ $tenant->terms_accepted_at->format('d F Y, H:i:s T') }}</span>
                </div>
                <div class="acceptance-row">
                    <span class="acceptance-label">IP Address:</span>
                    <span class="acceptance-value">{{ $tenant->terms_accepted_ip ?? '—' }}</span>
                </div>
                <div class="acceptance-row">
                    <span class="acceptance-label">Agreement Version:</span>
                    <span class="acceptance-value">{{ $tenant->terms_accepted_version ?? '—' }}</span>
                </div>
                <div class="acceptance-row">
                    <span class="acceptance-label">Reference:</span>
                    <span class="acceptance-value">SA-{{ $tenant->id }}-{{ $tenant->terms_accepted_at->year }}</span>
                </div>
            </div>
        </div>
    @else
        <div class="not-accepted-notice">
            This agreement has not yet been electronically accepted.
        </div>
    @endif

    {{-- Footer --}}
    <div class="doc-footer">
        Schoolytics &middot; Confidential &middot; Page 1 of 1
    </div>

</div>

</body>
</html>
