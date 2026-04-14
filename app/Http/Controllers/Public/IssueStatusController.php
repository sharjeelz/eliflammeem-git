<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class IssueStatusController extends Controller
{
    // existing single-issue view by tracking code
    public function show(string $public_id)
    {
        $issue = Issue::where('tenant_id', tenant('id'))
            ->where('public_id', $public_id)
            ->with([
                'branch',
                'school',
                'issueCategory',
                'messages'    => fn ($q) => $q->where('is_internal', false)->with('author'),
                'attachments' => fn ($q) => $q->whereNull('issue_message_id'),
            ])
            ->first();

        if (! $issue) {
            return redirect('/')->with('track_error', 'No issue found for that tracking ID.');
        }

        $code = null;
        if ($issue->roster_contact_id) {
            $code = AccessCode::where('tenant_id', tenant('id'))
                ->where('roster_contact_id', $issue->roster_contact_id)
                ->value('code');
        }

        return view('tenant.public.status', compact('issue', 'code'));
    }

    // NEW: list all issues for a parent/teacher/admin via their Access Code
    public function listByCode(string $code)
    {
        $access = \App\Models\AccessCode::where('tenant_id', tenant('id'))
            ->where('code', $code)
            ->first();

        if (! $access) {
            // Maybe the user entered a tracking ID (public_id) instead of an access code — try that
            $byPublicId = \App\Models\Issue::where('tenant_id', tenant('id'))
                ->where('public_id', strtoupper($code))
                ->first();
            if ($byPublicId) {
                return redirect()->route('tenant.public.status', ['public_id' => $byPublicId->public_id]);
            }

            return redirect('/')->with('track_error', 'Code not found. Please check and try again.');
        }

        $issues = \App\Models\Issue::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $access->roster_contact_id)
            ->with(['branch', 'school'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $spamCount = \App\Models\Issue::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $access->roster_contact_id)
            ->where('is_spam', true)
            ->count();

        return view('tenant.public.my_issues_index', [
            'code'      => $code,
            'issues'    => $issues,
            'spamCount' => $spamCount,
        ]);
    }

    /** Valid reasons a contact may give when self-closing an issue. */
    public const CONTACT_CLOSE_REASONS = [
        'fixed'               => 'Issue has been fixed',
        'no_longer_exists'    => 'Issue no longer exists',
        'mistaken_submission' => 'Submitted by mistake',
    ];

    public function close(Request $request, string $public_id)
    {
        $request->validate([
            'code'         => ['required', 'string'],
            'close_reason' => ['required', 'string', Rule::in(array_keys(self::CONTACT_CLOSE_REASONS))],
        ]);

        $issue = Issue::where('tenant_id', tenant('id'))
            ->where('public_id', $public_id)
            ->first();

        if (! $issue) {
            return back()->withErrors(['code' => 'Issue not found.']);
        }

        $access = AccessCode::where('tenant_id', tenant('id'))
            ->where('code', $request->code)
            ->where('roster_contact_id', $issue->roster_contact_id)
            ->first();

        if (! $access) {
            return back()->withErrors(['code' => 'Invalid access code for this issue.']);
        }

        if ($issue->status === 'closed') {
            return back()->with('ok', 'Issue is already closed.');
        }

        $prevStatus = $issue->status;
        $issue->update([
            'status'       => 'closed',
            'close_reason' => $request->close_reason,
        ]);

        // Reset the access code so the contact can submit a new issue
        $access->update(['used_at' => null]);

        $contact = RosterContact::where('id', $access->roster_contact_id)->first(['name', 'role']);

        IssueActivity::create([
            'tenant_id' => tenant('id'),
            'issue_id'  => $issue->id,
            'actor_id'  => null,
            'type'      => 'status_changed',
            'data'      => [
                'from'         => $prevStatus,
                'to'           => 'closed',
                'by_contact'   => true,
                'contact_name' => $contact?->name,
                'contact_role' => $contact?->role,
                'close_reason' => $request->close_reason,
                'close_reason_label' => self::CONTACT_CLOSE_REASONS[$request->close_reason] ?? $request->close_reason,
            ],
        ]);

        // CSAT survey — send once on first close if contact has email
        $fullContact = RosterContact::find($access->roster_contact_id);
        if ($fullContact && $fullContact->email && ! CsatResponse::where('issue_id', $issue->id)->exists()) {
            $schoolName = School::where('tenant_id', tenant('id'))->value('name') ?? 'School';
            $csat = CsatResponse::create([
                'tenant_id'     => tenant('id'),
                'issue_id'      => $issue->id,
                'token'         => Str::random(48),
                'email_sent_at' => now(),
            ]);
            Mail::to($fullContact->email)->queue(
                new CsatSurveyMail($issue, $csat->token, $schoolName, $fullContact->name, request()->getSchemeAndHttpHost())
            );
        }

        return redirect()
            ->route('tenant.public.status', ['public_id' => $public_id])
            ->with('ok', 'Issue closed. You can now submit a new one.');
    }

    public function reopen(Request $request, string $public_id)
    {
        $request->validate(['code' => ['required', 'string']]);

        $issue = Issue::where('tenant_id', tenant('id'))
            ->where('public_id', $public_id)
            ->first();

        if (! $issue || $issue->status !== 'closed') {
            return back()->with('ok', 'This issue is not closed.');
        }

        $access = AccessCode::where('tenant_id', tenant('id'))
            ->where('code', $request->code)
            ->where('roster_contact_id', $issue->roster_contact_id)
            ->with('contact')
            ->first();

        if (! $access) {
            return back()->withErrors(['code' => 'Invalid access code for this issue.']);
        }

        // Prevent reopening if the contact already has another open issue
        $hasOpen = Issue::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $issue->roster_contact_id)
            ->where('id', '!=', $issue->id)
            ->whereNotIn('status', ['closed'])
            ->exists();

        if ($hasOpen) {
            return back()->withErrors(['code' => 'You already have an open issue. Please close it before reopening this one.']);
        }

        $newStatus = $issue->assigned_user_id ? 'in_progress' : 'new';
        $issue->update(['status' => $newStatus]);

        $contact = $access->contact;

        IssueActivity::create([
            'tenant_id' => tenant('id'),
            'issue_id'  => $issue->id,
            'actor_id'  => null,
            'type'      => 'status_changed',
            'data'      => [
                'from'         => 'closed',
                'to'           => $newStatus,
                'by_contact'   => true,
                'contact_name' => $contact?->name,
                'contact_role' => $contact?->role,
            ],
        ]);

        // Notify assignee + admins that the contact reopened the issue
        if ($issue->assigned_user_id) {
            $assignee = User::find($issue->assigned_user_id);
            $assignee?->notify(new \App\Notifications\ContactRepliedNotification(
                $issue, $contact?->name ?? 'Contact', 'Issue has been reopened.'
            ));
        }

        User::role('admin')->where('tenant_id', tenant('id'))->each(
            fn ($admin) => $admin->notify(new \App\Notifications\ContactRepliedNotification(
                $issue, $contact?->name ?? 'Contact', 'Issue has been reopened.'
            ))
        );

        return redirect()
            ->route('tenant.public.status', ['public_id' => $public_id])
            ->with('ok', 'Issue reopened. The team has been notified and will follow up.');
    }

    public function anonymousFollowup(Request $request, string $public_id)
    {
        $request->validate([
            'message'               => ['required', 'string', 'max:3000'],
            'cf-turnstile-response' => [app()->environment('local', 'testing') ? 'nullable' : 'required', new \App\Rules\ValidTurnstile],
        ]);

        $issue = Issue::where('tenant_id', tenant('id'))
            ->where('public_id', $public_id)
            ->where('is_anonymous', true)
            ->with('assignedTo')
            ->first();

        if (! $issue) {
            return back()->withErrors(['message' => 'Issue not found.']);
        }

        if ($issue->status === 'closed') {
            return back()->withErrors(['message' => 'This issue is closed and cannot receive replies.']);
        }

        IssueMessage::create([
            'tenant_id'   => tenant('id'),
            'issue_id'    => $issue->id,
            'sender'      => 'parent',
            'message'     => $request->message,
            'is_internal' => false,
            'author_type' => null,
            'author_id'   => null,
            'meta'        => ['anonymous_followup' => true],
        ]);

        $issue->forceFill(['last_activity_at' => now()])->save();

        IssueActivity::create([
            'tenant_id' => tenant('id'),
            'issue_id'  => $issue->id,
            'actor_id'  => null,
            'type'      => 'commented',
            'data'      => ['preview' => Str::limit($request->message, 120), 'by_contact' => true],
        ]);

        $notification = new ContactRepliedNotification($issue, 'Anonymous', $request->message);

        if ($issue->assignedTo) {
            $issue->assignedTo->notify($notification);
        }

        User::role('admin')->where('tenant_id', tenant('id'))->each(
            fn ($admin) => $admin->notify($notification)
        );

        return redirect()
            ->route('tenant.public.status', ['public_id' => $public_id])
            ->with('ok', 'Your follow-up has been sent.');
    }

    public function reply(Request $request, string $public_id)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $issue = Issue::where('tenant_id', tenant('id'))
            ->where('public_id', $public_id)
            ->with('assignedTo')
            ->first();

        if (! $issue) {
            return back()->withErrors(['message' => 'Issue not found.']);
        }

        if ($issue->status === 'closed') {
            return back()->withErrors(['message' => 'This issue is closed and cannot receive replies.']);
        }

        $access = AccessCode::where('tenant_id', tenant('id'))
            ->where('code', $request->code)
            ->where('roster_contact_id', $issue->roster_contact_id)
            ->with('contact')
            ->first();

        if (! $access || ! $access->contact) {
            return back()->withErrors(['message' => 'Invalid access code.']);
        }

        $contact = $access->contact;

        IssueMessage::create([
            'tenant_id' => tenant('id'),
            'issue_id' => $issue->id,
            'sender' => $contact->role,
            'message' => $request->message,
            'is_internal' => false,
            'meta' => ['actor_name' => $contact->name],
            'author_type' => RosterContact::class,
            'author_id' => $contact->id,
        ]);

        $issue->forceFill(['last_activity_at' => now()])->save();

        IssueActivity::create([
            'tenant_id' => tenant('id'),
            'issue_id'  => $issue->id,
            'actor_id'  => null,
            'type'      => 'commented',
            'data'      => ['preview' => Str::limit($request->message, 120), 'by_contact' => true, 'contact_name' => $contact->name],
        ]);

        // Notify the assignee + admins
        $notification = new ContactRepliedNotification($issue, $contact->name, $request->message);

        if ($issue->assignedTo) {
            $issue->assignedTo->notify($notification);
        }

        User::role('admin')->where('tenant_id', tenant('id'))->each(
            fn ($admin) => $admin->notify($notification)
        );

        return redirect()
            ->route('tenant.public.status', ['public_id' => $public_id])
            ->with('ok', 'Reply sent.');
    }
}
