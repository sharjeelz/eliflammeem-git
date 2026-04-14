# Schoolytics — Feature Reference

Multi-tenant school issue-tracking and communication platform. Schools are isolated by subdomain (e.g., `schoola.lvh.me`). Superadmin lives at `central.lvh.me/nova`.

---

## Architecture

- **Multi-tenancy**: Stancl Tenancy v3, row-level isolation (single DB), subdomain routing
- **Auth**: Two guards — `central` (CentralUser → Nova) and `web` (User → tenant admin panel)
- **Roles**: Spatie Permission with team scoping — `admin`, `branch_manager`, `staff`
- **Queue**: Laravel queues for AI analysis, email, notifications
- **AI**: Python microservice at `AI_SERVICE_URL` (default `http://127.0.0.1:9000`) + Anthropic Claude API fallback
- **Storage**: Local disk for attachments; served via signed URLs scoped to tenant domain
- **Central DB**: `central_users`, `tenants`, `domains`, `leads`, `app_settings`
- **Tenant DB tables**: All have `tenant_id` + use `BelongsToTenant` trait

---

## Core Features

### Issue Tracking
- Public portal: parents/teachers authenticate via **access codes** (no passwords)
- One open issue per contact enforced at submission time
- Issue states: `new → in_progress → resolved → closed`
- Priority: `low | medium | high | urgent`
- Attachments: up to 5 per submission; stored on disk, served via tenant-scoped URLs
- Role-based visibility: admin = all issues, branch_manager = branch issues, staff = assigned only
- Staff **cannot** set status to `closed` (only admin, branch_manager, or contact via public portal)
- Access code reset to `null` on issue close so contact can submit again

### Admin Panel — Issues
- Issues index: server-side filters (search, status, priority, assigned user, branch, urgency, date)
- Issue detail: chat-style conversation (right = you, left = others), activity log timeline, sidebar actions
- AI urgency badge on issue rows: fire = escalate, eye = monitor
- Internal vs external messages (`is_internal` flag) — contacts only see external messages
- CSV export respects role-based scoping

### Admin Panel — Users
- Create / edit / soft-delete / restore staff accounts
- Role assignment: admin, branch_manager, staff
- Branch assignment (many-to-many via `branch_user` pivot)
- Category assignment (many-to-many via `issue_category_user` pivot)
- `PermanentlyDeleteUser` Nova action: hard-deletes, anonymises messages, nulls assignments

### Admin Panel — Roster Contacts
- Create / edit / delete contacts
- Import via CSV/Excel: smart upsert (match by external_id → email → phone), optional deactivation of missing contacts
- Bulk actions: generate codes, revoke codes, send codes
- Access code generation + send via email or SMS (Twilio)
- Deactivated contacts shown dimmed with badge; guarded from code send/issue submit

### Admin Panel — Branches & Categories
- Create / edit / delete branches; branch-level scoping for users, contacts, issues
- 10 default categories seeded on every new tenant
- Staff assigned to categories for routing visibility

### Default Issue Categories
Transport, Academics, Facilities, Behavior, Food & Dining, Communication, Health & Safety, Fees & Payments, Technology Issues, General Complaints

---

## AI Features

### Issue Analysis
- Triggered via `IssueCreated` event → `PerformAiAnalysis` listener → `AnalyzeIssueSentiment` job
- Primary: Python microservice `POST /ai/analyze/` with title + description + category
- Fallback: Anthropic Claude API (model configurable via `OPENAI_CHAT_MODEL`)
- Stores in `issue_ai_analyses`: sentiment, urgency_flag, themes[], tone, acknowledgment, analysis_type
- Urgency flags: `escalate` | `monitor` | `routine`
- `php artisan analyze:all-issues` — backfill command

### Trend Detection
- `DetectIssueTrends` job dispatched with 30s delay after every AI analysis
- Counts themes across last 7 days; alerts admins via `IssueTrendDetectedNotification`
- 24-hour dedup cache per theme key `trend:{tenantId}:{md5(theme)}`

### Dashboard AI Widgets
- "Needs Attention" card: issues with urgency_flag = escalate (admin/branch_manager only)
- "Hot Topics" card: top themes from AI analyses (admin/branch_manager only)

---

## Public Chatbot (RAG)

- `GET /ask` — chatbot page (school-branded, no auth required)
- `POST /ask` — answers question using vector search + Claude API; throttle: 20/min
- `ChatbotService::answerQuestion()`: extracts metadata filters → vector search → Claude prompt
- `VectorSearchService::searchSimilarChunks()`: pgvector similarity search with metadata filtering
- Logs every interaction in `chatbot_logs`: question, answer, confidence, sources, response_ms, used_fallback, metadata_filters
- Disabled state: shows `chatbot_disabled.blade.php` when `chatbot_enabled = false` in school settings
- Controlled by `chatbot_enabled` school setting

