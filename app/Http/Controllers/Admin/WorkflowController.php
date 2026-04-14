<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CsatSurveyMail;
use App\Mail\IssueAssignedMail;
use App\Mail\IssueCommentedMail;
use App\Mail\IssueStatusChangedMail;
use App\Models\CsatResponse;
use App\Models\Issue;
use App\Models\IssueActivity;
use App\Models\IssueMessage;
use App\Models\School;
use App\Models\User;
use App\Notifications\IssueAssignedNotification;
use App\Notifications\IssueCommentedNotification;
use App\Services\ExpoPushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

class WorkflowController extends Controller
{
    public function __construct()
    {
        // make sure spatie team is tenant-scoped
        app(PermissionRegistrar::class)->setPermissionsTeamId(tenant('id'));
    }

    /** Assign issue to a staff member */
    public function assign(Request $request, Issue $issue)
    {
        abort_unless($issue->tenant_id === tenant('id'), 404);

        $actor = $request->user()->load('branches');

        $this->authorize('assign', $issue);

        $data = $request->validate([
            'assigned_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('tenant_id', tenant('id'))),
            ],
        ]);

        $assignee = User::where('tenant_id', tenant('id'))
            ->with('branches')
            ->findOrFail($data['assigned_user_id']);

        // Assignee must belong to the same branch as the issue (enforced for all roles).
        $assigneeBranchIds = $assignee->branches->pluck('id')->toArray();
        if ($issue->branch_id && ! in_array($issue->branch_id, $assigneeBranchIds, false)) {
            abort(422, 'This person does not belong to the issue\'s branch.');
        }

        // Branch managers may only assign to staff (not other managers) in their own branches.
        if ($actor->hasRole('branch_manager')) {
            $actorBranchIds = $actor->branches->pluck('id')->toArray();

            if (! $assignee->hasRole('staff') || count(array_intersect($actorBranchIds, $assigneeBranchIds)) === 0) {
                abort(422, 'You can only assign issues to staff in your branch(es).');
            }
        }

        DB::transaction(function () use ($issue, $actor, $assignee) {
            $prev = $issue->assigned_user_id;

            $issue->assigned_user_id = $assignee->id;

            // Clear the branch-move unassigned flag if present
            if (isset($issue->meta['unassigned_reason'])) {
                $meta = $issue->meta;
                unset($meta['unassigned_reason']);
                $issue->meta = $meta;
            }

            if ($issue->status === 'new' && ! $issue->first_response_at) {
                $issue->first_response_at = now();
            }

            $issue->last_activity_at = now();
            $issue->save();

            IssueActivity::create([
                'tenant_id' => tenant('id'),
                'issue_id'  => $issue->id,
                'actor_id'  => $actor->id,
                'type'      => 'assigned',
                'data'      => ['from' => $prev, 'to' => $assignee->id],
            ]);
        });

        // Notify the assignee if they are not the actor
        if ($assignee->id !== $actor->id) {
            // In-app notification
            $assignee->notify(new IssueAssignedNotification($issue, $actor));

            // Email notification
            if ($assignee->email) {
                $schoolName = School::where('tenant_id', tenant('id'))->value('name') ?? 'School';
                $issue->load('branch');
                Mail::to($assignee->email)->queue(new IssueAssignedMail($issue, $assignee, $schoolName));
            }
        }

