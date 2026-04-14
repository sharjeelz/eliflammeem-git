# Security Auditor Agent Memory — Schoolytics

## Audit History
- **2026-03-06**: First full security audit completed. See `audit-2026-03-06.md` for findings.
- **2026-03-10**: Second audit (incremental) covering new chatbot/AI/document features. See `audit-2026-03-10.md`.
- **2026-03-12**: Focused audit of public portal. See `audit-2026-03-12.md` for findings.
- **2026-03-28**: Full production readiness audit. See `audit-2026-03-28.md` for findings.

## Architecture Quick Reference
- Multi-tenant single-DB (row-level via `tenant_id`). `BelongsToTenant` = global scope.
- Guards: `central` (CentralUser/Nova) vs `web` (User/tenant admin).
- Roles: `admin > branch_manager > staff`. Scoped via Spatie teams.
- Public portal: no auth — access code only. Throttled at `throttle:10,1`.
- Job pattern: store scalars, initialize tenancy in `handle()`, end in `finally`.
- API routes: `routes/api_tenant.php` — Bearer token auth via `AuthenticateTenantApiKey` middleware.

## Confirmed Hardened Areas (as of 2026-03-28)
- Workflow routes: `abort_unless($issue->tenant_id === tenant('id'), 404)` present everywhere.
- Branch manager cross-branch assignment: blocked in `WorkflowController::assign()`.
- Staff status "closed" block: enforced in `IssuePolicy::updateStatus()`.
- Access code entropy: `Str::random(10)` = 62^10 — adequate.
- Nova destructive actions: `ResetSchoolData` and `PermanentlyDeleteUser` verify server-side string.
- Notification IDOR: `markRead` chains off `$request->user()->notifications()`.
- `getIssuesByUser`: branch-manager scoped to own branches.
- Attachment access: `local` disk + signed URLs (6h TTL). Controller enforces tenant_id check.
- WhatsApp webhook POST: HMAC-SHA256 verified before payload is touched.
- API key middleware: SHA-256 hash, revocation/expiry, per-IP lockout, plan gate, daily limit.
- Document upload: MIME allowlist pdf/doc/docx/txt, stored on local disk, tenant check on CRUD.
- Vector search: embedding literal is float-only (from OpenAI) — safe interpolation.
- Chatbot injection: pre-LLM pattern blacklist + output guard + system/user role separation.
- IssueSubmitController now uses `$file->getMimeType()` (server-side) NOT `getClientMimeType()`.
- AgentConversation/Message: now have BelongsToTenant + `sendMessage` validator scoped by tenant_id AND user_id.
- BroadcastRecipient: now has BelongsToTenant.
- Activity log XSS: view wraps all user data in `e()` before `{!! $description !!}`.
- SerializesModels in mailables: SAFE — QueueTenancyBootstrapper initializes tenancy on JobProcessing (before unserialize). Order: JobProcessing → $job->fire() → CallQueuedHandler::call() → unserialize().
- LogActivityJob: uses SerializesModels but performedOn is always null at call sites — safe.

## Open Vulnerabilities (2026-03-28)
NOTE: Items 1, 2, 8 from previous list are FIXED. Items 3-7, 9-12 remain open. New items added below.

1. **MEDIUM** — `AiUsageLog` no `BelongsToTenant` — Nova resource lists all tenant costs unfiltered.
2. **MEDIUM** — `IssueActivity::create()` missing explicit `tenant_id` in many places (relies on auto-set). Missing in `IssueStatusController::reply()` line 344 and `WorkflowController::assign()` line 88.
3. **MEDIUM** — WhatsApp webhook GET (`verify`) still uses attacker-controlled `?tenant_id=` query param (VULN-005 comment in SchoolSettingsController refers to opaque webhook_id fix, but GET verify endpoint may still be vulnerable).
4. **MEDIUM** — `AccessCodeController::store()` email/phone OR logic allows contact enumeration (timing).
5. **LOW** — Turnstile test keys hardcoded as defaults in `config/services.php` — bypasses CAPTCHA if env not set.
6. **MEDIUM** — `/resend-code` POST has Turnstile check (confirmed in AccessCodeController::store line 25). RESOLVED.
7. **MEDIUM** — Anonymous followup `POST /status/{id}/followup` now has Turnstile check (confirmed IssueStatusController::anonymousFollowup line 246). RESOLVED.
8. **LOW** — `AnonymousIssueController` is dead code — route `POST /anonymous` does not exist. Remove.
9. **CRITICAL** — `close_note`/`close_reason` migration (`2026_03_28_100000_add_close_fields_to_issues_table.php`) is in central migrations folder. Issues table is tenant-scoped. Must move to `database/migrations/tenant/` and run `tenants:migrate`.
10. **HIGH** — `BulkIssueController::applyStatus()` does not enforce `close_note` requirement when bulk-closing issues. WorkflowController enforces it for single close but bulk close bypasses it.
11. **MEDIUM** — `SendAnnouncementViaMail::handle()` calls `Mail::to(...)->queue(...)` inside a queued job — double-queues the email. Should call `Mail::to(...)->send(...)`.
12. **MEDIUM** — External-ID-as-access-code flow in `UnifiedSubmitController` (lines 65-106): the hasOpenIssue check and code renewal are NOT in a DB transaction — TOCTOU race condition under concurrent submissions.
13. **LOW** — `IssueStatusController::close()` does not check `close_note` (admin-required field). Contact self-close uses `close_reason` (enum) which is correct — no close_note needed there. OK.
14. **MEDIUM** — Monthly issue cap in `UnifiedSubmitController` (lines 147-157) is not in a DB transaction with issue creation — race condition allows slightly over-limit submissions under high concurrency.

## Recurring Patterns to Watch
- New models MUST have `BelongsToTenant` + DB column or crash immediately.
- `AgentConversation`/`AgentConversationMessage` use manual scope — NOT BelongsToTenant.
- `AiUsageLog`, `ApiRequestLog`, `TenantApiKey` are central tables — no BelongsToTenant by design.
- `WorkflowController` routes use `middleware(['auth'])` — functionally equivalent since web is default guard.
- `BroadcastRecipient` no `BelongsToTenant` (no tenant_id column) — known cross-tenant risk.