### Chatbot Logs (Admin)
- `GET /admin/chatbot/logs` — paginated viewer with filters (keyword, date range, confidence, no-answer)
- KPI cards: today's questions, last 7 days, no-answer rate (30d), avg confidence (30d)
- GDPR data retention notice: IP anonymised after 30 days, full records deleted after 90 days, nightly prune at 02:00

---

## Document Library (Chatbot Knowledge Base)

- Upload PDF/Word → text extracted → chunked → embedded via OpenAI → stored with pgvector
- Documents feed the RAG chatbot with school-specific knowledge
- Managed from admin panel under Documents section

---

## CSAT Surveys

- Auto-sent when issue is closed (any actor)
- `CsatResponse` row created with unique token; `CsatSurveyMail` queued to contact's email
- Contact clicks rating link `/{tenant}/csat/{token}/{rating}` (1–5 stars) — one-time only
- Results shown in Reports page with per-branch breakdown

---

## Reports & Analytics

- `GET /admin/reports` — role-scoped (same as dashboard)
- Query params: `from`, `to`, `grain` (day/week/month) — defaults: last 30 days, day grain
- KPI cards, trend chart (ECharts stacked area), SLA card
- Tables: branch breakdown, staff performance (resolved issues), category breakdown, CSAT section

---

## Broadcasting

- Send emails/SMS to all contacts or filtered segments
- Powered by Twilio (SMS) and configured SMTP (email)

---

## WhatsApp Integration

- Per-tenant WhatsApp Business API credentials stored in school settings
- Webhook: `GET /whatsapp/webhook/{webhookId}` (verify) + `POST /whatsapp/webhook/{webhookId}` (events)
- `webhookId` is a 40-char random secret in `school.settings->whatsapp_webhook_id` — not the tenant UUID
- Auto-generated on first visit to WhatsApp settings tab; read-only URL shown with copy button

---

## School Settings

- General: school name, logo, contact info
- Issue settings: allow new issues, allow anonymous issues
- Chatbot: enable/disable, greeting message
- SMTP: custom outbound email
- WhatsApp: API credentials + webhook URL
- SMS: Twilio credentials

---

## Onboarding Wizard

- New tenants: `welcome → profile → contract → completed` (tracked via `tenants.registration_status`)
- **Contract / T&C page** (`/admin/contract`): public route (no auth required); shows school details + "Electronically accepted by" admin name
- On acceptance: stamps `terms_accepted_at`, `terms_accepted_ip`, `terms_accepted_name`, `contract_file_url`; advances status to `completed`
- Dashboard banner for admins without accepted T&C → links to `/admin/terms` standalone page

---

## Subscription Plans

Column `tenants.plan` (enum: starter|growth|pro|enterprise). Enforced via `PlanService` + `RequiresPlanFeature` middleware. Assigned via Nova.

| Feature | Starter | Growth | Pro | Enterprise |
|---|---|---|---|---|
| Branches | 1 | 3 | 10 | Unlimited |
| Staff users | 5 | 15 | 50 | Unlimited |
| Roster contacts | 100 | 500 | 2 000 | Unlimited |
| AI analysis | ❌ | ✅ | ✅ | ✅ |
| Chatbot | ❌ | 50/day | 200/day | Unlimited |
| Broadcasting | ❌ | ✅ | ✅ | ✅ |
| WhatsApp | ❌ | ❌ | ✅ | ✅ |
| Document library | ❌ | ✅ | ✅ | ✅ |
| CSV export | ❌ | ✅ | ✅ | ✅ |
| Custom SMTP | ❌ | ✅ | ✅ | ✅ |
| Full Reports | Basic | Full | Full | Full |

Stripe/self-serve billing is a future addition — enforcement layer (`PlanService`) is already in place.

---

## Nova (Superadmin)

Central domain only (`central.lvh.me/nova`), `central` guard.

### Resources
- **Tenant**: provision, manage, view contract acceptance dates
- **Lead**: contact form submissions from marketing site; ApproveLead action
- **AppSetting**: editable key/value for marketing pages (terms/privacy content)
- **AiUsageLog**: per-tenant AI API cost tracking

### Actions on Tenant
- `ProvisionTenant`: creates tenant + domain + school + branch + roles + 10 categories + first admin; sends `TenantProvisionedMail` with password-reset link
- `ResendWelcomeEmail`: generates fresh reset token, re-queues welcome email
- `ResetSchoolData`: wipes issues/contacts/codes/CSAT; keeps categories/users/branches; requires typing `RESET`
- `GenerateDemoData`: seeds branch managers, staff, contacts with open issues per branch
- `MarkTermsAccepted`: stamps all T&C fields, advances stuck registration statuses
- `ResetTermsAcceptance`: clears all T&C fields, sets status back to `profile_complete` (destructive)

### Actions on User (central)
- `PermanentlyDeleteUser`: hard-deletes, anonymises messages, nulls assignments; requires typing `DELETE`

---

## Marketing Site (Central Domain)

