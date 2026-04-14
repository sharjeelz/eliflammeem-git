<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\IssueActivity;
use App\Models\User;
use App\Notifications\ContactRepliedNotification;
use Illuminate\Http\Request;

class ReopenIssueController extends Controller
{
    /**
     * Parent clicks "Still a problem?" from the resolved email.
     * Validates the single-use token and reopens the issue.
     */
    public function __invoke(Request $request, string $public_id)
    {
        $token = $request->query('token');

        $issue = Issue::where('tenant_id', tenant('id'))
            ->where('public_id', $public_id)
            ->first();

        // Token missing or issue not found
        if (! $issue || ! $token) {
            return view('tenant.public.still_problem', [
                'success' => false,
                'message' => 'Invalid link. Please contact the school directly.',
            ]);
        }

        // Token already used or wrong
        if (! $issue->reopen_token || ! hash_equals($issue->reopen_token, $token)) {
            return view('tenant.public.still_problem', [
                'success' => false,
                'message' => $issue->status === 'closed'
                    ? 'This issue has already been closed by the school.'
                    : 'This link has already been used or has expired.',
            ]);
        }

        // Issue must still be in resolved status for this to work
        if ($issue->status !== 'resolved') {
            return view('tenant.public.still_problem', [
                'success' => false,
                'message' => 'This issue is no longer in resolved status.',
            ]);
        }

        $from = $issue->status;
        $to   = $issue->assigned_user_id ? 'in_progress' : 'new';

        $issue->status        = $to;
        $issue->status_entered_at = now();
        $issue->last_activity_at  = now();
        $issue->reopen_token  = null; // single-use
        $issue->save();

        IssueActivity::create([
            'tenant_id' => tenant('id'),
            'issue_id'  => $issue->id,
            'actor_id'  => null,
            'type'      => 'status_changed',
            'data'      => [
                'from'         => $from,
                'to'           => $to,
                'by_contact'   => true,
                'note'         => 'Parent indicated the problem is still not resolved.',
            ],
        ]);

        // Notify assignee + admins
        $notification = new ContactRepliedNotification(
            $issue,
            $issue->roasterContact?->name ?? 'Contact',
            'Parent says the problem is still not resolved.'
        );

        if ($issue->assigned_user_id) {
            User::find($issue->assigned_user_id)?->notify($notification);
        }

        User::role('admin')->where('tenant_id', tenant('id'))->each(
            fn ($admin) => $admin->notify($notification)
        );

        return view('tenant.public.still_problem', [
            'success' => true,
            'message' => "We've received your feedback. The team has been notified and will follow up with you shortly.",
            'issue'   => $issue,
        ]);
    }
}
