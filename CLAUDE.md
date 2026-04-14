# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Start all dev services (server + queue + logs + vite)
composer run dev

# Run tests
composer test
php artisan test --filter=TestClassName

# Code formatting (run before committing)
vendor/bin/pint --dirty

# Database
php artisan migrate
php artisan db:seed
php artisan tenants:migrate          # Run tenant migrations across all tenants
php artisan tenants:seed             # Seed all tenants

# Queue worker (required for AI analysis and email jobs)
php artisan queue:listen

# Custom commands
php artisan analyze:all-issues       # Dispatch AI sentiment jobs for all issues
```

## Architecture Overview

This is **Schoolytics** — a multi-tenant school issue-tracking platform. Schools (tenants) are isolated by subdomain (e.g., `schoola.lvh.me:8000`). The central admin lives at `central.lvh.me:8000/nova`.

### Two Separate Auth Systems

| Guard | Model | Users | Entry point |
|---|---|---|---|
| `central` | `CentralUser` (table: `central_users`) | Superadmins only | `/nova` |
| `web` | `User` (table: `users`) | Tenant staff | `/admin/login` |

Never mix these. `IssuePolicy` uses a `before()` hook to grant `CentralUser` full access since it can't have Spatie roles.

### Multi-Tenancy (Stancl Tenancy v3)

- **Row-level isolation** — single database, all tenant models have a `tenant_id` column
- The `BelongsToTenant` trait on a model adds a global scope that automatically filters by `tenant('id')` — always use this on new tenant-scoped models
- `InitializeTenancyByDomain` middleware sets the tenant context from the subdomain
- `tenant('id')` returns the current tenant's UUID string — use this directly in queries instead of relying on the global scope when security is critical (e.g., `abort_unless($model->tenant_id === tenant('id'), 404)`)
- Tenant-specific migrations live in `database/migrations/tenant/` and are run via `php artisan tenants:migrate`
- `lvh.me` automatically resolves all subdomains to `127.0.0.1` locally — no hosts file changes needed

### Three-Tier Role System (Spatie Permission)

Roles scoped to tenant via Spatie's team feature. Always set team ID before checking roles:
```php
app(PermissionRegistrar::class)->setPermissionsTeamId(tenant('id'));
```

- **admin** — sees all issues in tenant, can assign to anyone, manage users/contacts
- **branch_manager** — sees issues in their branch(es), can assign to staff in their branch
- **staff** — sees only issues assigned to them; can resolve issues but **cannot close** them

Role-based query scoping lives in `Issue::scopeVisibleTo(User $user)`.

**Status transition rules:**
- `closed` status can only be set by: admin, branch_manager, or a RosterContact (via public portal `/issues/{id}/close`)
- Staff are blocked from `closed` in both the policy (`IssuePolicy::updateStatus` checks `$to !== 'closed'`) and the view dropdown
- `WorkflowController::updateStatus` passes the target status to the policy: `$this->authorize('updateStatus', [$issue, $to])`

### Public Portal Flow

Parents/teachers (`RosterContact`) don't have accounts — they authenticate via **access codes**:

1. Admin imports `RosterContact` records (CSV/Excel) or creates them manually
2. Admin generates an `AccessCode` and sends it via email/SMS
3. Contact visits `/{tenant}.lvh.me` → enters code → submits issues or tracks status
4. Only **one open issue** per contact is allowed at a time — enforced by checking for any non-closed issue for that `roster_contact_id`
5. `AccessCode.used_at` is stamped on submission and **reset to `null` when the issue is closed** (both `WorkflowController::updateStatus` and `IssueStatusController::close`) so the contact can submit again
6. On submission, `IssueCreated` event fires → `PerformAiAnalysis` listener → AI call → Python microservice at `config('services.ai.url')` (env: `AI_SERVICE_URL`, default `http://127.0.0.1:9000`)
7. Public submit route is throttled: `throttle:10,1`; max 5 attachments per submission

### Key Relationships

```
Tenant → School → Branch → [Users (staff), RosterContacts]
RosterContact → AccessCode (one active at a time)
RosterContact → Issues
Issue → IssueMessages → IssueAttachments
Issue → IssueActivities (audit log)
Issue → IssueAiAnalysis (sentiment from Python service)
Issue → CsatResponse (one per issue, sent on close)
```

### Nova (Super-Admin Panel)

Only accessible on central domain, uses `central` guard. All Nova resources must handle the central/tenant auth split carefully — use `before()` in policies to grant `CentralUser` access.

