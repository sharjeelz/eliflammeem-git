<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Issue;
use App\Models\IssueAiAnalysis;
use App\Models\IssueGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = tenant('id');

        // Date range (defaults to current month → today)
        $from = $request->date('from')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $to = $request->date('to')?->toDateString() ?? now()->toDateString();

        // Base scope — always filtered by date range
        $base = Issue::where('tenant_id', $tenantId)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        // Role-based scoping
        $user = $request->user();

        if ($user->hasRole('branch_manager')) {
            $branchIds = $user->branches()->pluck('branches.id')->all();
            $base->whereIn('branch_id', $branchIds ?: [-1]);
        } elseif ($user->hasRole('staff')) {
            $base->where('assigned_user_id', $user->id);
        }
        // Admin: no extra filter → sees all issues

        // KPIs
        $kpis = [
            'open' => (clone $base)->whereIn('status', ['new', 'in_progress'])->count(),
            'new_period' => (clone $base)->count(),
            'resolved_period' => (clone $base)->where('status', 'resolved')->count(),
            'closed_period' => (clone $base)->where('status', 'closed')->count(),
            'avg_resolution_hours' => optional((clone $base)
                ->whereNotNull('resolved_at')
                ->selectRaw('AVG(EXTRACT(EPOCH FROM (resolved_at - created_at))/3600.0) as hrs')
                ->first())->hrs,
            'avg_first_response_hours' => optional((clone $base)
                ->whereNotNull('first_response_at')
                ->selectRaw('AVG(EXTRACT(EPOCH FROM (first_response_at - created_at))/3600.0) as hrs')
                ->first())->hrs,
        ];

        if ($kpis['avg_resolution_hours'] !== null) {
            $kpis['avg_resolution_hours'] = round($kpis['avg_resolution_hours'], 1);
        }
        if ($kpis['avg_first_response_hours'] !== null) {
            $kpis['avg_first_response_hours'] = round($kpis['avg_first_response_hours'], 1);
        }

        // Status distribution
        $statusRows = (clone $base)
            ->select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        // Priority distribution
        $priorityRows = (clone $base)
            ->select('priority', DB::raw('COUNT(*) as c'))
            ->groupBy('priority')
            ->orderByRaw("array_position(ARRAY['low','medium','high','urgent']::text[], priority)")
            ->get();

        // Branch breakdown
        $branchRows = (clone $base)
            ->select(
                'branch_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM((status='new')::int) as new"),
                DB::raw("SUM((status='in_progress')::int) as in_progress"),
                DB::raw("SUM((status='resolved')::int) as resolved"),
                DB::raw("SUM((status='closed')::int) as closed")
            )
            ->groupBy('branch_id')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($r) {
                $r->branch_name = optional(Branch::find($r->branch_id))->name ?? '—';

                // $r->total = (int) $r->total;
                return $r;
            });

        // Trends (by day, week, or month)
        $grain = in_array($request->get('grain'), ['day', 'week', 'month']) ? $request->get('grain') : 'day';
        $bucket = "date_trunc('".$grain."', created_at)";

        $trendRows = (clone $base)
            ->selectRaw("$bucket as bucket,
                         COUNT(*) as total,
                         SUM((status='new')::int) as new,
                         SUM((status='in_progress')::int) as in_progress,
                         SUM((status='resolved')::int) as resolved,
                         SUM((status='closed')::int) as closed")
            ->groupBy(DB::raw($bucket))
            ->orderBy(DB::raw($bucket))
            ->get()
            ->map(function ($r) {
                $r->bucket = \Illuminate\Support\Carbon::parse($r->bucket)->toDateString();

                return $r;
            });

        // SLA compliance
        $sla = (clone $base)
            ->selectRaw('
        COUNT(*) FILTER (WHERE resolved_at IS NOT NULL) as total_resolved,
        SUM((resolved_at <= sla_due_at)::int) FILTER (WHERE resolved_at IS NOT NULL) as met,
        SUM((resolved_at > sla_due_at)::int) FILTER (WHERE resolved_at IS NOT NULL) as missed,
        COUNT(*) FILTER (WHERE resolved_at IS NULL AND sla_due_at < now()) as overdue_unresolved
    ')
            ->first();

        // SLA rate (percentage of resolved issues that met SLA)
        $slaRate = ($sla && $sla->total_resolved > 0)
            ? round(($sla->met * 100.0) / $sla->total_resolved, 1)
            : null;

        // 🔹 New: Top Categories
        $categoryRows = (clone $base)
            ->select('issue_category_id', DB::raw('COUNT(*) as c'))
            ->groupBy('issue_category_id')
            ->orderByDesc('c')
            ->take(5)
            ->with('issueCategory:id,name')
            ->get();

        // 🔹 New: Top Staff
        $staffRows = (clone $base)
            ->whereNotNull('assigned_user_id')
            ->select(
                'assigned_user_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM((status IN ('new','in_progress'))::int) as open"),
                DB::raw("SUM((status='resolved')::int) as resolved"),
                DB::raw("SUM((status='closed')::int) as closed")
            )
            ->groupBy('assigned_user_id')
            ->orderByDesc('total')
            ->take(5)
            ->with('assignedTo:id,name')
            ->get();

        // Widget 1: Urgent unassigned issues (admin/branch_manager only)
        $urgentUnassigned = collect();
        if (! $user->hasRole('staff')) {
            $urgentQuery = Issue::with('roasterContact:id,name,role', 'issueCategory:id,name')
                ->where('tenant_id', $tenantId)
                ->whereNull('assigned_user_id')
                ->whereIn('status', ['new', 'in_progress'])
                ->whereHas('aiAnalysis', fn ($q) =>
                    $q->whereIn('analysis_type', ['full'])
                      ->whereRaw("result->>'urgency_flag' = 'escalate'")
                )
                ->orderByDesc('created_at')
                ->limit(5);

            if ($user->hasRole('branch_manager')) {
                $branchIds = $user->branches()->pluck('branches.id')->all();
                $urgentQuery->whereIn('branch_id', $branchIds ?: [-1]);
            }

            $urgentUnassigned = $urgentQuery->get();
        }

        // Widget 2: Hot topics this week (themes from full analyses)
        $hotTopics = collect();
        $weekAnalyses = IssueAiAnalysis::where('tenant_id', $tenantId)
            ->where('analysis_type', 'full')
            ->where('created_at', '>=', now()->subDays(7))
            ->get(['result']);

        if ($weekAnalyses->isNotEmpty()) {
            $hotTopics = $weekAnalyses
                ->flatMap(fn ($a) => $a->result['themes'] ?? [])
                ->countBy()
                ->sortDesc()
                ->take(8);
        }

        // ── AI Sentiment breakdown ────────────────────────────────────────
        // Overall totals (positive / neutral / negative) for the selected date range
        $sentimentTotals = DB::table('issue_ai_analysis as ai')
            ->join('issues as i', 'i.id', '=', 'ai.issue_id')
            ->select(
                DB::raw("lower(COALESCE(ai.result->>'sentiment', ai.result->>'label')) as label"),
                DB::raw('COUNT(*) as total')
            )
            ->whereIn('ai.analysis_type', ['full', 'sentiment'])
            ->where('i.tenant_id', $tenantId)
            ->whereDate('i.created_at', '>=', $from)
            ->whereDate('i.created_at', '<=', $to)
            ->whereNotNull(DB::raw("COALESCE(ai.result->>'sentiment', ai.result->>'label')"))
            ->groupBy(DB::raw("lower(COALESCE(ai.result->>'sentiment', ai.result->>'label'))"))
            ->get()
            ->keyBy('label');

        $sentimentGrandTotal = $sentimentTotals->sum('total') ?: 1;

        // Per-category breakdown for stacked bar chart
        $sentimentByCategory = DB::table('issue_ai_analysis as ai')
            ->join('issues as i', 'i.id', '=', 'ai.issue_id')
            ->leftJoin('issue_categories as c', 'c.id', '=', 'i.issue_category_id')
            ->select(
                DB::raw("COALESCE(c.name, 'Uncategorised') as category_name"),
                DB::raw("lower(COALESCE(ai.result->>'sentiment', ai.result->>'label')) as sentiment"),
                DB::raw('COUNT(*) as total')
            )
            ->whereIn('ai.analysis_type', ['full', 'sentiment'])
            ->where('i.tenant_id', $tenantId)
            ->whereDate('i.created_at', '>=', $from)
            ->whereDate('i.created_at', '<=', $to)
            ->whereNotNull(DB::raw("COALESCE(ai.result->>'sentiment', ai.result->>'label')"))
            ->groupBy('category_name', 'sentiment')
            ->orderBy('category_name')
            ->get()
            ->groupBy('category_name');

        $aiCategories = $sentimentByCategory->keys()->values()->all();
        $aiSeriesData = [];
        foreach (['positive', 'neutral', 'negative'] as $s) {
            $aiSeriesData[$s] = collect($aiCategories)->map(
                fn ($cat) => (int) ($sentimentByCategory[$cat]->firstWhere('sentiment', $s)?->total ?? 0)
            )->values()->all();
        }

        // ── Queue health ───────────────────────────────────────────────────
        // A job is dispatched every time an issue is submitted or a worker heartbeat
        // runs. If the queue is down, jobs pile up. We surface a warning to admins
        // if there are pending jobs older than 10 minutes.
        $queueWarning = false;
        if ($user->hasRole('admin')) {
            $queueWarning = Cache::remember('queue_health_warning_' . $tenantId, 60, function () {
                return DB::table('jobs')
                    ->where('available_at', '<=', now()->subMinutes(10)->timestamp)
                    ->exists();
            });
        }

        // ── Recent kudos / positive signals ───────────────────────────────
        $recentKudos = \App\Models\SchoolKudo::where('tenant_id', $tenantId)
            ->with('contact:id,name', 'category:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('tenant.admin.dashboard', [
            'from' => $from,
            'to' => $to,
            'grain' => $grain,
            'kpis' => $kpis,
            'statusRows' => $statusRows,
            'branchRows' => $branchRows,
            'trendRows' => $trendRows,
            'sla' => $sla,
            'slaRate' => $slaRate,
            'priorityRows' => $priorityRows,
            'categoryRows' => $categoryRows,
            'staffRows' => $staffRows,
            'user' => $user,
            // AI widgets
            'urgentUnassigned' => $urgentUnassigned,
            'hotTopics'        => $hotTopics,
            'issueGroupCount'  => $user->hasRole('admin')
                ? IssueGroup::where('tenant_id', $tenantId)->where('status', 'open')->where('issue_count', '>=', 2)->count()
                : 0,
            // AI sentiment
            'sentimentTotals'     => $sentimentTotals,
            'sentimentGrandTotal' => $sentimentGrandTotal,
            'aiCategories'        => $aiCategories,
            'aiSeriesData'        => $aiSeriesData,
            // Positive signals
            'recentKudos'  => $recentKudos,
            'queueWarning' => $queueWarning,
        ]);
    }
}
