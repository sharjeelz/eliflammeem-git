<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BulkIssueController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\EscalationRuleController;
use App\Http\Controllers\Admin\IssueGroupController;
use App\Http\Controllers\Admin\IssueController;
use App\Http\Controllers\Admin\IssueNoteController;
use App\Http\Controllers\Admin\NotificationsController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\SchoolSettingsController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SavedFilterController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\WorkflowController;
use App\Http\Controllers\TenantAuth\ForgotPasswordController;
use App\Http\Controllers\TenantAuth\ResetPasswordController;
use App\Http\Controllers\TenantAuth\TwoFactorChallengeController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    \App\Http\Middleware\SetPublicLocale::class,

])->group(function () {
    // Locale switcher
    Route::get('/set-locale/{lang}', function (string $lang) {
        session(['locale' => in_array($lang, ['en', 'ur']) ? $lang : 'en']);
        return redirect()->back()->withInput();
    })->where('lang', 'en|ur')->name('tenant.public.locale');

    Route::get('/', [\App\Http\Controllers\Public\WelcomeController::class, 'index'])->name('tenant.public.home');
    Route::get('/status/{public_id}', [\App\Http\Controllers\Public\IssueStatusController::class, 'show'])->name('tenant.public.status');
    Route::get('/status/by-code/{code}', [\App\Http\Controllers\Public\IssueStatusController::class, 'listByCode'])
        ->middleware('throttle:10,1')
        ->name('tenant.public.status.by_code');
    Route::post('/submit', [\App\Http\Controllers\Public\UnifiedSubmitController::class, 'store'])->middleware('throttle:10,1')->name('tenant.public.submit');
    Route::redirect('/anonymous', '/')->name('tenant.public.anonymous');
    Route::post('/status/{public_id}/followup', [\App\Http\Controllers\Public\IssueStatusController::class, 'anonymousFollowup'])->middleware('throttle:5,60')->name('tenant.public.issue.anonymous_followup');
    Route::post('/issues/{public_id}/close', [\App\Http\Controllers\Public\IssueStatusController::class, 'close'])->middleware('throttle:10,1')->name('tenant.public.issue.close');
    Route::post('/issues/{public_id}/reopen', [\App\Http\Controllers\Public\IssueStatusController::class, 'reopen'])->middleware('throttle:10,1')->name('tenant.public.issue.reopen');
    Route::post('/issues/{public_id}/reply', [\App\Http\Controllers\Public\IssueStatusController::class, 'reply'])->middleware('throttle:20,1')->name('tenant.public.issue.reply');
    Route::get('/issues/{public_id}/still-problem', \App\Http\Controllers\Public\ReopenIssueController::class)->middleware('throttle:5,1')->name('tenant.public.issue.still_problem');
    Route::get('/resend-code', [\App\Http\Controllers\Public\AccessCodeController::class, 'create'])->name('tenant.public.resend.create');
    Route::post('/resend-code', [\App\Http\Controllers\Public\AccessCodeController::class, 'store'])->middleware('throttle:5,1')->name('tenant.public.resend.store');
    Route::get('/csat/{token}/{rating}', [\App\Http\Controllers\Public\CsatController::class, 'store'])
        ->where('rating', '[1-5]')
        ->middleware('throttle:5,1')
        ->name('tenant.public.csat');

    // --- Compliments / Positive Signals ---
    Route::get('/compliment', [\App\Http\Controllers\Public\KudoSubmitController::class, 'create'])->name('tenant.public.compliment');
    Route::post('/compliment', [\App\Http\Controllers\Public\KudoSubmitController::class, 'store'])->middleware('throttle:5,1')->name('tenant.public.compliment.store');

    // --- Public AI Chatbot (no auth required — stateless Q&A for parents) ---
    Route::get('/ask', [\App\Http\Controllers\Public\PublicChatbotController::class, 'index'])->name('tenant.public.chatbot');
    Route::post('/ask', [\App\Http\Controllers\Public\PublicChatbotController::class, 'ask'])->middleware('throttle:20,1')->name('tenant.public.chatbot.ask');

    // --- Public document download (signed URL, no auth) ---
    Route::get('/documents/{document}/download', [\App\Http\Controllers\Public\PublicDocumentController::class, 'download'])
        ->middleware(['signed', 'throttle:30,1'])
        ->name('tenant.public.document.download');

    // --- User Manual (public — no auth required) ---
    Route::get('/manual', fn () => view('manual'))->name('tenant.manual');

    // --- Attachment download (signed URL — works for both admin and public portal) ---
    Route::get('/attachments/{attachment}', [\App\Http\Controllers\AttachmentController::class, 'show'])
        ->middleware(['signed', 'throttle:60,1'])
        ->name('tenant.attachment.show');

    // --- Admin (login required) ---
    Route::prefix('admin')->group(function () {
        // login/logout using Fortify views or simple controllers
        Route::get('/login', [\App\Http\Controllers\TenantAuth\LoginController::class, 'showLoginForm'])->name('tenant.login');
        Route::post('/login', [\App\Http\Controllers\TenantAuth\LoginController::class, 'login'])->name('tenant.login.post');

        // Password reset (unauthenticated)
        Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('tenant.admin.password.request');
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('tenant.admin.password.email')->middleware('throttle:5,1');
        Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('tenant.admin.password.reset');
        Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('tenant.admin.password.update');

        // 2FA challenge (unauthenticated — between first and second factor)
        Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'show'])->name('tenant.admin.two-factor.challenge');
        Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->name('tenant.admin.two-factor.challenge.store');

        // Contract view — public (no auth required so Nova superadmin can open it directly)
        Route::get('/contract', [\App\Http\Controllers\Admin\OnboardingController::class, 'contract'])->name('tenant.admin.contract');

        Route::middleware(['auth:web', 'single.session'])->group(function () {
            Route::post('/logout', [\App\Http\Controllers\TenantAuth\LoginController::class, 'logout'])->name('tenant.logout');

            // --- Onboarding wizard (exempt from onboarding check) ---
            Route::get('/onboarding', [\App\Http\Controllers\Admin\OnboardingController::class, 'show'])->name('tenant.admin.onboarding');
            Route::post('/onboarding/profile', [\App\Http\Controllers\Admin\OnboardingController::class, 'saveProfile'])->name('tenant.admin.onboarding.profile');
            Route::post('/onboarding/terms', [\App\Http\Controllers\Admin\OnboardingController::class, 'acceptTerms'])->name('tenant.admin.onboarding.terms');
            Route::post('/onboarding/complete', [\App\Http\Controllers\Admin\OnboardingController::class, 'complete'])->name('tenant.admin.onboarding.complete');

            // Standalone T&C acceptance for existing tenants (no onboarding wizard)
            Route::get('/terms', [\App\Http\Controllers\Admin\OnboardingController::class, 'termsPage'])->name('tenant.admin.terms');
            Route::post('/terms/accept', [\App\Http\Controllers\Admin\OnboardingController::class, 'acceptTermsStandalone'])->name('tenant.admin.terms.accept');

            // All routes below require onboarding to be complete
            Route::middleware('onboarding')->group(function () {

            Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('tenant.admin.dashboard');

            // In-app notifications
            Route::post('/notifications/{id}/read', [NotificationsController::class, 'markRead'])->name('tenant.admin.notifications.read');
            Route::post('/notifications/read-all', [NotificationsController::class, 'markAllRead'])->name('tenant.admin.notifications.read-all');

            // examples
            Route::get('/issues', [\App\Http\Controllers\Admin\IssueController::class, 'index'])->name('tenant.admin.issues.index');
            Route::get('/issues/export', [IssueController::class, 'export'])->middleware('plan.feature:csv_export')->name('tenant.admin.issues.export');
            Route::post('/issues/bulk', [BulkIssueController::class, 'update'])->name('tenant.admin.issues.bulk');
            Route::get('/issues/{issue}', [\App\Http\Controllers\Admin\IssueController::class, 'show'])->name('tenant.admin.issue.show');
            Route::get('/issues/assigned/me/', [IssueController::class, 'getIssuesToMe'])->name('tenant.admin.issues.assignedTome');

            Route::get('/issues/assigned/{user}', [IssueController::class, 'getIssuesByUser'])
                ->where('user', '[0-9]+')
                ->name('tenant.admin.issues.assigned');

            Route::get('/reports', [ReportsController::class, 'index'])->name('tenant.admin.reports.index');

            // Saved issue filters (per-user presets)
            Route::post('/saved-filters', [SavedFilterController::class, 'store'])->name('tenant.admin.saved-filters.store');
            Route::delete('/saved-filters/{savedFilter}', [SavedFilterController::class, 'destroy'])->name('tenant.admin.saved-filters.destroy');

            // Platform support tickets — submitted from admin panel to the platform team
            Route::get('/support-tickets', [SupportTicketController::class, 'index'])->name('tenant.admin.support-tickets.index');
            Route::post('/support-ticket', [SupportTicketController::class, 'store'])
                ->middleware('throttle:5,1')
                ->name('tenant.admin.support-ticket.store');

            // Self-service profile (all authenticated users)
            Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('tenant.admin.profile.edit');
            Route::put('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('tenant.admin.profile.update');
            Route::put('/profile/password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('tenant.admin.profile.password');

            // 2FA management — requires two_factor plan feature
            Route::middleware('plan.feature:two_factor')->group(function () {
                Route::post('/two-factor/enable', [TwoFactorController::class, 'enable'])->name('tenant.admin.two-factor.enable');
                Route::post('/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('tenant.admin.two-factor.confirm');
                Route::post('/two-factor/disable', [TwoFactorController::class, 'disable'])->name('tenant.admin.two-factor.disable');
                Route::post('/two-factor/recovery-codes', [TwoFactorController::class, 'regenerateCodes'])->name('tenant.admin.two-factor.recovery-codes');
            });

            }); // end onboarding middleware group
        });
    });
    Route::prefix('admin/issues')->middleware(['auth:web', 'single.session', 'onboarding'])->group(function () {
        // listing + detail
        // Route::get('/', [IssueController::class, 'index']);
        // Route::get('/{issue}/show', [IssueController::class, 'show'])->name('tenant.admin.issues.show');

        // assignment
        Route::post('/{issue}/assign', [WorkflowController::class, 'assign']);

        // status transitions
        Route::post('/{issue}/status', [WorkflowController::class, 'updateStatus']);

        // priority change
        Route::post('/{issue}/priority', [WorkflowController::class, 'updatePriority']);

        // category change
        Route::post('/{issue}/category', [WorkflowController::class, 'updateCategory']);

        // submission type override (admin manual tag)
        Route::post('/{issue}/submission-type', [WorkflowController::class, 'updateSubmissionType']);

        // comments/messages (plain text for now)
        Route::post('/{issue}/comment', [WorkflowController::class, 'comment']);

        // delete a message
        Route::delete('/{issue}/messages/{message}', [WorkflowController::class, 'deleteMessage'])->name('tenant.admin.issue.message.delete');

        // private notes
        Route::post('/{issue}/note', [IssueNoteController::class, 'save'])->name('tenant.admin.issue.note.save');
        Route::delete('/{issue}/note', [IssueNoteController::class, 'destroy'])->name('tenant.admin.issue.note.destroy');

        // unassign (admin only)
        Route::post('/{issue}/unassign', [WorkflowController::class, 'unassign'])->name('tenant.admin.issue.unassign');

        // spam
        Route::post('/{issue}/spam', [WorkflowController::class, 'markSpam'])->name('tenant.admin.issue.spam.mark');
        Route::delete('/{issue}/spam', [WorkflowController::class, 'unmarkSpam'])->name('tenant.admin.issue.spam.unmark');

        // get issues by assinged staff

    });
    Route::middleware(['auth:web', 'single.session', 'onboarding', 'can:manage-users'])->group(function () {
        // Compliments / Kudos (admin + branch_manager — role check inside controller)
        Route::get('admin/kudos', [\App\Http\Controllers\Admin\KudoController::class, 'index'])->name('tenant.admin.kudos.index');
        Route::delete('admin/kudos/{id}', [\App\Http\Controllers\Admin\KudoController::class, 'destroy'])->name('tenant.admin.kudos.destroy');

        // Announcements (admin only — role check inside controller)
        Route::get('admin/announcements', [\App\Http\Controllers\Admin\AnnouncementController::class, 'index'])->name('tenant.admin.announcements.index');
        Route::get('admin/announcements/create', [\App\Http\Controllers\Admin\AnnouncementController::class, 'create'])->name('tenant.admin.announcements.create');
        Route::post('admin/announcements', [\App\Http\Controllers\Admin\AnnouncementController::class, 'store'])->name('tenant.admin.announcements.store');
        Route::get('admin/announcements/{announcement}/edit', [\App\Http\Controllers\Admin\AnnouncementController::class, 'edit'])->name('tenant.admin.announcements.edit');
        Route::put('admin/announcements/{announcement}', [\App\Http\Controllers\Admin\AnnouncementController::class, 'update'])->name('tenant.admin.announcements.update');
        Route::delete('admin/announcements/{announcement}', [\App\Http\Controllers\Admin\AnnouncementController::class, 'destroy'])->name('tenant.admin.announcements.destroy');
        Route::post('admin/announcements/{announcement}/publish', [\App\Http\Controllers\Admin\AnnouncementController::class, 'publish'])->name('tenant.admin.announcements.publish');
        Route::post('admin/announcements/{announcement}/draft', [\App\Http\Controllers\Admin\AnnouncementController::class, 'draft'])->name('tenant.admin.announcements.draft');

        // Roster Contacts
        Route::get('admin/contacts', [\App\Http\Controllers\Admin\RosterContactController::class, 'index'])->name('tenant.admin.contacts.index');
        Route::get('admin/contacts/create', [\App\Http\Controllers\Admin\RosterContactController::class, 'create'])->name('tenant.admin.contacts.create');
        Route::post('admin/contacts', [\App\Http\Controllers\Admin\RosterContactController::class, 'store'])->name('tenant.admin.contacts.store');
        // Broadcast — requires broadcasting plan feature
        Route::middleware('plan.feature:broadcasting')->group(function () {
            Route::get('admin/contacts/broadcast', [\App\Http\Controllers\Admin\ContactBroadcastController::class, 'create'])->name('tenant.admin.contacts.broadcast');
            Route::get('admin/contacts/broadcast/count', [\App\Http\Controllers\Admin\ContactBroadcastController::class, 'count'])->name('tenant.admin.contacts.broadcast.count');
            Route::post('admin/contacts/broadcast', [\App\Http\Controllers\Admin\ContactBroadcastController::class, 'store'])->name('tenant.admin.contacts.broadcast.store');
            Route::get('admin/contacts/broadcast/logs', [\App\Http\Controllers\Admin\ContactBroadcastController::class, 'logs'])->name('tenant.admin.contacts.broadcast.logs');
            Route::get('admin/contacts/broadcast/logs/{batch}', [\App\Http\Controllers\Admin\ContactBroadcastController::class, 'logDetail'])->name('tenant.admin.contacts.broadcast.logs.detail');
            Route::post('admin/contacts/broadcast/logs/{recipient}/retry', [\App\Http\Controllers\Admin\ContactBroadcastController::class, 'retryRecipient'])->name('tenant.admin.contacts.broadcast.logs.retry');
        });

        // WhatsApp Templates — requires whatsapp plan feature
        Route::middleware('plan.feature:whatsapp')->group(function () {
            Route::get('admin/whatsapp/templates', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'index'])->name('tenant.admin.whatsapp.templates.index');
            Route::get('admin/whatsapp/templates/create', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'create'])->name('tenant.admin.whatsapp.templates.create');
            Route::post('admin/whatsapp/templates', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'store'])->name('tenant.admin.whatsapp.templates.store');
            Route::get('admin/whatsapp/templates/{template}/edit', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'edit'])->name('tenant.admin.whatsapp.templates.edit');
            Route::put('admin/whatsapp/templates/{template}', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'update'])->name('tenant.admin.whatsapp.templates.update');
            Route::delete('admin/whatsapp/templates/{template}', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'destroy'])->name('tenant.admin.whatsapp.templates.destroy');
        });

        Route::get('admin/contacts/{contact}/edit', [\App\Http\Controllers\Admin\RosterContactController::class, 'edit'])->name('tenant.admin.contacts.edit');
        Route::get('admin/contacts/{contact}/issues', [\App\Http\Controllers\Admin\RosterContactController::class, 'issues'])->name('tenant.admin.contacts.issues');
        Route::put('admin/contacts/{contact}', [\App\Http\Controllers\Admin\RosterContactController::class, 'update'])->name('tenant.admin.contacts.update');
        Route::delete('admin/contacts/{contact}', [\App\Http\Controllers\Admin\RosterContactController::class, 'destroy'])->name('tenant.admin.contacts.destroy');
        Route::post('admin/contacts/{contact}/reactivate', [\App\Http\Controllers\Admin\RosterContactController::class, 'reactivate'])->name('tenant.admin.contacts.reactivate');
        Route::post('admin/contacts/{contact}/generate-code', [\App\Http\Controllers\Admin\RosterContactController::class, 'generateCode'])->name('tenant.admin.contacts.generate-code');
        Route::post('admin/contacts/{contact}/renew-code', [\App\Http\Controllers\Admin\RosterContactController::class, 'renewCode'])->name('tenant.admin.contacts.renew-code');
        Route::post('admin/contacts/{contact}/revoke-code', [\App\Http\Controllers\Admin\RosterContactController::class, 'revokeCode'])->name('tenant.admin.contacts.revoke-code');
        Route::post('admin/contacts/{contact}/send-code', [\App\Http\Controllers\Admin\RosterContactController::class, 'sendCode'])->name('tenant.admin.contacts.send-code');
        Route::post('admin/contacts/bulk-send-code', [\App\Http\Controllers\Admin\RosterContactController::class, 'bulkSendCode'])->name('tenant.admin.contacts.bulk-send-code');
        Route::post('admin/contacts/bulk-generate-code', [\App\Http\Controllers\Admin\RosterContactController::class, 'bulkGenerateCode'])->name('tenant.admin.contacts.bulk-generate-code');
        Route::post('admin/contacts/bulk-revoke-code', [\App\Http\Controllers\Admin\RosterContactController::class, 'bulkRevokeCode'])->name('tenant.admin.contacts.bulk-revoke-code');
        Route::post('admin/contacts/bulk-change-branch', [\App\Http\Controllers\Admin\RosterContactController::class, 'bulkChangeBranch'])->name('tenant.admin.contacts.bulk-change-branch');
        Route::get('admin/contacts/import', [\App\Http\Controllers\Admin\RosterContactController::class, 'importForm'])->name('tenant.admin.contacts.import');
        Route::post('admin/contacts/import', [\App\Http\Controllers\Admin\RosterContactController::class, 'import'])->name('tenant.admin.contacts.import.store');
        Route::get('admin/contacts/template', [\App\Http\Controllers\Admin\RosterContactController::class, 'template'])->name('tenant.admin.contacts.template');

        Route::get('admin/users', [UserController::class, 'index'])->name('tenant.admin.users.index');
        Route::get('admin/users/create', [UserController::class, 'create'])->name('tenant.admin.users.create');
        Route::post('admin/users', [UserController::class, 'store'])->name('tenant.admin.users.store');
        Route::get('admin/users/auto-assign', [UserController::class, 'autoAssign'])->name('tenant.admin.users.auto_assign');
        Route::get('admin/users/{user}/edit', [UserController::class, 'edit'])->name('tenant.admin.users.edit');
        Route::put('admin/users/{user}', [UserController::class, 'update'])->name('tenant.admin.users.update');
        Route::get('admin/users/{user}/issues/', [IssueController::class, 'show'])->name('tenant.admin.user.issues.show');
        Route::delete('admin/users/{user}', [UserController::class, 'destroy'])->name('tenant.admin.users.destroy');
        Route::post('admin/users/enable/{user}', [UserController::class, 'restore'])->name('tenant.admin.users.enable');
        Route::post('admin/users/{user}/force-logout', [UserController::class, 'forceLogout'])->name('tenant.admin.users.force-logout');

        // Branches
        Route::get('admin/branches', [BranchController::class, 'index'])->name('tenant.admin.branches.index');
        Route::get('admin/branches/create', [BranchController::class, 'create'])->name('tenant.admin.branches.create');
        Route::post('admin/branches', [BranchController::class, 'store'])->name('tenant.admin.branches.store');
        Route::get('admin/branches/{branch}/edit', [BranchController::class, 'edit'])->name('tenant.admin.branches.edit');
        Route::put('admin/branches/{branch}', [BranchController::class, 'update'])->name('tenant.admin.branches.update');
        Route::delete('admin/branches/{branch}', [BranchController::class, 'destroy'])->name('tenant.admin.branches.destroy');
        Route::post('admin/branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->name('tenant.admin.branches.toggle_status');

        // Issue Categories
        Route::get('admin/categories', [CategoryController::class, 'index'])->name('tenant.admin.categories.index');
        Route::get('admin/categories/create', [CategoryController::class, 'create'])->name('tenant.admin.categories.create');
        Route::post('admin/categories', [CategoryController::class, 'store'])->name('tenant.admin.categories.store');
        Route::get('admin/categories/{category}/edit', [CategoryController::class, 'edit'])->name('tenant.admin.categories.edit');
        Route::put('admin/categories/{category}', [CategoryController::class, 'update'])->name('tenant.admin.categories.update');
        Route::delete('admin/categories/{category}', [CategoryController::class, 'destroy'])->name('tenant.admin.categories.destroy');

        // AI Issue Groups (bulk resolve)
        Route::get('admin/issue-groups', [IssueGroupController::class, 'index'])->name('tenant.admin.issue_groups.index');
        Route::post('admin/issue-groups/refresh', [IssueGroupController::class, 'refresh'])->name('tenant.admin.issue_groups.refresh');
        Route::get('admin/issue-groups/{issueGroup}', [IssueGroupController::class, 'show'])->name('tenant.admin.issue_groups.show');
        Route::post('admin/issue-groups/{issueGroup}/bulk-resolve', [IssueGroupController::class, 'bulkResolve'])->name('tenant.admin.issue_groups.bulk_resolve');
        Route::post('admin/issue-groups/{issueGroup}/dismiss', [IssueGroupController::class, 'dismiss'])->name('tenant.admin.issue_groups.dismiss');
        Route::post('admin/issue-groups/{issueGroup}/reopen', [IssueGroupController::class, 'reopen'])->name('tenant.admin.issue_groups.reopen');
        Route::delete('admin/issue-groups/{issueGroup}/issues/{issue}', [IssueGroupController::class, 'removeIssue'])->name('tenant.admin.issue_groups.remove_issue');

        // Escalation Rules
        Route::get('admin/escalation-rules', [EscalationRuleController::class, 'index'])->name('tenant.admin.escalation_rules.index');
        Route::get('admin/escalation-rules/create', [EscalationRuleController::class, 'create'])->name('tenant.admin.escalation_rules.create');
        Route::post('admin/escalation-rules', [EscalationRuleController::class, 'store'])->name('tenant.admin.escalation_rules.store');
        Route::get('admin/escalation-rules/{escalationRule}/edit', [EscalationRuleController::class, 'edit'])->name('tenant.admin.escalation_rules.edit');
        Route::put('admin/escalation-rules/{escalationRule}', [EscalationRuleController::class, 'update'])->name('tenant.admin.escalation_rules.update');
        Route::delete('admin/escalation-rules/{escalationRule}', [EscalationRuleController::class, 'destroy'])->name('tenant.admin.escalation_rules.destroy');

        // Activity Log (admin + branch_manager)
        Route::get('admin/activity-log', [ActivityLogController::class, 'index'])->name('tenant.admin.activity_log');

        // Plan & Billing overview (admin only)
        Route::get('admin/plan', [\App\Http\Controllers\Admin\PlanController::class, 'index'])->name('tenant.admin.plan.index');

        // School Settings (admin only)
        Route::get('admin/settings', [SchoolSettingsController::class, 'edit'])->name('tenant.admin.settings.edit');
        Route::put('admin/settings', [SchoolSettingsController::class, 'update'])->name('tenant.admin.settings.update');

        // API Key management (admin only, plan gated)
        Route::prefix('admin/settings')->name('tenant.admin.settings.')->group(function () {
            Route::get('api-keys', [ApiKeyController::class, 'index'])->name('api_keys.index');
            Route::post('api-keys', [ApiKeyController::class, 'store'])->name('api_keys.store');
            Route::delete('api-keys/{id}', [ApiKeyController::class, 'destroy'])->name('api_keys.destroy');
        });

        // Document Management & FAQs — requires document_library plan feature
        Route::middleware('plan.feature:document_library')->group(function () {
            // Document Categories
            Route::get('admin/document-categories', [\App\Http\Controllers\Admin\DocumentCategoryController::class, 'index'])->name('tenant.admin.document_categories.index');
            Route::get('admin/document-categories/create', [\App\Http\Controllers\Admin\DocumentCategoryController::class, 'create'])->name('tenant.admin.document_categories.create');
            Route::post('admin/document-categories', [\App\Http\Controllers\Admin\DocumentCategoryController::class, 'store'])->name('tenant.admin.document_categories.store');
            Route::get('admin/document-categories/{documentCategory}/edit', [\App\Http\Controllers\Admin\DocumentCategoryController::class, 'edit'])->name('tenant.admin.document_categories.edit');
            Route::put('admin/document-categories/{documentCategory}', [\App\Http\Controllers\Admin\DocumentCategoryController::class, 'update'])->name('tenant.admin.document_categories.update');
            Route::delete('admin/document-categories/{documentCategory}', [\App\Http\Controllers\Admin\DocumentCategoryController::class, 'destroy'])->name('tenant.admin.document_categories.destroy');

            // Documents
            Route::get('admin/documents', [\App\Http\Controllers\Admin\DocumentController::class, 'index'])->name('tenant.admin.documents.index');
            Route::get('admin/documents/create', [\App\Http\Controllers\Admin\DocumentController::class, 'create'])->name('tenant.admin.documents.create');
            Route::post('admin/documents', [\App\Http\Controllers\Admin\DocumentController::class, 'store'])->name('tenant.admin.documents.store');
            Route::get('admin/documents/{document}/edit', [\App\Http\Controllers\Admin\DocumentController::class, 'edit'])->name('tenant.admin.documents.edit');
            Route::put('admin/documents/{document}', [\App\Http\Controllers\Admin\DocumentController::class, 'update'])->name('tenant.admin.documents.update');
            Route::delete('admin/documents/{document}', [\App\Http\Controllers\Admin\DocumentController::class, 'destroy'])->name('tenant.admin.documents.destroy');
            Route::get('admin/documents/{document}/download', [\App\Http\Controllers\Admin\DocumentController::class, 'download'])->name('tenant.admin.documents.download');

            // FAQs
            Route::get('admin/faqs', [\App\Http\Controllers\Admin\FaqController::class, 'index'])->name('tenant.admin.faqs.index');
            Route::get('admin/faqs/create', [\App\Http\Controllers\Admin\FaqController::class, 'create'])->name('tenant.admin.faqs.create');
            Route::post('admin/faqs', [\App\Http\Controllers\Admin\FaqController::class, 'store'])->name('tenant.admin.faqs.store');
            Route::get('admin/faqs/{faq}/edit', [\App\Http\Controllers\Admin\FaqController::class, 'edit'])->name('tenant.admin.faqs.edit');
            Route::put('admin/faqs/{faq}', [\App\Http\Controllers\Admin\FaqController::class, 'update'])->name('tenant.admin.faqs.update');
            Route::delete('admin/faqs/{faq}', [\App\Http\Controllers\Admin\FaqController::class, 'destroy'])->name('tenant.admin.faqs.destroy');
        });

        // Chatbot observability logs — requires chatbot plan feature
        Route::get('admin/chatbot/logs', [\App\Http\Controllers\Admin\ChatbotLogController::class, 'index'])->middleware('plan.feature:chatbot')->name('tenant.admin.chatbot.logs');

        // Document Management is admin-only (handled above)
    });
});
Route::middleware(['universal', InitializeTenancyByDomain::class])
    ->prefix(config('sanctum.prefix', 'sanctum'))
    ->get('/csrf-cookie', [CsrfCookieController::class, 'show'])
    ->name('sanctum.csrf-cookie');