**Nova Actions on Tenant resource:**
- `ProvisionTenant` (standalone) — creates tenant, domain, school, default branch, seeds roles + 10 default issue categories, creates first admin user, then sends `TenantProvisionedMail` to the admin email
- `ResetSchoolData` — wipes all issues, contacts, access codes, CSAT, notifications; keeps categories/users/branches. Requires typing `RESET`
- `GenerateDemoData` — seeds per-branch: 1 branch_manager + 2 staff (category-assigned), 3 parents + 3 teachers (each with access code + 1 open issue). Issue titles are matched to category name by keyword. Only uses the 10 standard seeded categories

**Nova Actions on User resource:**
- `PermanentlyDeleteUser` — hard-deletes user, nulls `assigned_user_id` on their issues, anonymises their messages to "Deleted User", clears activity actor. Requires typing `DELETE`

### Default Issue Categories (TenantIssueCategoriesSeeder)

Seeded on every new tenant: `Transport`, `Academics`, `Facilities`, `Behavior`, `Food & Dining`, `Communication`, `Health & Safety`, `Fees & Payments`, `Technology Issues`, `General Complaints`.

### CSAT Surveys

When an issue is closed (by any actor), a `CsatResponse` row is created with a unique token and `CsatSurveyMail` is queued to the `RosterContact`'s email. The contact clicks a 1–5 rating link: `GET /{tenant}/csat/{token}/{rating}` → `CsatController::store()` marks `submitted_at`. One-time only — double submissions are silently ignored. Results appear in the Reports page.

### Assignment Scoping

The assign modal in `IssueController::show()` always scopes staff to the **issue's branch** — cross-branch assignment is blocked both in the UI (filtered `$staff` collection) and server-side (`WorkflowController::assign()` aborts 422 if the assignee doesn't belong to the issue's branch). Each staff row in the modal shows their assigned categories (eager-loaded via `categories:id,name`).

### Email Notifications

All mail uses `Mail::to($address)->queue(new SomeMail(...))` — never `Mail::queue()` alone (no recipient) and never `Mail::send()` (blocks HTTP worker).

| Trigger | Mailable | Recipient |
|---|---|---|
| Tenant provisioned | `TenantProvisionedMail` | New admin |
| Issue assigned | `IssueAssignedMail` | Assigned staff |
| Status → resolved/closed | `IssueStatusChangedMail` | RosterContact |
| Comment added | `IssueCommentedMail` | Assigned staff |
| Issue closed | `CsatSurveyMail` | RosterContact |

