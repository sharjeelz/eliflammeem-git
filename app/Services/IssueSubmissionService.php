<?php

namespace App\Services;

use App\Events\IssueCreated;
use App\Mail\IssueAssignedMail;
use App\Mail\IssueReceivedMail;
use App\Models\AccessCode;
use App\Models\Branch;
use App\Models\Issue;
use App\Models\IssueActivity;
use App\Models\IssueAttachment;
use App\Models\IssueCategory;
use App\Models\RosterContact;
use App\Models\School;
use App\Models\User;
use App\Notifications\IssueAssignedNotification;
use App\Notifications\NewIssueNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class IssueSubmissionService
{
    /**
     * Submit a new issue on behalf of a RosterContact.
     *
     * @param  RosterContact  $contact
     * @param  AccessCode     $code       The active access code for this contact.
     * @param  array          $data       ['description' => ..., 'category_id' => ..., 'title' => ...]
     * @param  UploadedFile[] $files      Uploaded attachment files (max 5).
     * @param  string         $portalBaseUrl  e.g. "https://tenant.schoolytics.app" (for emails)
     * @return array{issue: ?Issue, error: ?string}
     */
    public function submit(
        RosterContact $contact,
        AccessCode $code,
        array $data,
        array $files = [],
        string $portalBaseUrl = ''
    ): array {
        $school = School::where('tenant_id', tenant('id'))->first();

        if (! $school || $school->status === 'inactive') {
            return ['issue' => null, 'error' => 'This school portal is currently suspended.'];
        }

        if (! $school->setting('allow_new_issues', true)) {
            return ['issue' => null, 'error' => 'Issue submissions are currently closed.'];
        }

        // Plan: monthly issue cap
        $plan         = PlanService::forCurrentTenant();
        $monthlyLimit = $plan->monthlyIssueLimit();
        if ($monthlyLimit !== null) {
            $usedThisMonth = Issue::where('tenant_id', tenant('id'))
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count();
            if ($usedThisMonth >= $monthlyLimit) {
                return ['issue' => null, 'error' => "This school has reached its monthly issue limit ({$monthlyLimit})."];
            }
        }

        // Resolve branch
        $rawBranchId = $contact->branch_id ?: $code->branch_id ?? null;
        if ($rawBranchId && ! Branch::active()->where('id', $rawBranchId)->exists()) {
            return ['issue' => null, 'error' => 'Your branch is currently inactive. Please contact the school.'];
        }
        $branchId = $rawBranchId
            ?: Branch::active()->where('tenant_id', tenant('id'))->where('school_id', $school->id)->orderBy('id')->value('id');

        // Category
        $categoryId = $data['category_id'] ?? null;
        if (! $categoryId) {
            $categoryId = IssueCategory::where('tenant_id', tenant('id'))
                ->where('name', 'General Complaints')
                ->value('id');
        }

        // Auto-assign staff
        $assignee = $categoryId
            ? User::role('staff')
                ->where('tenant_id', tenant('id'))
                ->whereHas('categories', fn ($q) => $q->where('issue_categories.id', $categoryId))
                ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
                ->orderBy('id')
                ->first()
            : null;

        if (! $assignee) {
            $assignee = User::defaultAssigneeForBranch($branchId);
        }

        // Title
        $title = filled($data['title'] ?? null)
            ? $data['title']
            : Str::limit(strtok(trim($data['description']), "\n"), 80);

        // Spam detection
        [$isSpam, $spamReason] = $this->detectSpam(
            $title . ' ' . $data['description'],
            $contact->id,
            $contact->spam_pardoned_at
        );

        if ($isSpam) {
            $assignee = null;
        }

        $publicId = $this->generatePublicId();

        // Atomic: lock code row, check open issues, create issue, stamp used_at
        $openIssueConflict = false;
        $issue = DB::transaction(function () use (
            &$openIssueConflict, $code, $contact, $isSpam, $spamReason,
            $school, $branchId, $publicId, $title, $data, $categoryId
        ) {
            AccessCode::where('tenant_id', tenant('id'))
                ->where('id', $code->id)
                ->lockForUpdate()
                ->first();

            if (Issue::where('tenant_id', tenant('id'))
                ->where('roster_contact_id', $contact->id)
                ->where('status', '!=', 'closed')
                ->exists()) {
                $openIssueConflict = true;
                return null;
            }

            $issue = Issue::create([
                'tenant_id'         => tenant('id'),
                'school_id'         => $school->id,
                'branch_id'         => $branchId,
                'public_id'         => $publicId,
                'source_role'       => $contact->role,
                'roster_contact_id' => $contact->id,
                'title'             => $title,
                'description'       => $data['description'],
                'status'            => $isSpam ? 'closed' : 'new',
                'priority'          => 'medium',
                'issue_category_id' => $categoryId,
                'is_spam'           => $isSpam,
                'spam_reason'       => $spamReason,
                'is_anonymous'      => false,
                'submission_type'   => 'complaint',
            ]);

            if (! $isSpam) {
                $code->update(['used_at' => now()]);
            }

            return $issue;
        });

        if ($openIssueConflict) {
            return ['issue' => null, 'error' => 'open_issue_conflict'];
        }

        if ($assignee) {
            IssueActivity::create([
                'tenant_id' => tenant('id'),
                'issue_id'  => $issue->id,
                'actor_id'  => null,
                'type'      => 'assigned',
                'data'      => ['from' => null, 'to' => $assignee->id],
            ]);
            $issue->assigned_user_id = $assignee->id;
            $issue->save();
        }

        if (! $isSpam && $assignee) {
            $assignee->notify(new IssueAssignedNotification($issue, null));
            if ($assignee->email) {
                Mail::to($assignee->email)->queue(new IssueAssignedMail($issue, $assignee, $school->name ?? 'School'));
            }
        }

        if ($isSpam) {
            IssueActivity::create([
                'tenant_id' => tenant('id'),
                'issue_id'  => $issue->id,
                'actor_id'  => null,
                'type'      => 'spam_marked',
                'data'      => ['reason' => $spamReason, 'by' => 'System (auto-detected)'],
            ]);

            $totalSpam = Issue::where('tenant_id', tenant('id'))
                ->where('roster_contact_id', $contact->id)
                ->where('is_spam', true)
                ->when($contact->spam_pardoned_at, fn ($q) => $q->where('created_at', '>', $contact->spam_pardoned_at))
                ->count();

            if ($totalSpam >= 5) {
                AccessCode::where('tenant_id', tenant('id'))
                    ->where('roster_contact_id', $contact->id)
                    ->delete();
                RosterContact::where('id', $contact->id)
                    ->update(['revoke_reason' => 'Auto-revoked: reached ' . $totalSpam . ' spam submissions']);

                return ['issue' => null, 'error' => 'access_revoked'];
            }

            return ['issue' => $issue, 'error' => null, 'is_spam' => true];
        }

        // Attachments
        foreach ($files as $file) {
            $path = $file->store("issues/{$issue->id}", 'local');
            IssueAttachment::create([
                'tenant_id' => tenant('id'),
                'issue_id'  => $issue->id,
                'disk'      => 'local',
                'path'      => $path,
                'mime'      => $file->getMimeType(),
                'size'      => $file->getSize(),
            ]);
        }

        // Fire AI analysis event
        IssueCreated::dispatch($issue->id, $issue->tenant_id, $issue->description, $issue->title, $categoryId);

        // Confirmation email to contact
        if ($contact->email) {
            $trackingUrl = rtrim($portalBaseUrl, '/') . '/status/' . $issue->public_id;
            Mail::to($contact->email)->queue(new IssueReceivedMail(
                $issue,
                $contact->name,
                $school->name ?? 'School',
                $trackingUrl,
            ));
        }

        // In-app notifications
        $notification = new NewIssueNotification($issue, $contact->name);
        if ($assignee) {
            $assignee->notify($notification);
        }
        User::role('admin')
            ->where('tenant_id', tenant('id'))
            ->each(fn ($admin) => $admin->notify($notification));

        return ['issue' => $issue, 'error' => null];
    }

    private function detectSpam(string $text, int $contactId, ?\Illuminate\Support\Carbon $pardonedAt = null): array
    {
        $score   = 0;
        $reasons = [];

        if (str_word_count(strip_tags($text)) < 4) {
            $score   += 40;
            $reasons[] = 'Message too short';
        }

        if (strlen(trim($text)) < 10) {
            $score   += 30;
            $reasons[] = 'Description too brief';
        }

        if (preg_match('/https?:\/\/|www\./i', $text)) {
            $score   += 50;
            $reasons[] = 'Contains URL';
        }

        $stripped = preg_replace('/\s+/', '', $text);
        if (strlen($stripped) > 5 && strtoupper($stripped) === $stripped) {
            $score   += 20;
            $reasons[] = 'All caps';
        }

        if (preg_match('/(.)\1{4,}/', $text)) {
            $score   += 20;
            $reasons[] = 'Repeated characters';
        }

        $priorSpam = Issue::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contactId)
            ->where('is_spam', true)
            ->when($pardonedAt, fn ($q) => $q->where('created_at', '>', $pardonedAt))
            ->count();

        if ($priorSpam > 0) {
            $score   += 50;
            $reasons[] = 'Prior spam history (' . $priorSpam . ' previous)';
        }

        if ($score >= 40) {
            return [true, 'Auto: ' . implode(', ', $reasons)];
        }

        return [false, null];
    }

    private function generatePublicId(int $len = 8): string
    {
        do {
            $id = strtoupper(Str::random($len));
        } while (Issue::where('tenant_id', tenant('id'))->where('public_id', $id)->exists());

        return $id;
    }
}
