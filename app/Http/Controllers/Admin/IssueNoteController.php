<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\IssueNote;
use Illuminate\Http\Request;

class IssueNoteController extends Controller
{
    public function save(Request $request, Issue $issue)
    {
        abort_unless($issue->tenant_id === tenant('id'), 404);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        IssueNote::updateOrCreate(
            [
                'issue_id' => $issue->id,
                'user_id' => $request->user()->id,
            ],
            [
                'tenant_id' => tenant('id'),
                'content' => $data['content'],
            ]
        );

        return back()->with('ok', 'Note saved.');
    }

    public function destroy(Request $request, Issue $issue)
    {
        abort_unless($issue->tenant_id === tenant('id'), 404);

        IssueNote::where('issue_id', $issue->id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return back()->with('ok', 'Note cleared.');
    }
}
