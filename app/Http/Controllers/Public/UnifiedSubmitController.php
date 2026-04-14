<?php

namespace App\Http\Controllers\Public;

use App\Events\IssueCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\UnifiedSubmitRequest;
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
use App\Services\PlanService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UnifiedSubmitController extends Controller
{
    public function store(UnifiedSubmitRequest $request)
    {
        $data        = $request->validated();
        $isAnonymous = (bool) $request->boolean('anonymous');

        $school = School::where('tenant_id', tenant('id'))->first();

        if (! $school) {
            return back()->withErrors(['description' => 'School not provisioned yet.'])->withInput();
        }

        if ($school->status === 'inactive') {
            return back()->withErrors(['description' => 'This school portal is currently suspended.'])->withInput();
        }

        if (! $school->setting('allow_new_issues', true)) {
            return back()->withErrors(['description' => 'Issue submissions are currently closed. Please check back later.'])->withInput();
        }

        // Anonymous path: check school setting
        if ($isAnonymous && ! $school->setting('allow_anonymous_issues', true)) {
            return back()->withErrors(['anonymous' => 'Anonymous submissions are not accepted by this school.'])->withInput();
        }

        // Code-authenticated path
        $code    = null;
        $contact = null;

        if (! $isAnonymous) {
            $input = trim($data['code'] ?? '');

            // Step 1: try direct access code match
            $code = AccessCode::where('tenant_id', tenant('id'))
                ->where('code', $input)
                ->active()
                ->first();

            // Step 2: if not found as a code, try treating input as an external_id.
            // Wrapped in a transaction with lockForUpdate to prevent two concurrent
            // requests from the same contact both passing the open-issue check.
            if (! $code) {
                $tenantId         = tenant('id');
                $extIdOpenIssue   = false;

                $code = DB::transaction(function () use ($input, $tenantId, &$extIdOpenIssue) {
                    $contactByExtId = RosterContact::where('tenant_id', $tenantId)
                        ->where('external_id', $input)
                        ->whereNull('deactivated_at')
                        ->whereNull('revoke_reason')
                        ->lockForUpdate()
                        ->first();

                    if (! $contactByExtId) {
                        return null;
                    }

                    if (Issue::where('tenant_id', $tenantId)
                        ->where('roster_contact_id', $contactByExtId->id)
                        ->where('status', '!=', 'closed')
                        ->exists()) {
                        $extIdOpenIssue = true;
                        return null;
                    }

                    $existingCode = AccessCode::where('tenant_id', $tenantId)
                        ->where('roster_contact_id', $contactByExtId->id)
                        ->first();

                    if ($existingCode) {
                        $existingCode->update(['expires_at' => now()->addDays(7), 'used_at' => null]);
                        return $existingCode->fresh();
                    }

                    $rawCode = strtoupper(Str::random(10));
                    while (AccessCode::where('tenant_id', $tenantId)->where('code', $rawCode)->exists()) {
                        $rawCode = strtoupper(Str::random(10));
                    }

                    return AccessCode::create([
                        'tenant_id'         => $tenantId,
                        'roster_contact_id' => $contactByExtId->id,
                        'branch_id'         => $contactByExtId->branch_id,
                        'code'              => $rawCode,
                        'channel'           => 'manual',
                        'expires_at'        => now()->addDays(7),
                    ]);
                });

                if ($extIdOpenIssue) {
                    return back()->withErrors(['code' => 'You already have an open case with us. Please wait for it to be resolved, or visit your tracking link to close it yourself if your concern has been addressed.'])->withInput();
                }
            }

            // Step 3: still no code — check if a used code exists (open case) then give appropriate error
            if (! $code) {
                $usedCode = AccessCode::where('tenant_id', tenant('id'))
                    ->where('code', $input)
                    ->whereNotNull('used_at')
                    ->first();

                if ($usedCode) {
                    $hasOpenIssue = Issue::where('tenant_id', tenant('id'))
                        ->where('roster_contact_id', $usedCode->roster_contact_id)
                        ->where('status', '!=', 'closed')
                        ->exists();

                    if ($hasOpenIssue) {
                        return back()->withErrors(['code' => 'You already have an open case with us. Please wait for it to be resolved, or visit your tracking link to close it yourself if your concern has been addressed.'])->withInput();
                    }
                }

                return back()->withErrors(['code' => 'Access code is invalid, expired, or already used. Use your school/student ID if you don\'t have a code.'])->withInput();
            }

            $contact = RosterContact::find($code->roster_contact_id);

            if ($contact && $contact->deactivated_at) {
                return back()->withErrors(['code' => 'Your account is no longer active. Please contact the school.'])->withInput();
            }

            // One-open-issue check is deferred into the DB transaction below
            // (together with Issue::create + code stamp) to prevent TOCTOU races.
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
                return back()->withErrors(['description' => "This school has reached its monthly issue limit ({$monthlyLimit}). Please try again next month."])->withInput();
            }
        }

        // Resolve branch
        $branchId = null;
        if (! $isAnonymous && $code) {
            // Contact's current branch takes priority — reflects latest admin reassignment.
            // Access code branch_id is a fallback (may be stale after branch changes).
            $rawBranchId = $contact?->branch_id ?: $code->branch_id ?? null;

            // Block if the contact's assigned branch is inactive
            if ($rawBranchId && ! Branch::active()->where('id', $rawBranchId)->exists()) {
                return back()->withErrors(['description' => 'Your branch is currently inactive. Please contact the school for assistance.'])->withInput();
            }

            $branchId = $rawBranchId
                ?: Branch::active()->where('tenant_id', tenant('id'))->where('school_id', $school->id)->orderBy('id')->value('id');
        } else {
            $branchId = Branch::active()->where('tenant_id', tenant('id'))->where('school_id', $school->id)->orderBy('id')->value('id');
        }

        // Category
        $categoryId = $data['category_id'] ?? null;
        if (! $categoryId) {
            $categoryId = IssueCategory::where('tenant_id', tenant('id'))
                ->where('name', 'General Complaints')
                ->value('id');
        }

        // Auto-assign staff
        $assignee = null;
        if (! $isAnonymous) {
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
        }

        // Title
        $title = filled($data['title'] ?? null)
            ? $data['title']
            : Str::limit(strtok(trim($data['description']), "\n"), 80);

        // Spam detection (only for authenticated submissions)
        $isSpam     = false;
        $spamReason = null;

        if (! $isAnonymous && $code) {
            [$isSpam, $spamReason] = $this->detectSpam(
                $title . ' ' . $data['description'],
                $code->roster_contact_id,
                $contact?->spam_pardoned_at
            );

            if ($isSpam) {
                $assignee = null;
            }
        }

        $publicId = $this->generatePublicId();

        // Atomic: lock the access code row, re-check open issues, create the issue,
        // and stamp used_at — all in one transaction to prevent concurrent duplicates.
        $openIssueConflict = false;
        $issue = DB::transaction(function () use (
            &$openIssueConflict, $code, $isAnonymous, $isSpam, $spamReason,
            $school, $branchId, $publicId, $title, $data, $categoryId
        ) {
            if (! $isAnonymous && $code) {
                // Lock this contact's code row to serialize concurrent submissions
                AccessCode::where('tenant_id', tenant('id'))
                    ->where('id', $code->id)
                    ->lockForUpdate()
                    ->first();

                if (Issue::where('tenant_id', tenant('id'))
                    ->where('roster_contact_id', $code->roster_contact_id)
                    ->where('status', '!=', 'closed')
                    ->exists()) {
                    $openIssueConflict = true;
                    return null;
                }
            }

            $issue = Issue::create([
                'tenant_id'         => tenant('id'),
                'school_id'         => $school->id,
                'branch_id'         => $branchId,
                'public_id'         => $publicId,
                'source_role'       => $isAnonymous ? null : optional($code->contact)->role,
                'roster_contact_id' => $isAnonymous ? null : $code->roster_contact_id,
                'title'             => $title,
                'description'       => $data['description'],
                'status'            => $isSpam ? 'closed' : 'new',
                'priority'          => 'medium',
                'issue_category_id' => $categoryId,
                'is_spam'           => $isSpam,
                'spam_reason'       => $spamReason,
                'is_anonymous'      => $isAnonymous,
                'submission_type'   => 'complaint',
            ]);

            // Stamp the code as used atomically with issue creation (non-spam only)
            if (! $isAnonymous && $code && ! $isSpam) {
                $code->update(['used_at' => now()]);
            }

            return $issue;
        });

        if ($openIssueConflict) {
            return back()->withErrors(['code' => 'You already have an open submission. Please wait for it to be resolved before submitting a new one, or close it first.'])->withInput();
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

        // Notifications + email for non-spam authenticated submissions
        if (! $isAnonymous && ! $isSpam && $assignee) {
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
                ->where('roster_contact_id', $code->roster_contact_id)
                ->where('is_spam', true)
                ->when($contact?->spam_pardoned_at, fn ($q) => $q->where('created_at', '>', $contact->spam_pardoned_at))
                ->count();

            if ($totalSpam >= 5) {
                AccessCode::where('tenant_id', tenant('id'))
                    ->where('roster_contact_id', $code->roster_contact_id)
                    ->delete();

                RosterContact::where('id', $code->roster_contact_id)
                    ->update(['revoke_reason' => 'Auto-revoked: reached ' . $totalSpam . ' spam submissions']);

                return redirect()->route('tenant.public.home')
                    ->withErrors(['code' => 'Your access has been revoked due to repeated violations.']);
            }
        }

        // Attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
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
        }

        // Fire AI analysis event
        IssueCreated::dispatch($issue->id, $issue->tenant_id, $issue->description, $issue->title, $categoryId);

        // Confirmation email to contact
        if (! $isAnonymous && ! $isSpam && $contact?->email) {
            $trackingUrl = route('tenant.public.status', ['public_id' => $issue->public_id]);
            Mail::to($contact->email)->queue(new IssueReceivedMail(
                $issue,
                $contact->name,
                $school->name ?? 'School',
                $trackingUrl,
            ));
        }

        // In-app notifications
        if (! $isSpam) {
            $contactName   = $isAnonymous ? 'Anonymous' : ($contact?->name ?? 'A contact');
            $notification  = new NewIssueNotification($issue, $contactName);

            if (! $isAnonymous && $assignee) {
                $assignee->notify($notification);
            }

            User::role('admin')
                ->where('tenant_id', tenant('id'))
                ->each(fn ($admin) => $admin->notify($notification));
        }

        $thankyou = $school->setting('thankyou_message') ?: 'Your submission has been received successfully.';

        return redirect()->route('tenant.public.status', ['public_id' => $issue->public_id])
            ->with('ok', $thankyou . ' Tracking ID: ' . $issue->public_id);
    }

    private function detectSpam(string $text, int $contactId, ?\Illuminate\Support\Carbon $pardonedAt = null): array
    {
        $score   = 0;
        $reasons = [];

        $wordCount = str_word_count(strip_tags($text));
        if ($wordCount < 4) {
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
