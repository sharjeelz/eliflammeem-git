<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Schoolytics — User Manual</title>
    <style>
        /* ── Reset & Base ─────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 15px; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #1e293b;
            background: #f8fafc;
            line-height: 1.7;
        }

        /* ── Layout ───────────────────────────────────────────────────── */
        .page-wrap {
            max-width: 860px;
            width: 100%;
            margin: 0 auto;
            padding: 2.5rem 1.5rem 4rem;
        }

        /* ── Toolbar ──────────────────────────────────────────────────── */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #1e293b;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.5rem;
            font-size: 0.85rem;
            gap: 1rem;
        }
        .toolbar a { color: #94a3b8; text-decoration: none; }
        .toolbar a:hover { color: #fff; }
        .btn-print {
            background: #4f46e5;
            color: #fff !important;
            border: none;
            padding: 0.45rem 1.1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .btn-print:hover { background: #4338ca; }

        /* ── Cover ────────────────────────────────────────────────────── */
        .cover {
            background: linear-gradient(135deg, #3730a3 0%, #4f46e5 100%);
            color: #fff;
            border-radius: 16px;
            padding: 3rem 2.5rem 2.5rem;
            margin-bottom: 2.5rem;
        }
        .cover .logo { font-size: 2rem; font-weight: 800; letter-spacing: -0.5px; }
        .cover .logo span { color: #a5b4fc; }
        .cover .tagline { margin-top: 0.4rem; font-size: 1rem; color: #c7d2fe; }
        .cover .meta { margin-top: 2rem; display: flex; gap: 2rem; flex-wrap: wrap; }
        .cover .meta-item { font-size: 0.82rem; color: #a5b4fc; }
        .cover .meta-item strong { display: block; color: #fff; font-size: 0.9rem; }

        /* ── TOC ──────────────────────────────────────────────────────── */
        .toc {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem 2rem;
            margin-bottom: 2.5rem;
        }
        .toc h2 { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 1rem; }
        .toc ol { padding-left: 1.25rem; }
        .toc li { margin: 0.3rem 0; }
        .toc a { color: #4f46e5; text-decoration: none; font-size: 0.9rem; }
        .toc a:hover { text-decoration: underline; }
        .toc .sub { padding-left: 1rem; list-style: disc; }
        .toc .sub a { color: #64748b; font-size: 0.85rem; }

        /* ── Sections ─────────────────────────────────────────────────── */
        .section {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            overflow-x: auto;
            box-sizing: border-box;
            width: 100%;
        }
        .section-num {
            display: inline-block;
            background: #4f46e5;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 20px;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }
        h1 { font-size: 1.6rem; font-weight: 800; margin-bottom: 0.5rem; }
        h2 { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 1.5rem 0 0.75rem; }
        h3 { font-size: 1rem; font-weight: 600; color: #334155; margin: 1.25rem 0 0.5rem; }
        p  { margin-bottom: 0.85rem; color: #334155; }
        ul, ol { padding-left: 1.4rem; margin-bottom: 0.85rem; }
        li { margin: 0.3rem 0; color: #334155; }

        /* ── Callouts ─────────────────────────────────────────────────── */
        .tip, .warn, .note {
            border-radius: 8px;
            padding: 0.85rem 1.1rem;
            margin: 1rem 0;
            font-size: 0.88rem;
            display: flex;
            gap: 0.65rem;
            align-items: flex-start;
        }
        .tip  { background: #f0fdf4; border-left: 4px solid #22c55e; color: #166534; }
        .warn { background: #fffbeb; border-left: 4px solid #f59e0b; color: #92400e; }
        .note { background: #eff6ff; border-left: 4px solid #3b82f6; color: #1e40af; }
        .tip::before  { content: '✅'; flex-shrink: 0; }
        .warn::before { content: '⚠️'; flex-shrink: 0; }
        .note::before { content: '💡'; flex-shrink: 0; }

        /* ── Badge / Status ───────────────────────────────────────────── */
        .badge {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.15rem 0.55rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .badge-new       { background: #dbeafe; color: #1d4ed8; }
        .badge-progress  { background: #fef3c7; color: #92400e; }
        .badge-resolved  { background: #d1fae5; color: #065f46; }
        .badge-closed    { background: #f1f5f9; color: #475569; }
        .badge-high      { background: #fee2e2; color: #991b1b; }
        .badge-medium    { background: #fef3c7; color: #92400e; }
        .badge-low       { background: #f0fdf4; color: #166534; }

        /* ── Step list ────────────────────────────────────────────────── */
        .steps { counter-reset: step; list-style: none; padding: 0; }
        .steps li {
            counter-increment: step;
            padding: 0.6rem 0 0.6rem 2.5rem;
            position: relative;
            border-left: 2px solid #e2e8f0;
            margin-left: 0.75rem;
        }
        .steps li::before {
            content: counter(step);
            position: absolute;
            left: -0.85rem;
            top: 0.55rem;
            width: 1.5rem; height: 1.5rem;
            background: #4f46e5;
            color: #fff;
            border-radius: 50%;
            font-size: 0.72rem;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }

        /* ── Table ────────────────────────────────────────────────────── */
        table { width: 100%; max-width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 0.87rem; table-layout: auto; word-break: break-word; }
        th { background: #f8fafc; text-align: left; padding: 0.6rem 0.85rem; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
        td { padding: 0.55rem 0.85rem; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        tr:last-child td { border-bottom: none; }

        /* ── Divider ──────────────────────────────────────────────────── */
        hr { border: none; border-top: 1px solid #e2e8f0; margin: 1.5rem 0; }

        /* ── Footer ───────────────────────────────────────────────────── */
        .manual-footer {
            text-align: center;
            color: #94a3b8;
            font-size: 0.8rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        /* ── Print ────────────────────────────────────────────────────── */
        @media print {
            body { background: #fff; font-size: 13px; }
            .toolbar { display: none; }
            .page-wrap { max-width: 100%; padding: 1rem; }
            .cover { border-radius: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .section { border: 1px solid #ccc; break-inside: avoid; }
            h2 { break-after: avoid; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
</head>
<body>

{{-- Toolbar --}}
<div class="toolbar">
    <div style="display:flex;align-items:center;gap:1rem;">
        @auth
        <a href="{{ route('tenant.admin.dashboard') }}">← Back to Dashboard</a>
        @else
        <a href="{{ url('/') }}">← Back to Portal</a>
        @endauth
        <span style="color:#475569;">Eliflameem User Manual</span>
    </div>
    <button class="btn-print" onclick="window.print()">⬇ Save as PDF / Print</button>
</div>

<div class="page-wrap">

    {{-- Cover --}}
    <div class="cover">
        <div class="logo">Eliflameem</div>
        <div class="tagline">School Issue Tracking &amp; Parent Communication Platform</div>
        <div class="meta">
            <div class="meta-item"><strong>Version</strong>2.0</div>
            <div class="meta-item"><strong>Audience</strong>School Admins, Branch Managers, Staff</div>
            <div class="meta-item"><strong>Updated</strong>{{ now()->format('F Y') }}</div>
        </div>
    </div>

    {{-- Table of Contents --}}
    <div class="toc" id="toc">
        <h2>Table of Contents</h2>
        <ol>
            <li><a href="#s1">Getting Started</a></li>
            <li><a href="#s2">Dashboard Overview</a></li>
            <li><a href="#s3">Managing Branches &amp; Staff</a>
                <ul class="sub">
                    <li><a href="#s3-branches">Branches</a></li>
                    <li><a href="#s3-staff">Staff &amp; Roles</a></li>
                    <li><a href="#s3-categories">Issue Categories</a></li>
                </ul>
            </li>
            <li><a href="#s4">Roster Contacts (Parents &amp; Teachers)</a>
                <ul class="sub">
                    <li><a href="#s4-add">Adding Contacts</a></li>
                    <li><a href="#s4-codes">Access Codes</a></li>
                    <li><a href="#s4-csv">CSV Import</a></li>
                    <li><a href="#s4-branch-change">Changing a Contact's Branch</a></li>
                    <li><a href="#s4-bulk-branch">Bulk Branch Change</a></li>
                </ul>
            </li>
            <li><a href="#s5">Issue Management</a>
                <ul class="sub">
                    <li><a href="#s5-index">Issues Index &amp; Filters</a></li>
                    <li><a href="#s5-quick-filters">Quick Filter Presets</a></li>
                    <li><a href="#s5-saved-filters">Saved Filters</a></li>
                    <li><a href="#s5-workflow">Workflow &amp; Statuses</a></li>
                    <li><a href="#s5-reopen">Still a Problem? — Contact Reopen Flow</a></li>
                    <li><a href="#s5-detail">Issue Detail Page</a></li>
                    <li><a href="#s5-assign">Assignment</a></li>
                    <li><a href="#s5-priority">Priority &amp; Urgency</a></li>
                    <li><a href="#s5-type">Submission Type &amp; Positive Sentiment</a></li>
                    <li><a href="#s5-comments">Comments &amp; Replies</a></li>
                    <li><a href="#s5-notes">Private Notes</a></li>
                    <li><a href="#s5-spam">Spam — Rules &amp; Auto-Actions</a></li>
                    <li><a href="#s5-ai">AI Analysis</a></li>
                    <li><a href="#s5-sla">SLA &amp; Overdue</a></li>
                    <li><a href="#s5-activity">Activity Log</a></li>
                </ul>
            </li>
            <li><a href="#s6">Reports &amp; Analytics</a></li>
            <li><a href="#s7">Broadcasts (Bulk Messaging)</a></li>
            <li><a href="#s8">AI Chatbot</a></li>
            <li><a href="#s9">School Settings</a></li>
            <li><a href="#s10">The Parent &amp; Teacher Portal</a></li>
            <li><a href="#s11">Roles &amp; Permissions Quick Reference</a></li>
            <li><a href="#s12">Frequently Asked Questions</a></li>
        </ol>
    </div>

    {{-- ─── Section 1: Getting Started ─────────────────────────────────── --}}
    <div class="section" id="s1">
        <span class="section-num">01</span>
        <h1>Getting Started</h1>
        <p>Welcome to <strong>Schoolytics</strong> — a platform that helps your school collect, track, and resolve concerns raised by parents and teachers, while keeping everyone informed at every step.</p>

        <h2>Logging In</h2>
        <ol class="steps">
            <li>Open your school's admin URL — it looks like <strong>yourschool.schoolytics.app/admin/login</strong></li>
            <li>Enter the email address and password sent to you in the welcome email.</li>
            <li>You will land on the <strong>Dashboard</strong>.</li>
        </ol>

        <div class="tip">Your initial password was emailed to you when the account was created. Change it immediately via <strong>My Profile → Change Password</strong>.</div>

        <h2>First-time Setup Checklist</h2>
        <table>
            <thead><tr><th>Step</th><th>Where</th><th>Why</th></tr></thead>
            <tbody>
                <tr><td>Upload school logo</td><td>School Settings → Branding</td><td>Shown on the parent portal</td></tr>
                <tr><td>Set contact email &amp; phone</td><td>School Settings → Contact Info</td><td>Displayed to parents on the portal</td></tr>
                <tr><td>Review issue categories</td><td>Settings → Issue Categories</td><td>10 default categories are pre-loaded; add or rename as needed</td></tr>
                <tr><td>Create branches (if needed)</td><td>Settings → Branches</td><td>Each campus or building can be a branch</td></tr>
                <tr><td>Add staff members</td><td>Management → Users</td><td>Give teachers and admins access</td></tr>
                <tr><td>Import roster contacts</td><td>Contacts → Roster Contacts → Import</td><td>Upload parents and teachers via CSV</td></tr>
                <tr><td>Send access codes</td><td>Contacts → Roster Contacts → Bulk Actions</td><td>Parents need a code to submit issues</td></tr>
            </tbody>
        </table>
    </div>

    {{-- ─── Section 2: Dashboard ────────────────────────────────────────── --}}
    <div class="section" id="s2">
        <span class="section-num">02</span>
        <h1>Dashboard Overview</h1>
        <p>The dashboard gives you a real-time snapshot of everything happening across your school.</p>

        <h2>KPI Cards (top row)</h2>
        <table>
            <thead><tr><th>Card</th><th>Meaning</th></tr></thead>
            <tbody>
                <tr><td>Open Issues</td><td>All issues that are New or In Progress — need attention</td></tr>
                <tr><td>Resolved This Week</td><td>Issues moved to Resolved in the last 7 days</td></tr>
                <tr><td>Avg. Response Time</td><td>Average hours from submission to first status change</td></tr>
                <tr><td>CSAT Score</td><td>Average satisfaction rating from closed-issue surveys (1–5)</td></tr>
            </tbody>
        </table>

        <h2>AI Widgets</h2>
        <ul>
            <li><strong>Needs Attention</strong> — issues the AI flagged as urgent or escalating. Review these first.</li>
            <li><strong>Hot Topics</strong> — recurring themes detected across recent issues (e.g. "Transport delays", "Homework load").</li>
        </ul>

        <h2>Charts &amp; Tables</h2>
        <ul>
            <li><strong>Branch breakdown</strong> — open vs resolved per branch</li>
            <li><strong>Sentiment trend</strong> — whether parent sentiment is improving or declining over time</li>
            <li><strong>Staff performance</strong> — resolved issues per staff member this month</li>
        </ul>

        <div class="note">Branch managers only see data for their own branch(es). Staff only see issues assigned to them.</div>
    </div>

    {{-- ─── Section 3: Branches & Staff ────────────────────────────────── --}}
    <div class="section" id="s3">
        <span class="section-num">03</span>
        <h1>Managing Branches &amp; Staff</h1>

        <h2 id="s3-branches">Branches</h2>
        <p>A <strong>Branch</strong> represents a campus, building, or division. Every issue belongs to a branch. If your school has one location, you still have one branch (created automatically when your account was provisioned).</p>

        <ol class="steps">
            <li>Go to <strong>Settings → Branches</strong></li>
            <li>Click <strong>Add Branch</strong></li>
            <li>Enter branch name (e.g. "Main Campus", "North Wing")</li>
            <li>Save</li>
        </ol>

        <div class="warn">Deleting a branch is not possible once issues are linked to it. Rename it instead.</div>

        <h2 id="s3-staff">Staff &amp; Roles</h2>
        <p>There are three staff roles in Schoolytics:</p>

        <table>
            <thead><tr><th>Role</th><th>What they can do</th><th>What they cannot do</th></tr></thead>
            <tbody>
                <tr>
                    <td><strong>Admin</strong></td>
                    <td>Everything — see all issues across all branches, manage users, settings, reports</td>
                    <td>—</td>
                </tr>
                <tr>
                    <td><strong>Branch Manager</strong></td>
                    <td>See all issues in their branch(es), assign to staff, close issues</td>
                    <td>Cannot access other branches, cannot manage school settings</td>
                </tr>
                <tr>
                    <td><strong>Staff</strong></td>
                    <td>See issues assigned to them, add comments, mark as Resolved</td>
                    <td>Cannot close issues, cannot assign to others, cannot see unassigned issues</td>
                </tr>
            </tbody>
        </table>

        <h3>Adding a Staff Member</h3>
        <ol class="steps">
            <li>Go to <strong>Management → Users</strong> → <strong>Add User</strong></li>
            <li>Enter name, email, and select a role</li>
            <li>Assign to one or more branches</li>
            <li>Optionally assign issue categories (staff will be auto-assigned issues in those categories)</li>
            <li>Save — the staff member receives a welcome email with their login link</li>
        </ol>

        <h2 id="s3-categories">Issue Categories</h2>
        <p>Categories help route issues to the right staff member automatically. 10 default categories are created for every school:</p>
        <p style="color:#64748b; font-size:0.87rem;">Transport · Academics · Facilities · Behaviour · Food &amp; Dining · Communication · Health &amp; Safety · Fees &amp; Payments · Technology Issues · General Complaints</p>

        <h3>Auto-Assignment via Categories</h3>
        <p>When a parent submits an issue in a category, Schoolytics automatically assigns it to the first staff member in that branch who is linked to that category. If no match, it falls to the branch manager.</p>

        <div class="tip">Go to <strong>Settings → Auto-assign Rules</strong> to review and configure this routing logic.</div>
    </div>

    {{-- ─── Section 4: Roster Contacts ──────────────────────────────────── --}}
    <div class="section" id="s4">
        <span class="section-num">04</span>
        <h1>Roster Contacts (Parents &amp; Teachers)</h1>
        <p>Roster Contacts are the parents and teachers who use the public portal to submit issues. They do <strong>not</strong> have login accounts — they authenticate using a one-time <strong>Access Code</strong>.</p>

        <h2 id="s4-add">Adding Contacts</h2>
        <p>There are three ways to add contacts:</p>

        <table>
            <thead><tr><th>Method</th><th>Best for</th></tr></thead>
            <tbody>
                <tr><td><strong>Manual</strong> — Add one contact at a time via the form</td><td>Small additions or corrections</td></tr>
                <tr><td><strong>CSV Import</strong> — Upload a spreadsheet of contacts</td><td>Initial bulk upload or end-of-year roster refresh</td></tr>
                <tr><td><strong>REST API</strong> — Push contacts from your school MIS/SIS system</td><td>Automated sync with existing school software</td></tr>
            </tbody>
        </table>

        <h2 id="s4-codes">Access Codes</h2>
        <p>An Access Code is a short unique code (e.g. <code>XK92PL</code>) that a parent or teacher enters on the portal to submit or track an issue. Think of it as a temporary password.</p>

        <table>
            <thead><tr><th>Rule</th><th>Detail</th></tr></thead>
            <tbody>
                <tr><td>One active code per contact</td><td>A contact can only have one code at a time</td></tr>
                <tr><td>Expires after 7 days</td><td>Resend to refresh the expiry</td></tr>
                <tr><td>Consumed on submission</td><td>Once used to submit an issue, the code is locked until that issue is closed</td></tr>
                <tr><td>Reset on issue close</td><td>When an issue is closed, the code resets so the parent can submit again</td></tr>
            </tbody>
        </table>

        <h3>Sending Codes — Bulk Actions</h3>
        <ol class="steps">
            <li>Go to <strong>Contacts → Roster Contacts</strong></li>
            <li>Select contacts using the checkboxes (or select all)</li>
            <li>From <strong>Bulk Actions</strong> choose <strong>Generate &amp; Send Codes</strong></li>
            <li>Codes are emailed to each contact automatically</li>
        </ol>

        <div class="tip">Parents can also request their own code from the portal by clicking <strong>"Request Access Code"</strong> and entering their email or phone.</div>

        <h2 id="s4-csv">CSV Import</h2>
        <p>Download the template from the Import page. Required columns:</p>
        <table>
            <thead><tr><th>Column</th><th>Required</th><th>Notes</th></tr></thead>
            <tbody>
                <tr><td>name</td><td>Yes</td><td>Full name</td></tr>
                <tr><td>email</td><td>Recommended</td><td>Used to send access codes and notifications</td></tr>
                <tr><td>phone</td><td>Optional</td><td>Used for SMS if configured</td></tr>
                <tr><td>role</td><td>Yes</td><td><code>parent</code> or <code>teacher</code></td></tr>
                <tr><td>branch_name</td><td>Recommended</td><td>Must match an existing branch name exactly</td></tr>
                <tr><td>external_id</td><td>Optional</td><td>Your MIS student/parent ID — used for smart upsert on re-import</td></tr>
            </tbody>
        </table>

        <div class="note">On re-import, Schoolytics matches by <strong>external_id → email → phone</strong> and updates existing contacts rather than creating duplicates. Enable <strong>"Deactivate missing contacts"</strong> to automatically deactivate contacts no longer in the file.</div>

        {{-- 4.4 Branch Change --}}
        <h2 id="s4-branch-change">Changing a Contact's Branch</h2>
        <p>If a parent or teacher moves to a different campus or section, you can update their branch from the <strong>Edit Contact</strong> page. Because branch membership controls which staff team handles their issues, changing a branch triggers a set of automatic actions to keep data consistent.</p>

        <h3>How to Change a Contact's Branch</h3>
        <ol class="steps">
            <li>Go to <strong>Contacts → Roster Contacts</strong> → find the contact → click <strong>Edit</strong></li>
            <li>In the <strong>Branch</strong> dropdown, the current branch is labelled <em>"(current)"</em></li>
            <li>Select the new branch</li>
            <li>A warning modal appears listing all impacts — review carefully</li>
            <li>Click the red <strong>Confirm Branch Change</strong> button</li>
        </ol>

        <h3>What Happens Automatically</h3>
        <table>
            <thead><tr><th>Action</th><th>Reason</th></tr></thead>
            <tbody>
                <tr><td>Access code is <strong>revoked</strong></td><td>The old code was linked to the previous branch. A new code must be generated for the new branch.</td></tr>
                <tr><td>All open issues are <strong>auto-closed</strong></td><td>Open issues belong to the old branch's team and cannot be transferred. The contact must submit fresh issues under their new branch.</td></tr>
                <tr><td>A <strong>public message</strong> is added to each closed issue</td><td>The contact sees an amber notice on the portal explaining their record was updated and they should request a new access code.</td></tr>
                <tr><td>An <strong>internal note</strong> is added to each closed issue</td><td>Audit record for your team showing who made the change and when.</td></tr>
                <tr><td>A <strong>status-changed activity</strong> is logged on each closed issue</td><td>Full audit trail with the note "Auto-closed — contact moved to [Branch Name]".</td></tr>
                <tr><td>A <strong>contact_moved activity</strong> is logged on already-closed/resolved issues</td><td>Ensures the move is recorded on all of the contact's issue history, not just open ones.</td></tr>
            </tbody>
        </table>

        <div class="warn">Branch changes cannot be undone automatically. If a contact was moved by mistake, change their branch back manually and generate a new access code. Their previously auto-closed issues will not be re-opened.</div>

        <div class="tip">After changing a contact's branch, generate and send a new access code so they can submit issues under their new branch without delay.</div>

        <h3>Inactive Branch Enforcement</h3>
        <p>If a contact's assigned branch is marked as <strong>inactive</strong> in school settings, they will be blocked from submitting new issues on the public portal. They will see the message: <em>"Your branch is currently inactive. Please contact the school for assistance."</em> Reactivate the branch or reassign the contact to an active branch to restore access.</p>

        {{-- 4.5 Bulk Branch Change --}}
        <h2 id="s4-bulk-branch">Bulk Branch Change</h2>
        <p>To move multiple contacts to a different branch at once, use the <strong>Bulk Actions</strong> menu on the Contacts list page.</p>

        <ol class="steps">
            <li>Go to <strong>Contacts → Roster Contacts</strong></li>
            <li>Select the contacts you want to move using the checkboxes (or use "Select All")</li>
            <li>From the <strong>Bulk Actions</strong> dropdown, choose <strong>Change Branch</strong></li>
            <li>Select the destination branch from the modal</li>
            <li>Review the warning — the same auto-actions apply as for a single branch change (codes revoked, open issues closed)</li>
            <li>Click <strong>Confirm</strong></li>
        </ol>

        <h3>Bulk Change Results</h3>
        <table>
            <thead><tr><th>Scenario</th><th>What the system does</th></tr></thead>
            <tbody>
                <tr><td>Some contacts are already in the target branch</td><td>They are silently skipped. The success message shows how many were skipped.</td></tr>
                <tr><td>All selected contacts are already in the target branch</td><td>No contacts are moved. An info banner is shown instead of a success message.</td></tr>
                <tr><td>Mix of contacts with open and closed issues</td><td>Open issues are auto-closed; already-closed issues receive a <em>contact_moved</em> activity log entry only.</td></tr>
            </tbody>
        </table>

        <div class="note">The success message after a bulk branch change shows exactly how many contacts were moved, how many open issues were auto-closed, and how many contacts were skipped.</div>
    </div>

    {{-- ─── Section 5: Issue Management ─────────────────────────────────── --}}
    <div class="section" id="s5">
        <span class="section-num">05</span>
        <h1>Issue Management</h1>

        {{-- 5.1 Issues Index --}}
        <h2 id="s5-index">Issues Index &amp; Filters</h2>
        <p>Go to <strong>Issues</strong> in the sidebar. Every issue in your scope is listed here, newest first. The page has two filter areas:</p>

        <h3>Search Bar</h3>
        <p>The search box at the top matches against the issue <strong>title</strong>, <strong>description</strong>, and <strong>public tracking ID</strong>. It is case-insensitive.</p>

        <h3>Filter Bar</h3>
        <p>Click <strong>Filters</strong> to expand the full filter panel. All filters work together — selecting multiple filters narrows results further.</p>

        <table>
            <thead><tr><th>Filter</th><th>What it does</th></tr></thead>
            <tbody>
                <tr><td><strong>Status</strong></td><td>Show only issues in a specific status (New / In Progress / Resolved / Closed)</td></tr>
                <tr><td><strong>Priority</strong></td><td>Filter by Low / Medium / High / Urgent</td></tr>
                <tr><td><strong>Assigned To</strong></td><td>Show only issues assigned to a specific staff member</td></tr>
                <tr><td><strong>Branch</strong></td><td>Filter by branch (Admins only — branch managers are locked to their own branches)</td></tr>
                <tr><td><strong>Category</strong></td><td>Show only issues in a specific category (e.g. Transport, Academics)</td></tr>
                <tr><td><strong>Date From / To</strong></td><td>Filter by submission date range</td></tr>
                <tr><td><strong>AI Urgency</strong></td><td>Filter by AI-detected urgency flag: <em>escalate</em> (critical) or <em>monitor</em> (watch closely)</td></tr>
                <tr><td><strong>AI Theme</strong></td><td>Filter by a detected topic/theme (e.g. "homework", "bus delays") — populated from AI analysis</td></tr>
                <tr><td><strong>Sentiment</strong></td><td>Filter by AI-detected sentiment: Positive / Neutral / Negative</td></tr>
                <tr><td><strong>Submission Type</strong></td><td>Filter by Complaint or Suggestion</td></tr>
            </tbody>
        </table>

        <h3>Quick View Tabs</h3>
        <p>Above the filter bar are three quick-view controls:</p>
        <table>
            <thead><tr><th>View</th><th>Shows</th></tr></thead>
            <tbody>
                <tr><td><strong>All Issues</strong> (default)</td><td>All non-spam issues in your scope</td></tr>
                <tr><td><strong>Anonymous</strong></td><td>Issues submitted without an access code. Visible to admins only — branch managers never see anonymous issues.</td></tr>
                <tr><td><strong>Spam</strong></td><td>Issues flagged as spam. Used for review and audit — see Spam section below.</td></tr>
            </tbody>
        </table>

        <h3>SLA Overdue &amp; Spam Indicators</h3>
        <p>Two compact badges appear in the Issues table header to surface urgent situations without cluttering the page:</p>
        <table>
            <thead><tr><th>Badge</th><th>Colour</th><th>What it means</th><th>Click behaviour</th></tr></thead>
            <tbody>
                <tr>
                    <td><strong>Overdue SLA</strong></td>
                    <td>Red</td>
                    <td>At least one issue has passed its SLA deadline and is still open</td>
                    <td>When inactive (light red): activates the overdue filter to show only those issues. When active (solid red with ✕): click ✕ to clear the filter and return to all issues.</td>
                </tr>
                <tr>
                    <td><strong>Spam Flagged</strong></td>
                    <td>Amber</td>
                    <td>There are issues flagged as spam waiting for review</td>
                    <td>When inactive (light amber): switches to the Spam view. When active (solid amber with ✕): click ✕ to return to all issues.</td>
                </tr>
            </tbody>
        </table>

        <div class="tip">The SLA Overdue badge becomes active (solid red) when you are already viewing the overdue filter. Click the ✕ on either badge to instantly return to your normal unfiltered issue list.</div>

        <div class="note">All filters are preserved in the URL — you can bookmark a filtered view (e.g. all urgent issues in Branch A) and share it with colleagues.</div>

        {{-- 5.1b Quick Filter Presets --}}
        <h2 id="s5-quick-filters">Quick Filter Presets</h2>
        <p>Above the filter bar is a row of one-click <strong>Quick Filters</strong> that instantly apply the most common filter combinations. Click any button to jump straight to that view — no need to configure the filter form manually.</p>

        <table>
            <thead><tr><th>Quick Filter</th><th>What it shows</th><th>Badge colour</th></tr></thead>
            <tbody>
                <tr>
                    <td><strong>Urgent &amp; Unassigned</strong></td>
                    <td>Open issues with Urgent priority that have no assigned staff member — the highest-priority queue</td>
                    <td>Red</td>
                </tr>
                <tr>
                    <td><strong>Unassigned</strong></td>
                    <td>All open issues (any priority) with no assigned staff member. Use this to ensure nothing falls through the cracks.</td>
                    <td>Grey</td>
                </tr>
                <tr>
                    <td><strong>Branch Moved</strong></td>
                    <td>All issues (including closed) where the contact's branch was changed and the issue was auto-closed as a result. Identified by the internal <code>unassigned_reason = contact_branch_changed</code> flag.</td>
                    <td>Amber</td>
                </tr>
                <tr>
                    <td><strong>New This Week</strong></td>
                    <td>Issues submitted since the start of the current week with New status</td>
                    <td>Blue</td>
                </tr>
                <tr>
                    <td><strong>In Progress</strong></td>
                    <td>All issues currently being worked on</td>
                    <td>Amber</td>
                </tr>
                <tr>
                    <td><strong>AI: Escalate</strong></td>
                    <td>Issues the AI flagged as requiring immediate escalation</td>
                    <td>Red</td>
                </tr>
                <tr>
                    <td><strong>Negative Sentiment</strong></td>
                    <td>Issues where the AI detected negative emotional tone from the contact</td>
                    <td>Red</td>
                </tr>
            </tbody>
        </table>

        <div class="tip">The active Quick Filter button is highlighted. Click it again or click <strong>Reset</strong> in the filter bar to clear the filter and return to all issues.</div>

        {{-- 5.1c Saved Filters --}}
        <h2 id="s5-saved-filters">Saved Filters</h2>
        <p>If you frequently use the same combination of filters (e.g. "High priority, Branch A, unassigned"), you can save it as a named preset and reapply it in one click.</p>

        <h3>Saving a Filter</h3>
        <ol class="steps">
            <li>Apply the filter combination you want to save using the Filters panel</li>
            <li>Once filters are active, a <strong>"Save this filter"</strong> input box appears at the bottom of the filter form</li>
            <li>Type a short, descriptive name (2–60 characters) and click <strong>Save</strong></li>
            <li>The filter is saved and a chip appears in the <strong>Saved Filters</strong> row above the filter panel</li>
        </ol>

        <h3>Applying a Saved Filter</h3>
        <p>Click any chip in the Saved Filters row to instantly reload the page with that filter combination applied. The chip highlights to show it is currently active.</p>

        <h3>Deleting a Saved Filter</h3>
        <p>Click the <strong>✕</strong> on a chip to delete that saved filter. You are prompted to confirm before it is removed. Saved filters are personal — you only see your own presets, not those of other staff members.</p>

        <table>
            <thead><tr><th>Rule</th><th>Detail</th></tr></thead>
            <tbody>
                <tr><td>Per-user</td><td>Each staff member has their own set of saved filters</td></tr>
                <tr><td>Maximum</td><td>Up to 20 saved filters per user</td></tr>
                <tr><td>Stored filters</td><td>Status, Priority, Assignee, Branch, Category, Date range, AI Urgency, Theme, Sentiment, Submission type, SLA overdue</td></tr>
            </tbody>
        </table>

        <div class="note">Saved filters capture the query parameters — not a snapshot of results. They will show different issues as new ones are submitted over time.</div>

        {{-- 5.2 Workflow --}}
        <h2 id="s5-workflow">Workflow &amp; Statuses</h2>
        <p>Every issue moves through a defined lifecycle. You cannot skip statuses — the allowed transitions are enforced by the system.</p>

        <table>
            <thead><tr><th>Status</th><th>Meaning</th><th>Who can set it</th><th>Allowed next statuses</th></tr></thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-new">New</span></td>
                    <td>Just submitted — not yet reviewed</td>
                    <td>Set automatically on submission</td>
                    <td>In Progress, Closed</td>
                </tr>
                <tr>
                    <td><span class="badge badge-progress">In Progress</span></td>
                    <td>Being actively worked on</td>
                    <td>Any staff, branch manager, admin</td>
                    <td>Resolved, Closed</td>
                </tr>
                <tr>
                    <td><span class="badge badge-resolved">Resolved</span></td>
                    <td>Action has been taken</td>
                    <td>Any staff, branch manager, admin</td>
                    <td>Closed, In Progress (re-open)</td>
                </tr>
                <tr>
                    <td><span class="badge badge-closed">Closed</span></td>
                    <td>Fully closed — no further action</td>
                    <td>Branch manager, admin, or the contact themselves</td>
                    <td>In Progress (admin only — to re-open)</td>
                </tr>
            </tbody>
        </table>

        <div class="note">Staff <strong>cannot</strong> close an issue — only branch managers, admins, or the parent/teacher themselves (via the portal) can. This ensures parents always have the final say.</div>

        <div class="tip">When an issue is closed, the parent's access code is automatically unlocked so they can submit a new issue.</div>

        {{-- 5.2b Still a Problem? --}}
        <h2 id="s5-reopen">"Still a Problem?" — Contact Reopen Flow</h2>
        <p>When a staff member moves an issue to <strong>Resolved</strong>, the parent receives an email notification with a prominent red <strong>"Still a problem?"</strong> button. If the parent feels the issue has not actually been resolved, they can click this button to automatically reopen it — without needing their access code.</p>

        <h3>How the Flow Works</h3>
        <ol class="steps">
            <li>Staff sets the issue status to <strong>Resolved</strong></li>
            <li>The system emails the contact an update that includes a <strong>"Still a problem?"</strong> button</li>
            <li>The contact clicks the button — they land on a dedicated confirmation page</li>
            <li>The issue is immediately reopened to <strong>In Progress</strong> (or back to <strong>New</strong> if it has no assignee)</li>
            <li>The assigned staff member and all admins receive a notification that the contact has indicated the problem is unresolved</li>
        </ol>

        <h3>Token Rules</h3>
        <table>
            <thead><tr><th>Rule</th><th>Detail</th></tr></thead>
            <tbody>
                <tr><td><strong>Single-use</strong></td><td>The reopen link can only be used once. After the issue is reopened, the link expires immediately.</td></tr>
                <tr><td><strong>Expires on close</strong></td><td>If the issue is subsequently closed by the school, any outstanding "Still a problem?" link becomes invalid. The page shown to the contact will explain the issue has been closed.</td></tr>
                <tr><td><strong>No access code required</strong></td><td>The contact does not need their access code — the secure token in the email link is sufficient.</td></tr>
            </tbody>
        </table>

        <h3>Reopen from the Status Page</h3>
        <p>In addition to the email button, a <strong>"Still a problem?"</strong> button also appears directly on the issue's public status page when the issue is in Resolved status and the contact is viewing it via their access code. The same single-use token logic applies.</p>

        <h3>Branch-Moved Issues Cannot Be Reopened</h3>
        <p>If an issue was auto-closed because the contact's branch was changed, the reopen button is <strong>not shown</strong>. Instead, the contact sees an amber notice explaining that their record was updated and they should contact the school for a new access code. This is by design — the issue belongs to the old branch's team and cannot be reinstated.</p>

        <div class="note">When an issue is reopened via the "Still a problem?" flow, a full activity log entry is created with the note "Parent indicated the problem is still not resolved." This is visible in both the issue detail timeline and the global Activity Log.</div>

        {{-- 5.3 Issue Detail --}}
        <h2 id="s5-detail">Issue Detail Page — Full Walkthrough</h2>
        <p>Click any issue row to open its detail page. The page is divided into two columns:</p>

        <h3>Left Column — Conversation</h3>
        <p>Shows the full message thread between the contact and your team:</p>
        <ul>
            <li><strong>Original submission</strong> — the parent's message, shown as the first bubble on the left.</li>
            <li><strong>Staff comments</strong> — internal notes are shown with a lock icon (🔒). They are never visible to the contact.</li>
            <li><strong>Replies to contact</strong> — shown with a different colour. The contact sees these on the portal and receives an email.</li>
            <li><strong>Contact replies</strong> — follow-up messages from the parent, shown on the left side.</li>
        </ul>

        <h3>Meta Bar (above conversation)</h3>
        <p>A single info line shows: <strong>Contact name &amp; role</strong> · <strong>Branch</strong> · <strong>SLA deadline</strong> · <strong>First reply time</strong> · <strong>Resolved at</strong> · <strong>Last activity</strong>. This gives you the key facts without scrolling down.</p>

        <h3>Right Column — Action Cards</h3>
        <p>Each card on the right controls a specific aspect of the issue:</p>

        <table>
            <thead><tr><th>Card</th><th>Purpose</th><th>Who can use it</th></tr></thead>
            <tbody>
                <tr><td><strong>Status</strong></td><td>Change the issue status. Dropdown shows only valid next transitions.</td><td>All roles (staff cannot set Closed)</td></tr>
                <tr><td><strong>Priority</strong></td><td>Set Low / Medium / High / Urgent manually.</td><td>All roles</td></tr>
                <tr><td><strong>Type</strong></td><td>Override the AI-set submission type: Complaint or Suggestion. Defaults to Complaint until AI analysis runs.</td><td>Admin, Branch Manager</td></tr>
                <tr><td><strong>Category</strong></td><td>Change the issue category. If AI suggests a different category, a one-click "Apply AI suggestion" button appears.</td><td>Admin, Branch Manager</td></tr>
                <tr><td><strong>Spam</strong></td><td>Mark the issue as spam with a reason. See Spam section below.</td><td>Admin, Branch Manager</td></tr>
                <tr><td><strong>Assigned To</strong></td><td>Shows current assignee. Click to reassign within the same branch.</td><td>Admin, Branch Manager</td></tr>
                <tr><td><strong>AI Analysis</strong></td><td>Shows urgency flag, detected themes, tone, sentiment, and suggested actions — in English and Urdu. Toggle language with the EN / اردو button.</td><td>Read-only for all roles</td></tr>
                <tr><td><strong>Private Note</strong></td><td>A personal scratchpad visible <em>only to you</em>. Other staff cannot see your note. Use it for reminders or context you don't want to share.</td><td>Staff and Branch Managers only</td></tr>
            </tbody>
        </table>

        <h3>Attachments</h3>
        <p>If the contact uploaded files with their submission, they appear below the original message. Click to download.</p>

        <h3>Activity Log</h3>
        <p>At the bottom of the right column, every action taken on the issue is logged with a timestamp and the name of who did it — status changes, assignments, category changes, spam flags, comments, and more.</p>

        {{-- 5.4 Assignment --}}
        <h2 id="s5-assign">Assignment</h2>
        <p>Issues are <strong>auto-assigned</strong> on submission based on category-to-staff mapping. You can manually reassign at any time:</p>
        <ol class="steps">
            <li>Open the issue</li>
            <li>Click <strong>Assign</strong> in the Assigned To card (right panel)</li>
            <li>Select a staff member — the dropdown shows only staff in the same branch as the issue</li>
            <li>The new assignee receives an email and an in-app bell notification</li>
        </ol>

        <div class="warn">Cross-branch assignment is blocked — you cannot assign an issue from Branch A to a staff member in Branch B. This is by design to maintain branch accountability.</div>

        <h3>Unassigning</h3>
        <p>Click <strong>Unassign</strong> in the Assigned To card to remove the current assignee. The issue returns to an unassigned state visible to branch managers and admins.</p>

        {{-- 5.5 Priority --}}
        <h2 id="s5-priority">Priority &amp; Urgency</h2>
        <p><strong>Priority</strong> is set manually by your team. <strong>AI Urgency</strong> is detected automatically and is separate — an issue can be Low priority but flagged as urgent by the AI (and vice versa).</p>
        <table>
            <thead><tr><th>Priority</th><th>Guideline</th></tr></thead>
            <tbody>
                <tr><td><span class="badge badge-high">Urgent</span></td><td>Safety, health emergency, immediate action required. Target response: same day.</td></tr>
                <tr><td><span class="badge badge-high">High</span></td><td>Significant disruption. Target response: within 24 hours.</td></tr>
                <tr><td><span class="badge badge-medium">Medium</span></td><td>Standard concern. Target response: within 3 business days.</td></tr>
                <tr><td><span class="badge badge-low">Low</span></td><td>Minor feedback or question. Address when time permits.</td></tr>
            </tbody>
        </table>

        <h3>AI Urgency Flags</h3>
        <table>
            <thead><tr><th>Flag</th><th>Meaning</th><th>What to do</th></tr></thead>
            <tbody>
                <tr><td>🔥 <strong>Escalate</strong></td><td>AI detected a serious or escalating tone — possible safety, health, or legal concern</td><td>Review immediately and escalate to senior management if needed</td></tr>
                <tr><td>👁 <strong>Monitor</strong></td><td>AI detected a concern that is not yet critical but could worsen</td><td>Respond within 24 hours; check back after replying</td></tr>
                <tr><td><em>None</em></td><td>Routine issue</td><td>Normal workflow applies</td></tr>
            </tbody>
        </table>

        {{-- 5.6 Submission Type & Positive Sentiment --}}
        <h2 id="s5-type">Submission Type &amp; Positive Sentiment</h2>
        <p>Every issue is automatically classified by AI into one of three types. Admins and branch managers can manually override the type from the issue detail page if the AI got it wrong.</p>
        <table>
            <thead><tr><th>Type</th><th>Definition</th><th>Example</th></tr></thead>
            <tbody>
                <tr><td><strong>Complaint</strong></td><td>Reports a problem, dissatisfaction, or negative experience. This is the default — all issues start as Complaint until AI reclassifies them.</td><td>"The bus was 40 minutes late every day this week."</td></tr>
                <tr><td><strong>Suggestion</strong></td><td>Constructive idea for improvement — the parent's intent is to help, not just report a problem. A blue <em>Suggestion</em> badge appears on the issue row and detail header.</td><td>"It would be great if homework was posted on the app the night before."</td></tr>
                <tr><td><strong>Compliment</strong></td><td>Positive feedback — the parent is expressing satisfaction or praise. If submitted through the issues form (rather than the dedicated compliment link), a green notice is shown on the issue detail.</td><td>"The new library is wonderful. Thank you for the investment in our children."</td></tr>
            </tbody>
        </table>

        <div class="note">A message that is <em>both</em> a complaint and a suggestion (e.g. "The lunch queue is too slow — you should add more staff") is classified as <strong>Suggestion</strong> because constructive intent takes priority. Admins can always override.</div>

        <h3>Positive Sentiment Notice</h3>
        <p>When the AI detects <strong>positive sentiment</strong> on an issue — meaning the contact appears to be expressing satisfaction rather than a concern — a dismissible green banner is shown at the top of the issue detail page. The banner text adapts based on the detected type:</p>
        <ul>
            <li>If classified as a <strong>Compliment</strong>: the banner explains this may be a compliment that was submitted through the issues form by mistake, and suggests acknowledging it or forwarding to the relevant team.</li>
            <li>If classified as a <strong>Suggestion</strong>: the banner notes the positive tone and encourages the team to act on the constructive feedback.</li>
        </ul>
        <p>Staff can dismiss the banner by clicking the × button. It does not affect the issue's processing in any way — it is informational only.</p>

        <div class="tip">If a contact clearly submitted a compliment through the wrong form, you can still process it normally — acknowledge it with a "Reply to Contact" message and close the issue. No harm is done.</div>

        {{-- 5.7 Comments --}}
        <h2 id="s5-comments">Comments &amp; Replies</h2>
        <p>Use the comment box at the bottom of the conversation to communicate about an issue. There are two types of message:</p>

        <table>
            <thead><tr><th>Type</th><th>Visible to</th><th>When to use</th></tr></thead>
            <tbody>
                <tr>
                    <td>🔒 <strong>Internal Note</strong></td>
                    <td>Staff and managers only — the contact never sees this</td>
                    <td>Team discussion, investigation notes, escalation decisions, anything not meant for the parent</td>
                </tr>
                <tr>
                    <td>💬 <strong>Reply to Contact</strong></td>
                    <td>The contact on the public portal + email notification sent to them</td>
                    <td>Giving the parent an update, asking for more information, confirming resolution</td>
                </tr>
            </tbody>
        </table>

        <div class="warn">Once a reply is sent to a contact it cannot be unsent — the parent will have already received the email. Draft carefully before clicking Send.</div>

        <p>When a staff member adds a comment, the assigned staff member receives a bell notification. When a <em>contact</em> replies from the portal, the assigned staff member and all admins are notified.</p>

        {{-- 5.8 Private Notes --}}
        <h2 id="s5-notes">Private Notes</h2>
        <p>A <strong>Private Note</strong> is a personal scratchpad attached to a specific issue. It is <strong>only visible to you</strong> — no other staff member, manager, or admin can read it.</p>

        <p>Use private notes for:</p>
        <ul>
            <li>Reminders to yourself ("Follow up Friday")</li>
            <li>Context you don't want in the shared conversation ("Parent is related to a board member")</li>
            <li>Draft replies before you're ready to send</li>
            <li>Notes from a phone call with the parent</li>
        </ul>

        <p>The Private Note card appears in the right column of the issue detail page. Click <strong>Save Note</strong> to save, or clear the field and save to delete your note.</p>

        <div class="note">Private notes are separate from Internal Notes (comments). An Internal Note is part of the shared conversation visible to all staff. A Private Note is yours alone.</div>

        {{-- 5.9 Spam --}}
        <h2 id="s5-spam">Spam — Rules &amp; Auto-Actions</h2>
        <p>The spam system protects your team from contacts who submit irrelevant, abusive, or repeated junk submissions.</p>

        <h3>When to Mark an Issue as Spam</h3>
        <ul>
            <li>The content is completely irrelevant (e.g. advertisements, gibberish)</li>
            <li>The submission is abusive or offensive</li>
            <li>The same contact is submitting duplicate or near-identical issues repeatedly to harass staff</li>
            <li>The contact is testing the system with fake submissions</li>
        </ul>

        <h3>How to Mark as Spam</h3>
        <ol class="steps">
            <li>Open the issue detail page</li>
            <li>In the <strong>Spam</strong> card (right panel), click <strong>Mark as Spam</strong></li>
            <li>Enter a reason (required) — this is recorded in the activity log for audit purposes</li>
            <li>Click Confirm</li>
        </ol>

        <h3>What Happens Automatically</h3>
        <table>
            <thead><tr><th>Action</th><th>Why</th></tr></thead>
            <tbody>
                <tr><td>Issue is <strong>un-assigned</strong></td><td>Frees the staff member — no action required on spam</td></tr>
                <tr><td>Issue is <strong>auto-closed</strong> silently</td><td>Removes it from the open queue. No email is sent to the contact.</td></tr>
                <tr><td>Issue is <strong>hidden</strong> from the main list</td><td>Spam issues only appear when you select the Spam view</td></tr>
                <tr><td><strong>No CSAT survey</strong> is sent</td><td>Spam closures do not trigger satisfaction surveys</td></tr>
            </tbody>
        </table>

        <h3>Auto-Revoke After 5 Spams</h3>
        <p>If the same contact reaches <strong>5 confirmed spam submissions</strong>, their access code is <strong>automatically deleted</strong>. They can no longer submit issues until an admin manually generates a new code for them. This count resets if an admin pardons the contact.</p>

        <div class="warn">Spam flagging should be used for genuinely bad-faith submissions. Do not mark issues as spam simply because the concern seems minor — use Low priority instead.</div>

        <h3>Removing a Spam Flag</h3>
        <p>If an issue was incorrectly flagged, open it from the Spam view → click <strong>Remove Spam Flag</strong>. The issue returns to the normal list. You will need to manually reassign it and update the status if needed. The access code is <em>not</em> automatically restored — generate a new one if required.</p>

        {{-- 5.10 AI Analysis --}}
        <h2 id="s5-ai">AI Analysis</h2>
        <p>Every issue submitted through the portal is automatically analysed by AI within seconds of submission. Results appear in the <strong>AI Analysis</strong> card on the issue detail page.</p>

        <table>
            <thead><tr><th>Field</th><th>What it tells you</th></tr></thead>
            <tbody>
                <tr><td><strong>Urgency Flag</strong></td><td>Escalate / Monitor / None — how seriously the AI rates the issue</td></tr>
                <tr><td><strong>Themes</strong></td><td>Key topics detected (e.g. "bus delays", "teacher conduct", "homework volume")</td></tr>
                <tr><td><strong>Tone</strong></td><td>How the parent expressed the issue (e.g. Frustrated, Calm, Distressed)</td></tr>
                <tr><td><strong>Sentiment</strong></td><td>Positive / Neutral / Negative — overall emotional direction of the message</td></tr>
                <tr><td><strong>Submission Type</strong></td><td>Complaint / Suggestion / Compliment — AI classification (overrideable by admin)</td></tr>
                <tr><td><strong>Suggested Category</strong></td><td>If AI thinks the issue belongs in a different category, a button appears to apply the suggestion with one click</td></tr>
                <tr><td><strong>Suggested Actions</strong></td><td>2–4 specific next steps for the responding staff member, written in plain English and Urdu</td></tr>
                <tr><td><strong>Acknowledgment</strong></td><td>A ready-to-send empathetic opening sentence staff can use when replying to the parent</td></tr>
            </tbody>
        </table>

        <p>Use the <strong>EN / اردو</strong> toggle on the AI Analysis card to switch between English and Urdu for the suggested actions and acknowledgment.</p>

        <div class="tip">The AI Suggested Actions are a starting point — always review them before using. They are generated from the parent's message and may not have full context of your school's policies.</div>

        {{-- 5.11 SLA --}}
        <h2 id="s5-sla">SLA &amp; Overdue Issues</h2>
        <p>SLA (Service Level Agreement) deadlines are set automatically when an issue is submitted, based on its priority:</p>
        <table>
            <thead><tr><th>Priority</th><th>SLA Target</th></tr></thead>
            <tbody>
                <tr><td>Urgent</td><td>4 hours</td></tr>
                <tr><td>High</td><td>24 hours</td></tr>
                <tr><td>Medium</td><td>72 hours (3 days)</td></tr>
                <tr><td>Low</td><td>168 hours (7 days)</td></tr>
            </tbody>
        </table>
        <p>An issue becomes <strong>Overdue</strong> when its SLA deadline passes and it is still open (not resolved or closed). Overdue issues are surfaced via the red <strong>Overdue SLA</strong> badge in the Issues table header — click it to filter to only those issues. They also appear on the Reports page.</p>

        {{-- 5.12 Activity Log --}}
        <h2 id="s5-activity">Activity Log</h2>
        <p>Every action taken on an issue is recorded in the <strong>Activity Log</strong> at the bottom of the right panel on the issue detail page. This provides a full audit trail.</p>
        <p>Logged events include:</p>
        <ul>
            <li>Status changes (from → to, who changed it)</li>
            <li>Priority changes</li>
            <li>Category changes (old → new)</li>
            <li>Submission type overrides</li>
            <li>Assignment and unassignment (who assigned to whom)</li>
            <li>Spam marked / spam cleared (with reason)</li>
            <li>Access code auto-revoked</li>
            <li>Comments added (internal or contact reply)</li>
            <li>Contact replies from the portal</li>
            <li>Contact branch changes (<em>contact_moved</em> type — see below)</li>
        </ul>

        <h3>Contact Moved Activity</h3>
        <p>When a contact's branch is changed (individually or via bulk action), a <strong>contact_moved</strong> activity entry is logged on every issue linked to that contact — not just the ones that were auto-closed. This ensures you have a full record of the move across the contact's entire history.</p>
        <table>
            <thead><tr><th>Where shown</th><th>What it says</th></tr></thead>
            <tbody>
                <tr><td>Issue detail timeline</td><td>A transfer icon and the text "Contact [Name] moved from [Old Branch] to [New Branch]"</td></tr>
                <tr><td>Global Activity Log page</td><td>Same description, with a dedicated "Contact moved" badge. Filterable by selecting "Contact moved" in the Action dropdown.</td></tr>
            </tbody>
        </table>

        <h3>Activity Notes on Auto-closed Issues</h3>
        <p>When a status change is triggered automatically by the system (such as auto-closing due to a branch change), the activity entry includes a <strong>note</strong> field explaining why. For example: <em>"Auto-closed — contact moved to TABUK"</em>. This note is displayed inline below the status change description in both the issue timeline and the global Activity Log — you do not need to open a detail view to understand the reason.</p>

        <h3>Global Activity Log Page</h3>
        <p>In addition to the per-issue timeline, there is a dedicated <strong>Activity Log</strong> page accessible from the sidebar. It shows activity across all issues in your scope, with filters for:</p>
        <ul>
            <li><strong>Action type</strong> — Status changed, Assigned, Priority changed, Commented, Contact moved, Message deleted</li>
            <li><strong>Actor</strong> — Who performed the action</li>
            <li><strong>Branch</strong> — (Admin only) filter to a specific branch</li>
            <li><strong>Date range</strong> — From and To dates</li>
            <li><strong>Issue ID</strong> — Search by tracking ID</li>
        </ul>

        <div class="note">The activity log cannot be edited or deleted. It is permanent for compliance and accountability purposes.</div>
    </div>

    {{-- ─── Section 6: Reports ───────────────────────────────────────────── --}}
    <div class="section" id="s6">
        <span class="section-num">06</span>
        <h1>Reports &amp; Analytics</h1>
        <p>Go to <strong>Reports</strong> in the sidebar. Use the date range and grain (daily / weekly / monthly) filters at the top.</p>

        <table>
            <thead><tr><th>Report</th><th>What it shows</th></tr></thead>
            <tbody>
                <tr><td>Issue Volume Trend</td><td>How many issues were submitted over the period</td></tr>
                <tr><td>Resolution Rate</td><td>% of issues resolved within your SLA target</td></tr>
                <tr><td>Branch Breakdown</td><td>Volume and resolution rate per branch</td></tr>
                <tr><td>Staff Performance</td><td>Issues resolved per staff member</td></tr>
                <tr><td>Category Breakdown</td><td>Which categories generate the most issues</td></tr>
                <tr><td>CSAT Scores</td><td>Parent satisfaction ratings, per branch and overall</td></tr>
                <tr><td>Sentiment Trend</td><td>AI-measured parent sentiment over time (positive/neutral/negative)</td></tr>
            </tbody>
        </table>

        <div class="note">Use <strong>CSV Export</strong> (Growth plan and above) to download raw issue data for your own analysis.</div>
    </div>

    {{-- ─── Section 7: Broadcasts ────────────────────────────────────────── --}}
    <div class="section" id="s7">
        <span class="section-num">07</span>
        <h1>Broadcasts (Bulk Messaging)</h1>
        <p>Send a message to all or a filtered subset of your roster contacts via email or SMS.</p>

        <ol class="steps">
            <li>Go to <strong>Bulk Notifications → Send Broadcast</strong></li>
            <li>Choose your <strong>audience</strong> — all contacts, by branch, by role (parents/teachers), or by category</li>
            <li>Select the <strong>channel</strong> — Email, SMS, or both</li>
            <li>Write your <strong>subject</strong> and <strong>message body</strong></li>
            <li>Preview the recipient count, then click <strong>Send Broadcast</strong></li>
        </ol>

        <div class="warn">Broadcasts cannot be recalled once sent. Always double-check the recipient count before confirming.</div>

        <p>View delivery history and open rates in <strong>Bulk Notifications → Broadcast Logs</strong>.</p>
    </div>

    {{-- ─── Section 8: AI Chatbot ────────────────────────────────────────── --}}
    <div class="section" id="s8">
        <span class="section-num">08</span>
        <h1>AI Chatbot</h1>
        <p>The chatbot allows parents to ask questions about school policies, timings, fees, and procedures — and get instant answers — without needing to contact staff.</p>

        <h2>How it Works</h2>
        <p>The chatbot answers questions using two sources:</p>
        <ul>
            <li><strong>FAQs</strong> — answers you've written manually under <strong>Document Library → FAQs</strong></li>
            <li><strong>Uploaded Documents</strong> — PDFs and Word files you've uploaded to the Document Library (e.g. school handbook, fee schedule)</li>
        </ul>

        <div class="tip">Add FAQs first. They are the most reliable source because you write the answer yourself. Documents are used when no FAQ matches.</div>

        <h2>Enabling the Chatbot</h2>
        <ol class="steps">
            <li>Go to <strong>School Settings → Features</strong></li>
            <li>Toggle <strong>Enable Public Chatbot</strong> to On</li>
            <li>Save — the chatbot link appears on the parent portal immediately</li>
        </ol>

        <h2>Adding FAQs</h2>
        <ol class="steps">
            <li>Go to <strong>Document Library → FAQs</strong> → <strong>Add FAQ</strong></li>
            <li>Enter the question as a parent would ask it (e.g. "What time does school start?")</li>
            <li>Enter a clear, complete answer</li>
            <li>Set status to <strong>Published</strong></li>
        </ol>

        <h2>Uploading Documents</h2>
        <ol class="steps">
            <li>Go to <strong>Document Library → All Documents</strong> → <strong>Upload</strong></li>
            <li>Select a PDF or Word document (max 20 MB)</li>
            <li>Assign a category, title, and document type</li>
            <li>Set the access option for the document (see below)</li>
            <li>The system processes the document and makes it searchable (takes 1–2 minutes)</li>
        </ol>

        <h3>Document Types &amp; Access Settings</h3>
        <p>The access option shown when uploading depends on the document type:</p>
        <table>
            <thead><tr><th>Document Type</th><th>Access Option Shown</th><th>Default</th></tr></thead>
            <tbody>
                <tr>
                    <td><strong>Form</strong>, <strong>Handbook</strong></td>
                    <td>🟢 <strong>Allow parents to download</strong> — enables a download button on the parent portal so parents can save the document</td>
                    <td>Enabled</td>
                </tr>
                <tr>
                    <td><strong>Policy</strong>, <strong>Schedule</strong>, <strong>Guideline</strong>, <strong>Announcement</strong>, <strong>Other</strong></td>
                    <td>🔵 <strong>Include in chatbot responses</strong> — the document's content is indexed and used by the AI chatbot to answer parent questions</td>
                    <td>Enabled</td>
                </tr>
            </tbody>
        </table>

        <div class="note">Forms and handbooks are not included in chatbot search results — they are too form-specific for Q&amp;A. Policy and informational documents are not made publicly downloadable — they feed the chatbot instead. You can override either default when uploading.</div>

        <h2>Monitoring Usage</h2>
        <p>Go to <strong>Chatbot Logs</strong> to see every question asked, the answer given, and the confidence score. Low-confidence answers indicate you should add an FAQ or upload a relevant document.</p>
    </div>

    {{-- ─── Section 9: School Settings ──────────────────────────────────── --}}
    <div class="section" id="s9">
        <span class="section-num">09</span>
        <h1>School Settings</h1>
        <p>Go to <strong>Settings → School Settings</strong>. Settings are grouped into tabs:</p>

        <table>
            <thead><tr><th>Tab</th><th>What you configure</th></tr></thead>
            <tbody>
                <tr><td>General</td><td>School name, logo, welcome message, contact info, primary colour</td></tr>
                <tr><td>Portal</td><td>Enable/disable new submissions, anonymous submissions, chatbot</td></tr>
                <tr><td>Email (SMTP)</td><td>Custom "From" email address and SMTP credentials (Growth+ plans)</td></tr>
                <tr><td>WhatsApp</td><td>Connect your WhatsApp Business account for notifications (Pro+ plans)</td></tr>
                <tr><td>SMS</td><td>Twilio credentials for SMS access codes and broadcasts (Growth+ plans)</td></tr>
                <tr><td>Thank You Message</td><td>Message shown to parents after they submit an issue</td></tr>
            </tbody>
        </table>

        <div class="warn">Disabling "Allow new submissions" immediately prevents parents from submitting issues. Use this only during maintenance or school holidays.</div>
    </div>

    {{-- ─── Section 10: Parent Portal ────────────────────────────────────── --}}
    <div class="section" id="s10">
        <span class="section-num">10</span>
        <h1>The Parent &amp; Teacher Portal</h1>
        <p>The public portal is the face of Schoolytics for parents and teachers. They access it at your school's subdomain (e.g. <strong>yourschool.schoolytics.app</strong>).</p>

        <h2>What Parents Can Do</h2>
        <ul>
            <li><strong>Submit an issue</strong> — enter their access code, choose a category, describe the issue, and optionally attach files</li>
            <li><strong>Track their issue</strong> — enter their tracking ID or access code to see the current status and any staff replies</li>
            <li><strong>Reply to staff</strong> — add follow-up messages directly from the status page</li>
            <li><strong>Close their issue</strong> — if they are satisfied with the resolution</li>
            <li><strong>Submit anonymously</strong> — no access code required (if enabled by admin)</li>
            <li><strong>Submit a compliment</strong> — positive feedback for the school</li>
            <li><strong>Ask the AI chatbot</strong> — get instant answers to common questions (if enabled)</li>
        </ul>

        <h2>After Submitting an Issue</h2>
        <p>The parent receives a <strong>confirmation email</strong> with their tracking ID and a direct link to the status page. They can bookmark this link to check back anytime without needing their code again.</p>

        <h2>Status Page — What Parents See</h2>
        <p>The status page shows the current status, the full conversation thread, and AI-generated acknowledgment text. The actions available depend on the issue's current state:</p>
        <table>
            <thead><tr><th>Issue state</th><th>What the parent sees</th></tr></thead>
            <tbody>
                <tr><td>New or In Progress</td><td>A reply text box to send follow-up messages, and a "Close issue" link at the bottom if they consider it resolved</td></tr>
                <tr><td>Resolved</td><td>A <strong>"Still a problem?"</strong> button — clicking it reopens the issue and notifies the team immediately (no access code needed)</td></tr>
                <tr><td>Closed (by school or contact)</td><td>A prompt to reopen the issue (requires their access code) if they have a new concern</td></tr>
                <tr><td>Closed due to branch change</td><td>An amber notice explaining their record was updated and they should contact the school for a new access code — no reopen option is available</td></tr>
            </tbody>
        </table>

        <h2>CSAT Survey</h2>
        <p>When an issue is closed, the parent automatically receives a <strong>satisfaction survey</strong> (1–5 stars) via email. Their response feeds into your school's CSAT score on the dashboard.</p>

        <div class="note">Anonymous issues do not receive CSAT surveys (no email address is captured).</div>
    </div>

    {{-- ─── Section 11: Roles Quick Reference ───────────────────────────── --}}
    <div class="section" id="s11">
        <span class="section-num">11</span>
        <h1>Roles &amp; Permissions Quick Reference</h1>

        <table>
            <thead>
                <tr>
                    <th>Action</th>
                    <th style="text-align:center">Admin</th>
                    <th style="text-align:center">Branch Mgr</th>
                    <th style="text-align:center">Staff</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>View all issues</td><td style="text-align:center">✅</td><td style="text-align:center">Branch only</td><td style="text-align:center">Assigned only</td></tr>
                <tr><td>View anonymous issues</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td></tr>
                <tr><td>Assign issues</td><td style="text-align:center">✅</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td></tr>
                <tr><td>Change status → Resolved</td><td style="text-align:center">✅</td><td style="text-align:center">✅</td><td style="text-align:center">✅</td></tr>
                <tr><td>Change status → Closed</td><td style="text-align:center">✅</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td></tr>
                <tr><td>Change category</td><td style="text-align:center">✅</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td></tr>
                <tr><td>Override submission type</td><td style="text-align:center">✅</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td></tr>
                <tr><td>Mark / unmark spam</td><td style="text-align:center">✅</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td></tr>
                <tr><td>Add internal note</td><td style="text-align:center">✅</td><td style="text-align:center">✅</td><td style="text-align:center">✅</td></tr>
                <tr><td>Reply to contact</td><td style="text-align:center">✅</td><td style="text-align:center">✅</td><td style="text-align:center">✅</td></tr>
                <tr><td>Private note (own only)</td><td style="text-align:center">❌</td><td style="text-align:center">✅</td><td style="text-align:center">✅</td></tr>
                <tr><td>Manage users</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td></tr>
                <tr><td>Manage contacts</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td></tr>
                <tr><td>View reports</td><td style="text-align:center">✅</td><td style="text-align:center">Branch only</td><td style="text-align:center">❌</td></tr>
                <tr><td>School settings</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td></tr>
                <tr><td>Send broadcasts</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td></tr>
            </tbody>
        </table>
    </div>

    {{-- ─── Section 12: FAQ ──────────────────────────────────────────────── --}}
    <div class="section" id="s12">
        <span class="section-num">12</span>
        <h1>Frequently Asked Questions</h1>

        <h3>A parent says they never received their access code email — what do I do?</h3>
        <p>Go to <strong>Contacts → Roster Contacts</strong> → find the contact → click <strong>Send Code</strong>. Check that their email address is correct. Also ask them to check their spam/junk folder. Alternatively, copy the code directly from the contact page and share it via WhatsApp or SMS.</p>

        <h3>A parent submitted an issue but wants to submit another — they say it won't let them.</h3>
        <p>Schoolytics allows one open issue per contact at a time. The parent's previous issue must be closed before they can submit a new one. Either close the old issue from the admin panel, or ask the parent to close it themselves from the portal.</p>

        <h3>How do I stop parents from submitting issues temporarily (e.g. during exams)?</h3>
        <p>Go to <strong>School Settings → Portal</strong> → toggle off <strong>"Allow new submissions"</strong>. The portal will show a "submissions currently closed" message.</p>

        <h3>Can I change which staff member gets auto-assigned a category?</h3>
        <p>Yes. Go to <strong>Management → Users</strong> → edit the staff member → scroll to <strong>Assigned Categories</strong>. Remove the category from the old staff member and add it to the new one.</p>

        <h3>The chatbot is giving wrong answers — what do I do?</h3>
        <p>Add an FAQ that directly answers that question (FAQs take priority over document content). Go to <strong>Document Library → FAQs → Add FAQ</strong>. Make the question match how parents actually phrase it.</p>

        <h3>How do I update the school's contact details shown on the parent portal?</h3>
        <p>Go to <strong>School Settings → General</strong> → update Email, Phone, and Address → Save.</p>

        <h3>A staff member has left — how do I remove them?</h3>
        <p>Go to <strong>Management → Users</strong> → find the user → click <strong>Disable</strong>. Their account is deactivated and they can no longer log in. Their name remains on historical issues for audit purposes.</p>

        <h3>Where do I find the tracking ID to give to a parent?</h3>
        <p>Open the issue in the admin panel. The 8-character tracking ID (e.g. <code>ABCD1234</code>) is displayed at the top of the issue detail page. The parent can also find it in their submission confirmation email.</p>

        <h3>A parent clicked "Still a problem?" but says the page showed an error.</h3>
        <p>The reopen link is single-use and expires when the issue is closed. If the issue was closed by the school after being resolved, the link is no longer valid. In that case, you can manually reopen the issue from the admin panel by changing the status back to In Progress, or ask the parent to use their access code on the portal to submit a new issue.</p>

        <h3>We moved a contact to a different branch by mistake — how do we fix it?</h3>
        <p>Go to <strong>Contacts → Edit Contact</strong> → change the branch back to the original. The auto-closed issues will not be automatically reopened, but you can manually reopen each one from the admin panel if needed. Generate a new access code for the contact so they can submit issues again.</p>

        <h3>A parent's issue was auto-closed after a branch change — can they still see it on the portal?</h3>
        <p>Yes. The parent can still view the issue using its tracking ID or their access code history. The status page will show an amber notice explaining that their record was updated and they should request a new access code for any new concerns. The "Still a problem?" reopen option is not available for branch-change-closed issues.</p>

        <h3>The AI classified an issue as a Compliment — what should I do?</h3>
        <p>A green banner on the issue detail will alert you. You can acknowledge the parent with a "Reply to Contact" message thanking them, then close the issue. Alternatively, use the <strong>Type</strong> card on the right panel to reclassify it if the AI got it wrong.</p>

        <h3>What does the "Branch Moved" quick filter on the Issues page show?</h3>
        <p>It shows all issues — including closed ones — where the contact's branch was changed and the issue was auto-closed as part of that branch change. This is useful for reviewing how many issues were affected by a bulk branch move or for auditing a specific contact's history after a move.</p>
    </div>

    <div class="manual-footer">
        Schoolytics User Manual · {{ now()->format('F Y') }} · For support, contact your system administrator.
    </div>

</div>
</body>
</html>