Mail config env keys: `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `MAIL_SUPPORT_ADDRESS` (shown in transactional emails as contact address — falls back to `MAIL_FROM_ADDRESS`).

In dev: `MAIL_MAILER=smtp` with Mailtrap sandbox (see `.env`). All emails logged when `MAIL_MAILER=log`.

### In-App Notifications

Laravel database notifications (`notifications` table, UUID keyed). `User` has the `Notifiable` trait.

- `IssueAssignedNotification` — fired in `WorkflowController::assign()` to the new assignee
- `IssueCommentedNotification` — fired in `WorkflowController::comment()` to assignee; if commenter is staff, also fires to branch manager + all admins
- `ContactRepliedNotification` — fired in `IssueStatusController::reply()` to assignee + all admins
- Bell dropdown in header loads `$user->unreadNotifications()->latest()->take(15)->get()` on every page render
- Single notification marked read via `POST /admin/notifications/{id}/read` (JS fetch on link click)
- All marked read via `POST /admin/notifications/read-all`

### Reports Page

`ReportsController` at `GET admin/reports` — same role-based scoping as `DashboardController`. Accepts `from`, `to`, `grain` (day/week/month) query params (defaults: last 30 days, day grain). Renders: KPI cards, trend chart (ECharts stacked area), SLA card, branch table, staff table, category table, CSAT section with per-branch breakdown.

### Attachments

`IssueAttachment` stores `disk`, `path`, `mime`, `size`. Use `$att->storage_url` (accessor) — not `Storage::disk()->url()` — to get the URL. The accessor uses `request()->getSchemeAndHttpHost()` so URLs always point to the current tenant domain, not the central `APP_URL`.

### Issue Messages — Internal Flag

`IssueMessage.is_internal` (boolean, default `true`):
- Staff comments via admin panel default to `is_internal = true`
- Staff can toggle to "Reply to Contact" (`is_internal = false`) in the comment form
- Contact replies from the public portal are always `is_internal = false`
- Public portal only loads messages where `is_internal = false`

### Issue Detail Page — Conversation Layout

`IssueMessage.sender` values: `admin | teacher` (staff side) vs `parent` (contact side) vs `system`.

The chat renders using `auth()->id()` vs `$msg->author_id` (polymorphic, `author_type` = model class). If `author_id === auth()->id()` → right-aligned blue bubble ("You"); otherwise → left-aligned grey bubble. The original issue description is always rendered as the first left-side bubble (attributed to the `RosterContact`), not inside `IssueMessage` rows.

Variable names in `show.blade.php` use `$isMyMsg`, `$msgDisplayName`, `$msgRoleLabel` — deliberately prefixed to avoid collision with the `$staff` Collection passed for the assign dropdown.

`IssueMessage` has a `getSenderLabelAttribute()` accessor — always use `$msg->sender_label` in views.

### Route Quirks

Two separate `admin/issues` route groups exist in `routes/tenant.php` — one under `auth:web` middleware (GET detail/listing routes named `tenant.admin.issues.*`) and a second under plain `auth` middleware for POST workflow actions (assign, status, priority, comment). The `can:manage-users` middleware group covers contacts, users, branches, and categories management.

### Code Conventions

- PHP 8.2+, constructor property promotion, explicit return types
- Form Requests for validation (not inline `$request->validate()` in new code)
- Queue jobs for async work (AI analysis, activity logging)
- `ilike` for case-insensitive PostgreSQL string matching (not `like`)
- Pagination with `->paginate(25)->withQueryString()` — always carry query string on paginated lists
- Status values: `new | in_progress | resolved | closed` (DB check constraint — never use `open`)
- Priority values: `low | medium | high | urgent`
- All index filter forms use `<form method="GET">` with server-side query logic — no client-side DataTables JS filtering
- Soft-delete pattern for Users: `withTrashed()` in index, `onlyTrashed()` for "disabled" filter; restore via `POST admin/users/enable/{user}`

### Tenant Isolation — Model Checklist

All tenant-scoped models **must** use `BelongsToTenant` trait and include `tenant_id` in `$fillable`. Models confirmed with this trait: `Issue`, `IssueActivity`, `IssueMessage`, `IssueAiAnalysis`, `RosterContact`, `AccessCode`, `Branch`, `School`, `IssueCategory`, `IssueNote`, `CsatResponse`.

If adding `BelongsToTenant` to a model, always check whether the DB `tenant_id` column exists and create a migration if not — the trait crashes immediately if the column is missing.

### Branch Manager Scope

`User` does NOT have a `branch_id` column — the relationship is many-to-many via the `branch_user` pivot (columns: `tenant_id`, `branch_id`, `user_id`, `title`). Always use `$user->branches->pluck('id')`. Never use `$user->branch_id` — always null.

Staff→category assignments are many-to-many via `issue_category_user` pivot (columns: `tenant_id`, `user_id`, `issue_category_id`). When syncing either pivot, always include `tenant_id` in the pivot data:
```php
$user->branches()->attach($branchId, ['tenant_id' => tenant('id'), 'title' => 'Staff']);
$user->categories()->sync([$catId => ['tenant_id' => tenant('id')]]);
```

### ⚠ Queue + BelongsToTenant Serialization Trap

**Never store an Eloquent model that uses `BelongsToTenant` directly in a queued event or job using `SerializesModels`.** When the queue worker restores the model, `BelongsToTenant`'s global scope applies `WHERE tenant_id = NULL` (no tenant context yet), causing `firstOrFail()` to throw `ModelNotFoundException`.

**The fix:** store plain scalar properties (`tenantId`, `issueId`, `description`) instead of the model, then initialize tenancy manually in `handle()`:

```php
// In the job constructor — store scalars, not the model
public function __construct(
    public readonly int $issueId,
    public readonly string $tenantId,
    public readonly string $description,
) {}

// In handle() — initialize tenancy before any tenant-scoped query
public function handle(): void
{
    $tenant = \App\Models\Tenant::find($this->tenantId); // Tenant has no global scope
    tenancy()->initialize($tenant);
    try {
        $issue = Issue::find($this->issueId); // safe — tenancy is now set
        // ... do work
    } finally {
        tenancy()->end();
    }
}
```

This applies to both queued Jobs and queued Listeners that receive events containing tenant models. `QueueTenancyBootstrapper` is registered but initializes tenancy *after* model deserialization — too late to help here.
