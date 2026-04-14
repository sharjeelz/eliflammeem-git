<?php

namespace App\Http\Controllers\Public;

use App\Events\IssueCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\IssueSubmitRequest;
use App\Mail\IssueAssignedMail;
use App\Mail\IssueReceivedMail;
use App\Models\Branch;
use App\Models\Issue;
use App\Models\IssueActivity;
use App\Models\IssueAttachment;
use App\Models\School;
use App\Models\User;
use App\Notifications\IssueAssignedNotification;
use App\Notifications\NewIssueNotification;
use App\Services\PlanService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class IssueSubmitController extends Controller
{
    public function store(IssueSubmitRequest $request)
    {
        $data = $request->validated();

        // 1) Active access code (unused + not expired)
        $code = \App\Models\AccessCode::where('tenant_id', tenant('id'))
            ->where('code', $data['code'])
            ->active()
            ->first();

        if (! $code) {
            $usedCode = \App\Models\AccessCode::where('tenant_id', tenant('id'))
                ->where('code', $data['code'])
                ->whereNotNull('used_at')
                ->first();

            if ($usedCode) {
                $hasOpenIssue = \App\Models\Issue::where('tenant_id', tenant('id'))
                    ->where('roster_contact_id', $usedCode->roster_contact_id)
                    ->where('status', '!=', 'closed')
                    ->exists();

                if ($hasOpenIssue) {
                    return back()->withErrors(['code' => 'You already have an open case with us. Please wait for it to be resolved, or visit your tracking link to close it yourself if your concern has been addressed.'])->withInput();
                }
            }

            return back()->withErrors(['code' => 'Access code is invalid, expired, or already used.'])->withInput();
        }

        // 2) Check contact is not deactivated (belt-and-suspenders; deactivation also revokes the code)
        $submitContact = \App\Models\RosterContact::find($code->roster_contact_id);
        if ($submitContact && $submitContact->deactivated_at) {
            return back()->withErrors(['code' => 'Your account is no longer active. Please contact the school.'])->withInput();
        }

        // 3) Load school + branch for this tenant
        $school = School::where('tenant_id', tenant('id'))->first();
        if (! $school) {
            return back()->withErrors(['code' => 'School not provisioned yet.'])->withInput();
        }

        if ($school->status === 'inactive') {
            return back()->withErrors(['code' => 'This school portal is currently suspended.'])->withInput();
        }

        if (! $school->setting('allow_new_issues', true)) {
            return back()->withErrors(['code' => 'Issue submissions are currently closed. Please check back later.'])->withInput();
        }

        // Plan: monthly issue cap
        $plan = PlanService::forCurrentTenant();
        $monthlyLimit = $plan->monthlyIssueLimit();
        if ($monthlyLimit !== null) {
            $usedThisMonth = Issue::where('tenant_id', tenant('id'))
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count();
            if ($usedThisMonth >= $monthlyLimit) {
                return back()->withErrors(['code' => "This school has reached its monthly issue limit ({$monthlyLimit}). Please try again next month."])->withInput();
            }
        }

        // Contact's current branch takes priority — reflects latest admin reassignment.
        $rawBranchId = $submitContact?->branch_id ?: $code->branch_id;
        $branchId = $rawBranchId
            ?: Branch::active()->where('tenant_id', tenant('id'))->where('school_id', $school->id)->orderBy('id')->value('id');

        // Block submission if the contact's branch is inactive
        if ($rawBranchId && ! Branch::active()->where('id', $rawBranchId)->exists()) {
            return back()->withErrors(['code' => 'Your branch is currently inactive. Please contact the school for assistance.'])->withInput();
        }
        // 3) Unique short public_id per tenant
        $publicId = $this->generatePublicId();

        // check if the issue is already submitted by this contact with status not closed then allow submission
        $issueExists = Issue::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $code->roster_contact_id)
            ->where('status', '!=', 'closed')
            ->exists();

        if ($issueExists) {
            return back()->withErrors(['code' => 'You have already submitted an issue, please wait for it to be resolved before submitting a new one. or if its resolved , please close it'])->withInput();
        }

        $categoryId = $data['category_id'] ?? null;
        if (! $categoryId) {
            $categoryId = \App\Models\IssueCategory::where('tenant_id', tenant('id'))
                ->where('name', 'General Complaints')
                ->value('id');
        }

        // 1. Find staff linked to this category in this branch (first match)
        $assignee = $categoryId
            ? User::role('staff')
            ->where('tenant_id', tenant('id'))
            ->whereHas('categories', fn($q) => $q->where('issue_categories.id', $categoryId))
            ->whereHas('branches', fn($q) => $q->where('branches.id', $branchId))
            ->orderBy('id')
            ->first()
            : null;

        // 2. Fall back to branch manager
        if (! $assignee) {
            $assignee = User::defaultAssigneeForBranch($branchId);
        }
        // 4) Create the issue (with rule-based spam detection)
        $contact = \App\Models\RosterContact::find($code->roster_contact_id);
        $title = filled($data['title'] ?? null)
            ? $data['title']
            : \Illuminate\Support\Str::limit(strtok(trim($data['description']), "\n"), 80);

        [$isSpam, $spamReason] = $this->detectSpam(
            $title . ' ' . $data['description'],
            $code->roster_contact_id,
            $contact?->spam_pardoned_at
        );

        // Spam issues stay unassigned — no point routing junk to staff
        if ($isSpam) {
            $assignee = null;
        }

        $issue = Issue::create([
            'tenant_id' => tenant('id'),
            'school_id' => $school->id,
            'branch_id' => $branchId,
            'public_id' => $publicId,
            'source_role' => optional($code->contact)->role,
            'roster_contact_id' => $code->roster_contact_id,
            'title' => $title,
            'description' => $data['description'],
            'status' => $isSpam ? 'closed' : 'new',
            'priority' => 'medium',
            'issue_category_id' => $categoryId,
            'is_spam' => $isSpam,
            'spam_reason' => $spamReason,
        ]);

        if ($assignee) {
            IssueActivity::create([
                'tenant_id' => tenant('id'),
                'issue_id' => $issue->id,
                'actor_id' => null, // system assignment
                'type' => 'assigned',
                'data' => ['from' => null, 'to' => $assignee->id],
            ]);
        }
        $issue->assigned_user_id = $assignee?->id;
        $issue->save();

        // Send assignment email + in-app notification to the auto-assigned user
        if ($assignee && ! $isSpam) {
            $schoolName = $school->name ?? 'School';
            $issue->loadMissing('branch');
            $assignee->notify(new IssueAssignedNotification($issue, null)); // null actor = system
            if ($assignee->email) {
                Mail::to($assignee->email)->queue(new IssueAssignedMail($issue, $assignee, $schoolName));
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

            // Auto-revoke after 5 confirmed spams since last pardon
            $totalSpam = Issue::where('tenant_id', tenant('id'))
                ->where('roster_contact_id', $code->roster_contact_id)
                ->where('is_spam', true)
                ->when($contact?->spam_pardoned_at, fn($q) => $q->where('created_at', '>', $contact->spam_pardoned_at))
                ->count();

            if ($totalSpam >= 5) {
                \App\Models\AccessCode::where('tenant_id', tenant('id'))
                    ->where('roster_contact_id', $code->roster_contact_id)
                    ->delete();

                \App\Models\RosterContact::where('id', $code->roster_contact_id)
                    ->update(['revoke_reason' => 'Auto-revoked: reached ' . $totalSpam . ' spam submissions']);

                return redirect()->route('tenant.public.home')
                    ->withErrors(['code' => 'Your access has been revoked due to repeated violations.']);
            }
        }

        // Mark the access code as used.
        // For auto-spam (immediately closed), reset used_at so the contact can resubmit —
        // prior spam history will score them higher on the next attempt.
        $code->update(['used_at' => $isSpam ? null : now()]);

        // 5) Save attachments (if any)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("issues/{$issue->id}", 'local');
                IssueAttachment::create([
                    'tenant_id' => tenant('id'),
                    'issue_id' => $issue->id,
                    'disk' => 'local',
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        IssueCreated::dispatch($issue->id, $issue->tenant_id, $issue->description, $issue->title, $categoryId);

        // Thank-you confirmation email to the contact (skip for spam)
        if (! $isSpam && $contact?->email) {
            $trackingUrl = route('tenant.public.status', ['public_id' => $issue->public_id]);
            Mail::to($contact->email)->queue(new IssueReceivedMail(
                $issue,
                $contact->name,
                $school->name ?? 'School',
                $trackingUrl,
            ));
        }

        // Notify assignee + all admins about the new issue (skip for spam)
        if (! $isSpam) {
            $contactName = $code->contact?->name ?? 'A contact';
            $notification = new NewIssueNotification($issue, $contactName);

            if ($assignee) {
                $assignee->notify($notification);
            }

            User::role('admin')
                ->where('tenant_id', tenant('id'))
                ->each(fn($admin) => $admin->notify($notification));
        }

        $school = \App\Models\School::where('tenant_id', tenant('id'))->first();
        $thankyou = $school?->setting('thankyou_message') ?: 'Your issue has been submitted successfully.';

        return redirect()->route('tenant.public.status', ['public_id' => $issue->public_id])
            ->with('ok', $thankyou . ' Tracking ID: ' . $issue->public_id);
    }

    /**
     * Rule-based spam detection. Returns [bool $isSpam, ?string $reason].
     */
    private function detectSpam(string $text, int $contactId, ?\Illuminate\Support\Carbon $pardonedAt = null): array
    {
        $score = 0;
        $reasons = [];

        $wordCount = str_word_count(strip_tags($text));
        if ($wordCount < 4) {
            $score += 40;
            $reasons[] = 'Message too short';
        }

        // Description is suspiciously short even if word count passes
        if (strlen(trim($text)) < 10) {
            $score += 30;
            $reasons[] = 'Description too brief';
        }

        if (preg_match('/https?:\/\/|www\./i', $text)) {
            $score += 50;
            $reasons[] = 'Contains URL';
        }

        $stripped = preg_replace('/\s+/', '', $text);
        if (strlen($stripped) > 5 && strtoupper($stripped) === $stripped) {
            $score += 20;
            $reasons[] = 'All caps';
        }

        if (preg_match('/(.)\1{4,}/', $text)) {
            $score += 20;
            $reasons[] = 'Repeated characters';
        }

        $priorSpam = Issue::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contactId)
            ->where('is_spam', true)
            ->when($pardonedAt, fn($q) => $q->where('created_at', '>', $pardonedAt))
            ->count();

        if ($priorSpam > 0) {
            $score += 50;
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
