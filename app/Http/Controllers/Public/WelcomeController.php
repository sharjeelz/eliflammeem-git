<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\IssueCategory;
use App\Models\SchoolAnnouncement;

class WelcomeController extends Controller
{
    public function index()
    {
        $categories = IssueCategory::where('tenant_id', tenant('id'))->get();

        $announcements = SchoolAnnouncement::where('tenant_id', tenant('id'))
            ->published()
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return view('tenant.public.home', compact('categories', 'announcements'));
    }
}
