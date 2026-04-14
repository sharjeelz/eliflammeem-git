<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $tenant     = tenancy()->tenant;
        $currentKey = $tenant?->plan ?? 'starter';
        $plans      = Plan::orderByRaw("CASE key WHEN 'starter' THEN 1 WHEN 'growth' THEN 2 WHEN 'pro' THEN 3 WHEN 'enterprise' THEN 4 ELSE 5 END")->get();
        $current    = $plans->firstWhere('key', $currentKey) ?? $plans->first();

        return view('tenant.admin.plan.index', compact('plans', 'current', 'currentKey', 'tenant'));
    }
}
