<?php

namespace App\Services;

use App\Mail\CsatSurveyMail;
use App\Models\AccessCode;
use App\Models\CsatResponse;
use App\Models\Issue;
use App\Models\IssueActivity;
use App\Models\IssueMessage;
use App\Models\RosterContact;
use App\Models\School;
use App\Models\User;
use App\Notifications\ContactRepliedNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class IssueActionService
{
    /**
     * Valid reasons a contact may provide when self-closing an issue.
     */
    public const CONTACT_CLOSE_REASONS = [
        'fixed'               => 'Issue has been fixed',
        'no_longer_exists'    => 'Issue no longer exists',
        'mistaken_submission' => 'Submitted by mistake',
    ];

    /**
     * Close an issue on behalf of a RosterContact.
     *
     * @throws InvalidArgumentException  if close_reason is invalid
     * @throws RuntimeException          if the issue is already closed
     */
    public function close(
        Issue $issue,
        RosterContact $contact,
        string $reason,
        string $portalBaseUrl = ''
    ): Issue {
        if (! array_key_exists($reason, self::CONTACT_CLOSE_REASONS)) {
            throw new InvalidArgumentException('Invalid close reason.');
        }

        if ($issue->status === 'closed') {
            throw new RuntimeException('Issue is already closed.');
        }

        $prevStatus = $issue->status;
        $issue->update([
            'status'       => 'closed',
            'close_reason' => $reason,
        ]);

        // Reset access code so the contact can submit a new issue
        AccessCode::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->update(['used_at' => null]);

        IssueActivity::create([
            'tenant_id' => tenant('id'),
            'issue_id'  => $issue->id,
            'actor_id'  => null,
            'type'      => 'status_changed',
            'data'      => [
                'from'               => $prevStatus,
                'to'                 => 'closed',
                'by_contact'         => true,
                'contact_name'       => $contact->name,
                'contact_role'       => $contact->role,
                'close_reason'       => $reason,
                'close_reason_label' => self::CONTACT_CLOSE_REASONS[$reason],
            ],
        ]);

        // CSAT survey — send once on first close if contact has email
        if ($contact->email && ! CsatResponse::where('issue_id', $issue->id)->exists()) {
            $schoolName = School::where('tenant_id', tenant('id'))->value('name') ?? 'School';
            $csat       = CsatResponse::create([
                'tenant_id'     => tenant('id'),
                'issue_id'      => $issue->id,
                'token'         => Str::random(48),
                'email_sent_at' => now(),
            ]);
            $base = rtrim($portalBaseUrl, '/') ?: config('app.url');
            Mail::to($contact->email)->queue(
                new CsatSurveyMail($issue, $csat->token, $schoolName, $contact->name, $base)
            );
        }

        return $issue->fresh();
    }

    /**
     * Reopen a closed issue on behalf of a RosterContact.
     *
     * @throws RuntimeException  if issue is not closed or another open issue exists
     */
    public function reopen(Issue $issue, RosterContact $contact): Issue
    {
        if ($issue->status !== 'closed') {
            throw new RuntimeException('Issue is not closed.');
        }

        $hasOpen = Issue::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->where('id', '!=', $issue->id)
            ->whereNotIn('status', ['closed'])
            ->exists();

        if ($hasOpen) {
            throw new RuntimeException('You already have an open issue. Please close it before reopening this one.');
        }

        $newStatus = $issue->assigned_user_id ? 'in_progress' : 'new';
        $issue->update(['status' => $newStatus]);

        IssueActivity::create([
            'tenant_id' => tenant('id'),
            'issue_id'  => $issue->id,
            'actor_id'  => null,
            'type'      => 'status_changed',
            'data'      => [
                'from'         => 'closed',
                'to'           => $newStatus,
                'by_contact'   => true,
                'contact_name' => $contact->name,
                'contact_role' => $contact->role,
            ],
        ]);

        $notification = new ContactRepliedNotification($issue, $contact->name, 'Issue has been reopened.');

        if ($issue->assigned_user_id) {
            User::find($issue->assigned_user_id)?->notify($notification);
        }

        User::role('admin')->where('tenant_id', tenant('id'))
            ->each(fn ($admin) => $admin->notify($notification));

        return $issue->fresh();
    }

    /**
     * Add a reply message to an issue on behalf of a RosterContact.
     *
     * @throws RuntimeException  if issue is closed
     */
    public function addReply(Issue $issue, RosterContact $contact, string $message): IssueMessage
    {
        if ($issue->status === 'closed') {
            throw new RuntimeException('This issue is closed and cannot receive replies.');
        }

        $issueMessage = IssueMessage::create([
            'tenant_id'   => tenant('id'),
            'issue_id'    => $issue->id,
            'sender'      => $contact->role,
            'message'     => $message,
            'is_internal' => false,
            'meta'        => ['actor_name' => $contact->name],
            'author_type' => RosterContact::class,
            'author_id'   => $contact->id,
        ]);

        $issue->forceFill(['last_activity_at' => now()])->save();

        IssueActivity::create([
            'tenant_id' => tenant('id'),
            'issue_id'  => $issue->id,
            'actor_id'  => null,
            'type'      => 'commented',
            'data'      => [
                'preview'      => Str::limit($message, 120),
                'by_contact'   => true,
                'contact_name' => $contact->name,
            ],
        ]);

        $notification = new ContactRepliedNotification($issue, $contact->name, $message);

        $issue->load('assignedTo');
        if ($issue->assignedTo) {
            $issue->assignedTo->notify($notification);
        }

        User::role('admin')->where('tenant_id', tenant('id'))
            ->each(fn ($admin) => $admin->notify($notification));

        return $issueMessage;
    }
}
