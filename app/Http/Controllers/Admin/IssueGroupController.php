<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DetectIssueGroups;
use App\Mail\IssueStatusChangedMail;
use App\Models\AccessCode;
use App\Models\CsatResponse;
use App\Models\Issue;
use App\Models\IssueActivity;
use App\Models\IssueGroup;
use App\Models\IssueGroupItem;
use App\Models\IssueMessage;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class IssueGroupController extends Controller
{
    /** List AI-detected groups, tabbed by status */
    public function index(Request $request)
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $tab = $request->get('tab', 'open');
        abort_unless(in_array($tab, ['open', 'dismissed', 'resolved']), 422);

        $query = IssueGroup::where('tenant_id', tenant('id'))
            ->where('status', $tab)
            ->with(['category:id,name', 'branch:id,name'])
            ->orderByDesc('issue_count')
            ->orderByRaw("CASE confidence WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderByDesc('created_at');

        if ($tab === 'open') {
            $query->where('issue_count', '>=', 2);
        }

        $groups = $query->paginate(20)->withQueryString();

        $counts = IssueGroup::where('tenant_id', tenant('id'))
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('tenant.admin.issue_groups.index', compact('groups', 'tab', 'counts'));
    }

    /** Review a single group before resolving */
    public function show(Request $request, IssueGroup $issueGroup)
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        abort_unless($issueGroup->tenant_id === tenant('id'), 404);
        abort_unless($issueGroup->status === 'open', 422);

        $issues = Issue::whereIn('id',
            IssueGroupItem::where('issue_group_id', $issueGroup->id)
                ->whereNull('removed_at')
                ->pluck('issue_id')
        )
        ->with(['roasterContact:id,name,role', 'branch:id,name', 'issueCategory:id,name'])
        ->whereIn('status', ['new', 'in_progress'])
        ->get();

        return view('tenant.admin.issue_groups.show', compact('issueGroup', 'issues'));
    }

    /** Remove a single issue from the group (admin keeps reviewing others) */
    public function removeIssue(Request $request, IssueGroup $issueGroup, Issue $issue)
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        abort_unless($issueGroup->tenant_id === tenant('id'), 404);
        abort_unless($issue->tenant_id === tenant('id'), 404);

        IssueGroupItem::where('issue_group_id', $issueGroup->id)
            ->where('issue_id', $issue->id)
            ->update(['removed_at' => now()]);

        $remaining = IssueGroupItem::where('issue_group_id', $issueGroup->id)
            ->whereNull('removed_at')
            ->count();

        $issueGroup->update(['issue_count' => $remaining]);

        // Auto-dismiss if fewer than 2 issues remain
        if ($remaining < 2) {
            $issueGroup->update(['status' => 'dismissed']);
            return redirect()->route('tenant.admin.issue_groups.index')
                ->with('ok', 'Group dismissed — too few issues remaining.');
        }

        return back()->with('ok', 'Issue removed from group.');
    }

    /** Dismiss the entire group without resolving */
    public function dismiss(Request $request, IssueGroup $issueGroup)
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        abort_unless($issueGroup->tenant_id === tenant('id'), 404);
        abort_unless($issueGroup->status === 'open', 422);

        $issueGroup->update(['status' => 'dismissed']);

        return redirect()->route('tenant.admin.issue_groups.index')
            ->with('ok', "Group \"{$issueGroup->label}\" dismissed.");
    }

    /** Reopen a dismissed group */
    public function reopen(Request $request, IssueGroup $issueGroup)
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        abort_unless($issueGroup->tenant_id === tenant('id'), 404);
        abort_unless($issueGroup->status === 'dismissed', 422);

        $issueGroup->update(['status' => 'open']);

        return redirect()->route('tenant.admin.issue_groups.index')
            ->with('ok', "Group \"{$issueGroup->label}\" reopened.");
    }

    /** Bulk resolve all active issues in the group */
    public function bulkResolve(Request $request, IssueGroup $issueGroup)
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        abort_unless($issueGroup->tenant_id === tenant('id'), 404);
        abort_unless($issueGroup->status === 'open', 422);

        $data = $request->validate([
            'message'    => ['required', 'string', 'max:3000'],
            'close_issues' => ['sometimes', 'boolean'],
        ]);

        $actor      = $request->user();
        $toStatus   = ($data['close_issues'] ?? false) ? 'closed' : 'resolved';
        $message    = $data['message'];
        $schoolName = School::where('tenant_id', tenant('id'))->value('name') ?? 'School';

        $issueIds = IssueGroupItem::where('issue_group_id', $issueGroup->id)
            ->whereNull('removed_at')
            ->pluck('issue_id');

        $issues = Issue::whereIn('id', $issueIds)
            ->whereIn('status', ['new', 'in_progress'])
            ->where('is_spam', false)
            ->with('roasterContact')
            ->get();

        DB::transaction(function () use ($issues, $actor, $toStatus, $message, $schoolName, $issueGroup) {
            foreach ($issues as $issue) {
                $from = $issue->status;

                $issue->status            = $toStatus;
                $issue->status_entered_at = now();
                $issue->last_activity_at  = now();
                if ($toStatus === 'resolved') {
                    $issue->resolved_at  = now();
                    $issue->reopen_token = Str::random(48);
                }
                if ($toStatus === 'closed') {
                    $issue->reopen_token = null;
                    $issue->close_note   = $message; // resolution message doubles as close note
                }
                $issue->save();

                // Visible reply to contact
                IssueMessage::create([
                    'tenant_id'   => tenant('id'),
                    'issue_id'    => $issue->id,
                    'sender'      => 'admin',
                    'message'     => $message,
                    'is_internal' => false,
                    'meta'        => ['actor_id' => $actor->id, 'actor_name' => $actor->name],
                    'author_type' => \App\Models\User::class,
                    'author_id'   => $actor->id,
                ]);

                IssueActivity::create([
                    'issue_id' => $issue->id,
                    'actor_id' => $actor->id,
                    'type'     => 'status_changed',
                    'data'     => array_filter([
                        'from'       => $from,
                        'to'         => $toStatus,
                        'close_note' => $toStatus === 'closed' ? $message : null,
                        'group_id'   => $issueGroup->id,
                        'group_label'=> $issueGroup->label,
                    ]),
                ]);

                // Reset access code so contact can resubmit after resolution
                if ($toStatus === 'closed' && $issue->roster_contact_id) {
                    AccessCode::where('tenant_id', tenant('id'))
                        ->where('roster_contact_id', $issue->roster_contact_id)
                        ->whereNotNull('used_at')
                        ->update(['used_at' => null]);
                }

                // Email contact
                $contact = $issue->roasterContact;
                if ($contact && $contact->email) {
                    Mail::to($contact->email)->queue(
                        new IssueStatusChangedMail($issue, $from, $toStatus, $schoolName, $contact->name)
                    );

                    // CSAT on close
                    if ($toStatus === 'closed' && ! CsatResponse::where('issue_id', $issue->id)->exists()) {
                        $csat = CsatResponse::create([
                            'tenant_id'     => tenant('id'),
                            'issue_id'      => $issue->id,
                            'token'         => Str::random(48),
                            'email_sent_at' => now(),
                        ]);
                        Mail::to($contact->email)->queue(
                            new \App\Mail\CsatSurveyMail(
                                $issue, $csat->token, $schoolName,
                                $contact->name, request()->getSchemeAndHttpHost()
                            )
                        );
                    }
                }
            }

            $issueGroup->update([
                'status'           => 'resolved',
                'resolved_message' => $message,
                'resolved_by'      => $actor->id,
                'resolved_at'      => now(),
                'issue_count'      => $issues->count(),
            ]);
        });

        return redirect()->route('tenant.admin.issue_groups.index')
            ->with('ok', "Bulk resolved {$issues->count()} issues in group \"{$issueGroup->label}\".");
    }

    /** Manually re-run group detection for this tenant (runs synchronously) */
    public function refresh(Request $request)
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        (new DetectIssueGroups(tenant('id')))->handle();

        return back()->with('ok', 'Group scan complete. Groups updated.');
    }
}