- `/` — landing page with pricing plans
- `/terms` — Terms & Conditions (content from `app_settings`, editable in Nova)
- `/privacy` — Privacy Policy (content from `app_settings`, editable in Nova)
- `/contact` — Contact form with Cloudflare Turnstile CAPTCHA
  - Fields: name, email, phone, school name, city, package interest, message
  - Honeypot field (`website`) for bot detection
  - Phone: digits, +, -, spaces, parentheses only
  - Leads saved to `leads` table; `LeadSubmittedMail` queued to superadmin
  - Turnstile skipped in `local` and `testing` environments

---

## Security Hardening

| Area | Implementation |
|---|---|
| Provisioning | Password-reset link sent; plaintext password never leaves server |
| WhatsApp webhook | Opaque 40-char secret in URL, not the tenant UUID |
| Rate limiting | Public submit: 10/min; contact form: 5/min; resend code: 5/min; chatbot: 20/min |
| Attachment limit | Max 5 per submission |
| Staff close restriction | Staff cannot set `closed`; enforced in policy + view |
| Cross-branch assignment | Blocked server-side in `WorkflowController::assign()` |
| Role scope enforcement | `Issue::scopeVisibleTo()` applied at query level |
| Honeypot | Contact form `website` field; non-empty = reject |
| Turnstile CAPTCHA | On contact form (production only) |
| AI job reliability | `$tries=3`, `$timeout=15`, `$backoff=30`, try/catch |

---

## Email Notifications

All mail uses `Mail::to($addr)->queue(new Mailable(...))` — never `Mail::send()`.

| Trigger | Mailable | Recipient |
|---|---|---|
| Tenant provisioned | `TenantProvisionedMail` | New admin (includes password-reset link) |
| Lead approved | `LeadApprovedMail` | Lead email |
| Lead submitted | `LeadSubmittedMail` | Superadmin |
| Issue assigned | `IssueAssignedMail` | Assigned staff |
| Status → resolved/closed | `IssueStatusChangedMail` | RosterContact |
| Comment added | `IssueCommentedMail` | Assigned staff |
| Issue closed | `CsatSurveyMail` | RosterContact |
| Trend detected | `IssueTrendDetectedNotification` | All tenant admins |
| Daily digest | `DailyTrendDigestMail` | All tenant admins |

---

## Scheduled Tasks

| Schedule | Task |
|---|---|
| Daily 02:00 | Purge chatbot logs > 90 days; anonymise IPs > 30 days |
| Daily | `DailyTrendDigestMail` to admins |

---

## Key File Locations

```
app/
  Http/
    Controllers/
      Admin/           — tenant admin controllers
      Central/         — marketing site (ContactController)
      Public/          — public portal (IssueSubmit, Chatbot, etc.)
      WhatsAppWebhookController.php
    Middleware/
      RequiresPlanFeature.php
  Jobs/
    AnalyzeIssueSentiment.php
    DetectIssueTrends.php
  Listeners/
    PerformAiAnalysis.php
  Models/
    Tenant.php, School.php, Branch.php
    User.php, RosterContact.php, AccessCode.php
    Issue.php, IssueMessage.php, IssueAiAnalysis.php
    ChatbotLog.php, AiUsageLog.php
    AppSetting.php, Lead.php
  Nova/
    Tenant.php, Lead.php, AppSetting.php, AiUsageLog.php
    Actions/           — Nova action classes
  Services/
    PlanService.php, ChatbotService.php
    VectorSearchService.php, AiAnalysisService.php
config/
  plans.php            — plan limits and feature flags
  services.php         — AI_SERVICE_URL, ANTHROPIC_API_KEY, OpenAI, Turnstile
database/
  migrations/tenant/   — tenant-scoped migrations
resources/views/
  central/             — marketing site views (terms, privacy, contact)
  layouts/central.blade.php
  tenant/admin/        — admin panel views
  tenant/public/       — public portal views
  emails/              — email templates
routes/
  web.php              — central domain routes + WhatsApp webhook
  tenant.php           — tenant routes (public portal + admin panel)
```

---

## Environment Variables

```env
# App
APP_URL=https://yourdomain.com
SESSION_DOMAIN=           # empty = use request host (required for localhost dev)

# AI Services
AI_SERVICE_URL=http://127.0.0.1:9000
ANTHROPIC_API_KEY=sk-ant-...
OPENAI_API_KEY=sk-...
OPENAI_CHAT_MODEL=gpt-4o-mini

# Cloudflare Turnstile
TURNSTILE_SITE_KEY=...
TURNSTILE_SECRET_KEY=...

# Twilio (SMS)
TWILIO_SID=...
TWILIO_TOKEN=...
TWILIO_FROM=+1...

# Mail
MAIL_FROM_ADDRESS=noreply@schoolytics.app
MAIL_FROM_NAME="Schoolytics"
MAIL_SUPPORT_ADDRESS=support@schoolytics.app
```



add openAi or other model integration for school settings, so school can choose their own if they wish , other wise, schoolytics will handle. this may cause resuuts differenciate, lets sy we use openAI and they choose antrhpic. results may vary, is it good or bad approcah to give this oprion to shcool?


update user manual and add all filters, flows, features added newly in last 2 days

