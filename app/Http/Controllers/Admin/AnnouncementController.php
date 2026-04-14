<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IssueCategory;
use App\Models\SchoolAnnouncement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $announcements = SchoolAnnouncement::where('tenant_id', tenant('id'))
            ->with('category:id,name', 'author:id,name')
            ->orderByRaw('published_at DESC NULLS FIRST')
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('tenant.admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $categories = IssueCategory::where('tenant_id', tenant('id'))->get();

        return view('tenant.admin.announcements.create', compact('categories'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:200'],
            'body'         => ['required', 'string', 'max:5000'],
            'category_id'  => ['nullable', 'integer', 'exists:issue_categories,id'],
            'published_at' => ['nullable', 'date'],
        ]);

        SchoolAnnouncement::create([
            'tenant_id'        => tenant('id'),
            'title'            => $data['title'],
            'body'             => $data['body'],
            'issue_category_id' => $data['category_id'] ?? null,
            'published_at'     => $data['published_at'] ?? null,
            'created_by'       => auth()->id(),
        ]);

        return redirect()->route('tenant.admin.announcements.index')
            ->with('success', 'Announcement created.');
    }

    public function edit(SchoolAnnouncement $announcement)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        abort_unless($announcement->tenant_id === tenant('id'), 404);

        $categories = IssueCategory::where('tenant_id', tenant('id'))->get();

        return view('tenant.admin.announcements.edit', compact('announcement', 'categories'));
    }

    public function update(Request $request, SchoolAnnouncement $announcement)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        abort_unless($announcement->tenant_id === tenant('id'), 404);

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:200'],
            'body'         => ['required', 'string', 'max:5000'],
            'category_id'  => ['nullable', 'integer', 'exists:issue_categories,id'],
            'published_at' => ['nullable', 'date'],
        ]);

        $announcement->update([
            'title'            => $data['title'],
            'body'             => $data['body'],
            'issue_category_id' => $data['category_id'] ?? null,
            'published_at'     => $data['published_at'] ?? null,
        ]);

        return redirect()->route('tenant.admin.announcements.index')
            ->with('success', 'Announcement updated.');
    }

    public function destroy(SchoolAnnouncement $announcement)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        abort_unless($announcement->tenant_id === tenant('id'), 404);

        $announcement->delete();

        return redirect()->route('tenant.admin.announcements.index')
            ->with('success', 'Announcement deleted.');
    }

    public function publish(SchoolAnnouncement $announcement)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        abort_unless($announcement->tenant_id === tenant('id'), 404);

        $announcement->update(['published_at' => now()]);

        return back()->with('success', 'Announcement published.');
    }

    public function draft(SchoolAnnouncement $announcement)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        abort_unless($announcement->tenant_id === tenant('id'), 404);

        $announcement->update(['published_at' => null]);

        return back()->with('success', 'Announcement moved to draft.');
    }
}