        return back()->with('ok', 'Assigned successfully.');
    }

    /** Change status with transition rules */
    public function updateStatus(Request $request, Issue $issue)
    {
        abort_unless($issue->tenant_id === tenant('id'), 404);

        $user = $request->user();

        $data = $request->validate([
            'status'     => ['required', Rule::in(['new', 'in_progress', 'resolved', 'closed'])],
            'close_note' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $from = $issue->status;
        $to   = $data['status'];
        $allowed = Issue::allowedTransitions();

        if (! in_array($to, $allowed[$from] ?? [], true)) {
            abort(422, 'Invalid status transition.');
        }

        $this->authorize('updateStatus', [$issue, $to]);

        // Manual close requires a resolution note
        if ($to === 'closed' && empty(trim((string) ($data['close_note'] ?? '')))) {
            return back()->withErrors(['close_note' => 'Please provide a resolution note before closing.'])->withInput();
        }

        $issue->status = $to;
        $issue->status_entered_at = now();

        if ($to === 'in_progress' && ! $issue->first_response_at) {
            $issue->first_response_at = now();
        }
        if ($to === 'resolved') {
            $issue->resolved_at  = now();
            $issue->reopen_token = Str::random(48);
        }
        if ($to === 'closed') {
            $issue->reopen_token = null;
            $issue->close_note   = trim($data['close_note']);
        }

        $issue->last_activity_at = now();
        $issue->save();

        IssueActivity::create([
            'issue_id' => $issue->id,
            'actor_id' => $user->id,
            'type'     => 'status_changed',
            'data'     => array_filter([
                'from'       => $from,
                'to'         => $to,
                'close_note' => $to === 'closed' ? trim($data['close_note']) : null,
            ]),
        ]);

        // When closed, reset the contact's access code so they can submit again
        if ($to === 'closed' && $issue->roster_contact_id) {
            \App\Models\AccessCode::where('tenant_id', tenant('id'))
                ->where('roster_contact_id', $issue->roster_contact_id)
                ->whereNotNull('used_at')
                ->update(['used_at' => null]);
        }

        // Push notification to parent app when status changes to in_progress or resolved
        if (! $issue->is_spam && in_array($to, ['in_progress', 'resolved', 'closed'], true)) {
            $issue->load('roasterContact');
            $pushContact = $issue->roasterContact;
            if ($pushContact?->expo_push_token) {
                $statusLabel = match ($to) {
                    'in_progress' => 'Your issue is being reviewed',
                    'resolved'    => 'Your issue has been resolved',
                    'closed'      => 'Your issue has been closed',
                    default       => 'Your issue status has been updated',
                };
                ExpoPushService::send(
                    expoPushToken: $pushContact->expo_push_token,
                    title: $statusLabel,
                    body: $issue->title,
                    data: ['public_id' => $issue->public_id, 'type' => 'status_change'],
                );
            }
        }

        // Email the contact when their issue is resolved or closed (never for spam)
        if (! $issue->is_spam && in_array($to, ['resolved', 'closed'], true)) {
            $issue->load('roasterContact');
            $contact = $issue->roasterContact;
            if ($contact && $contact->email) {
                $schoolName = School::where('tenant_id', tenant('id'))->value('name') ?? 'School';
                Mail::to($contact->email)->queue(
                    new IssueStatusChangedMail($issue, $from, $to, $schoolName, $contact->name, request()->getSchemeAndHttpHost())
                );

                // CSAT survey — send once on first close, skip for spam issues
                if ($to === 'closed' && ! $issue->is_spam && ! CsatResponse::where('issue_id', $issue->id)->exists()) {
                    $csat = CsatResponse::create([
                        'tenant_id'     => tenant('id'),
                        'issue_id'      => $issue->id,
                        'token'         => Str::random(48),
                        'email_sent_at' => now(),
                    ]);
                    Mail::to($contact->email)->queue(
                        new CsatSurveyMail($issue, $csat->token, $schoolName, $contact->name, request()->getSchemeAndHttpHost())
                    );
                }
            }
        }

        return back()->with('ok', "Status changed: {$from} → {$to}");
    }

    /** Change priority */
    public function updatePriority(Request $request, Issue $issue)
    {
        abort_unless($issue->tenant_id === tenant('id'), 404);

        $this->authorize('updatePriority', $issue);

        $data = $request->validate([
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
        ]);

        $from = $issue->priority;
        $to = $data['priority'];

        if ($from === $to) {
            return back()->with('ok', 'Priority unchanged.');
        }

        $issue->update(['priority' => $to]);

        IssueActivity::create([
            'tenant_id' => tenant('id'),
            'issue_id'  => $issue->id,
            'actor_id'  => $request->user()->id,
            'type'      => 'priority_changed',
            'data'      => ['from' => $from, 'to' => $to],
        ]);

        return back()->with('ok', "Priority changed: {$from} → {$to}");
    }

    /** Change category */
    public function updateCategory(Request $request, Issue $issue)
    {
        abort_unless($issue->tenant_id === tenant('id'), 404);

        $this->authorize('updatePriority', $issue);

        $data = $request->validate([
            'issue_category_id' => [
                'nullable',
                Rule::exists('issue_categories', 'id')->where('tenant_id', tenant('id')),
            ],
        ]);

        $issue->load('issueCategory');
        $oldName = $issue->issueCategory?->name ?? '—';
        $newCatId = $data['issue_category_id'] ?? null;

        if ((string) ($issue->issue_category_id ?? '') === (string) ($newCatId ?? '')) {
            return back()->with('ok', 'Category unchanged.');
        }

        $issue->update(['issue_category_id' => $newCatId]);
        $issue->refresh()->load('issueCategory');
        $newName = $issue->issueCategory?->name ?? '—';

        IssueActivity::create([
            'tenant_id' => tenant('id'),
            'issue_id'  => $issue->id,
            'actor_id'  => $request->user()->id,
            'type'      => 'category_changed',
            'data'      => ['from' => $oldName, 'to' => $newName],
        ]);

        return back()->with('ok', "Category changed: {$oldName} → {$newName}");
    }

    /** Change submission type (admin override) */
    public function updateSubmissionType(Request $request, Issue $issue)
    {
        abort_unless($issue->tenant_id === tenant('id'), 404);

        $this->authorize('updatePriority', $issue);

        $data = $request->validate([
            'submission_type' => ['required', Rule::in(['complaint', 'suggestion', 'compliment'])],
        ]);

        $from = $issue->submission_type;
        $to   = $data['submission_type'];

        if ($from === $to) {
            return back()->with('ok', 'Type unchanged.');
        }

        $issue->update(['submission_type' => $to]);

        IssueActivity::create([
            'tenant_id' => tenant('id'),
            'issue_id'  => $issue->id,
            'actor_id'  => $request->user()->id,
            'type'      => 'type_changed',
            'data'      => ['from' => $from, 'to' => $to],
        ]);

        return back()->with('ok', "Submission type changed: {$from} → {$to}");
    }

    /** Add a staff/admin comment */
    public function comment(Request $request, Issue $issue)
    {
        abort_unless($issue->tenant_id === tenant('id'), 404);

        $actor = $request->user();

        $this->authorize('comment', $issue);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'is_internal' => ['sometimes', 'boolean'],
        ]);

        $isInternal = (bool) ($data['is_internal'] ?? true);
        
        // Staff members can only send internal notes, not external replies to contacts
        if ($actor->hasRole('staff') && !$isInternal) {
            abort(403, 'Staff members cannot send external messages to contacts.');
        }
        
        $sender = $actor->hasRole('admin') ? 'admin' : 'teacher';

        IssueMessage::create([
            'tenant_id' => tenant('id'),
            'issue_id' => $issue->id,
            'sender' => $sender,
            'message' => $data['message'],
            'is_internal' => $isInternal,
            'meta' => ['actor_id' => $actor->id, 'actor_name' => $actor->name],
            'author_type' => User::class,
            'author_id' => $actor->id,
        ]);

        $issue->last_activity_at = now();

        // First external (contact-visible) reply counts as first response
        if (! $isInternal && ! $issue->first_response_at) {
            $issue->first_response_at = now();
        }

        $issue->save();

        IssueActivity::create([
            'issue_id' => $issue->id,
            'actor_id' => $actor->id,
            'type' => 'commented',
            'data' => ['preview' => Str::limit($data['message'], 120)],
        ]);

        $issue->load('assignedTo', 'branch');

        // Admin/BM comments → notify the assignee (staff)
        $assignee = $issue->assignedTo;
        if ($assignee && $assignee->id !== $actor->id) {
            $assignee->notify(new IssueCommentedNotification($issue, $actor, $data['message']));

            if ($assignee->email) {
                $schoolName = School::where('tenant_id', tenant('id'))->value('name') ?? 'School';
                Mail::to($assignee->email)->queue(
                    new IssueCommentedMail($issue, $actor, Str::limit($data['message'], 300), $schoolName)
                );
            }
        }

        // Staff comments → notify the branch manager + all admins
        if ($actor->hasRole('staff')) {
            $branchManager = User::defaultAssigneeForBranch($issue->branch_id);
            if ($branchManager && $branchManager->id !== $actor->id) {
                $branchManager->notify(new IssueCommentedNotification($issue, $actor, $data['message']));
            }

            $admins = User::role('admin')->where('tenant_id', tenant('id'))->get();
            foreach ($admins as $admin) {
                if ($admin->id !== $actor->id) {
                    $admin->notify(new IssueCommentedNotification($issue, $actor, $data['message']));
                }
            }
        }

        // Push notification to parent app for external (contact-visible) replies
        if (! $isInternal && $issue->roster_contact_id) {
            $issue->load('roasterContact');
            $pushContact = $issue->roasterContact;
            if ($pushContact?->expo_push_token) {
                ExpoPushService::send(
                    expoPushToken: $pushContact->expo_push_token,
                    title: 'New reply on your issue',
                    body: Str::limit($data['message'], 100),
                    data: ['public_id' => $issue->public_id, 'type' => 'new_reply'],
                );
            }
        }

        return back()->with('ok', 'Comment added.');
    }

    /** Delete a message and log the deletion */
    public function deleteMessage(Request $request, Issue $issue, IssueMessage $message)
    {
        abort_unless($issue->tenant_id === tenant('id'), 404);
        abort_unless($message->issue_id === $issue->id, 404);

        $actor = $request->user();

        $this->authorize('deleteMessage', $issue);

        // Non-admins can only delete their own messages
        if (! $actor->hasRole('admin')) {
            abort_unless(
                $message->author_type === User::class && $message->author_id === $actor->id,
                403,
                'You can only delete your own messages.'
            );
        }

        $preview = Str::limit($message->message ?? '', 120);
        $authorName = $message->meta['actor_name'] ?? 'Unknown';

        $message->delete();

        IssueActivity::create([
            'issue_id' => $issue->id,
            'actor_id' => $actor->id,
            'type' => 'message_deleted',
            'data' => [
                'preview' => $preview,
                'author_name' => $authorName,
                'deleted_by' => $actor->name,
            ],
        ]);

        return back()->with('ok', 'Message deleted.');
    }

    /** Unassign issue — admin only */
    public function unassign(Request $request, Issue $issue)
    {
        abort_unless($issue->tenant_id === tenant('id'), 404);
        abort_unless($request->user()->hasRole('admin'), 403);
        abort_unless($issue->assigned_user_id, 422, 'Issue is not assigned.');

        $prev = $issue->assigned_user_id;
        $prevName = $issue->assignedTo?->name;

        $issue->update([
            'assigned_user_id' => null,
            'last_activity_at' => now(),
        ]);

        IssueActivity::create([
            'issue_id' => $issue->id,
            'actor_id' => $request->user()->id,
            'type'     => 'assigned',
            'data'     => ['from' => $prev, 'to' => null, 'unassigned_name' => $prevName],
        ]);

        return back()->with('ok', 'Issue unassigned from ' . $prevName . '.');
    }

    /** Mark issue as spam (admin or branch_manager only) */
    public function markSpam(Request $request, Issue $issue)
    {
        abort_unless($issue->tenant_id === tenant('id'), 404);

        $actor = $request->user();
        abort_unless($actor->hasRole(['admin', 'branch_manager']), 403);

        $data = $request->validate([
            'spam_reason' => ['required', 'string', 'max:500'],
        ]);

        $previousAssigneeId   = $issue->assigned_user_id;
        $previousAssigneeName = $issue->assignedTo?->name;

        $prevStatus = $issue->status;

        $issue->update([
            'is_spam'          => true,
            'spam_reason'      => $data['spam_reason'],
            'assigned_user_id' => null,
            'status'           => 'closed',
            'last_activity_at' => now(),
        ]);

        IssueActivity::create([
            'issue_id' => $issue->id,
            'actor_id' => $actor->id,
            'type'     => 'spam_marked',
            'data'     => [
                'reason'               => $data['spam_reason'],
                'by'                   => $actor->name,
                'unassigned_user_id'   => $previousAssigneeId,
                'unassigned_user_name' => $previousAssigneeName,
            ],
        ]);

        // Auto-close silently — log status change but send no email and no CSAT
        if ($prevStatus !== 'closed') {
            IssueActivity::create([
                'issue_id' => $issue->id,
                'actor_id' => null,
                'type'     => 'status_changed',
                'data'     => ['from' => $prevStatus, 'to' => 'closed', 'note' => 'Auto-closed: marked as spam'],
            ]);
        }

        // Auto-revoke access code after 5 confirmed spams since last pardon
        if ($issue->roster_contact_id) {
            $rosterContact = \App\Models\RosterContact::find($issue->roster_contact_id);
            $spamCount = Issue::where('tenant_id', tenant('id'))
                ->where('roster_contact_id', $issue->roster_contact_id)
                ->where('is_spam', true)
                ->when($rosterContact?->spam_pardoned_at, fn ($q) => $q->where('created_at', '>', $rosterContact->spam_pardoned_at))
                ->count();

            if ($spamCount >= 5) {
                \App\Models\AccessCode::where('tenant_id', tenant('id'))
                    ->where('roster_contact_id', $issue->roster_contact_id)
                    ->delete();

                if ($rosterContact) {
                    $rosterContact->update(['revoke_reason' => 'Auto-revoked: reached ' . $spamCount . ' spam submissions']);
                }

                IssueActivity::create([
                    'issue_id' => $issue->id,
                    'actor_id' => null,
                    'type'     => 'note',
                    'data'     => ['note' => 'Access code auto-revoked: contact reached ' . $spamCount . ' spam submissions.'],
                ]);
            }
        }

        return back()->with('ok', 'Issue marked as spam.');
    }

    /** Remove spam flag from issue (admin or branch_manager only) */
    public function unmarkSpam(Request $request, Issue $issue)
    {
        abort_unless($issue->tenant_id === tenant('id'), 404);

        $actor = $request->user();
        abort_unless($actor->hasRole(['admin', 'branch_manager']), 403);

        $prevReason = $issue->spam_reason;

        $issue->update([
            'is_spam'     => false,
            'spam_reason' => null,
        ]);

        IssueActivity::create([
            'issue_id' => $issue->id,
            'actor_id' => $actor->id,
            'type'     => 'spam_cleared',
            'data'     => ['prev_reason' => $prevReason, 'by' => $actor->name],
        ]);

        return back()->with('ok', 'Spam flag removed.');
    }
}
