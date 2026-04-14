<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Issue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Staff have no access to reports
        if ($user->hasRole('staff')) {
            abort(403, 'Reports are not available to staff.');
        }

        $tenantId = tenant('id');

        // Date range defaults to last 30 days
        $from = $request->date('from')?->toDateString() ?? now()->subDays(29)->toDateString();
        $to   = $request->date('to')?->toDateString()   ?? now()->toDateString();

        $grain = in_array($request->get('grain'), ['day', 'week', 'month'])
            ? $request->get('grain')
            : 'day';

        // Base scope with date range and tenant isolation
        $base = Issue::where('tenant_id', $tenantId)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        // Role-based scoping
        if ($user->hasRole('branch_manager')) {
            $branchIds = $user->branches()->pluck('branches.id')->all();
            $base->whereIn('branch_id', $branchIds ?: [-1]);
        }
        // Admin: no extra filter → sees all issues

        // ── KPIs ──────────────────────────────────────────────────────────────
        $total    = (clone $base)->count();
        $open     = (clone $base)->whereIn('status', ['new', 'in_progress'])->count();
        $resolved = (clone $base)->where('status', 'resolved')->count();
        $closed   = (clone $base)->where('status', 'closed')->count();

        $avgResolutionHours = optional(
            (clone $base)
                ->whereNotNull('resolved_at')
                ->selectRaw('AVG(EXTRACT(EPOCH FROM (resolved_at - created_at))/3600.0) as hrs')
                ->first()
        )->hrs;

        if ($avgResolutionHours !== null) {
            $avgResolutionHours = round($avgResolutionHours, 1);
        }

        // ── Trend ─────────────────────────────────────────────────────────────
        $bucket = "date_trunc('{$grain}', created_at)";

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

        // ── SLA ───────────────────────────────────────────────────────────────
        $sla = (clone $base)
            ->selectRaw("
                COUNT(*) FILTER (WHERE resolved_at IS NOT NULL) as total_resolved,
                SUM((resolved_at <= sla_due_at)::int) FILTER (WHERE resolved_at IS NOT NULL) as met,
                SUM((resolved_at > sla_due_at)::int) FILTER (WHERE resolved_at IS NOT NULL) as missed,
                COUNT(*) FILTER (WHERE status NOT IN ('resolved','closed') AND sla_due_at IS NOT NULL AND sla_due_at < now()) as overdue_unresolved
            ")
            ->first();

        $slaRate = ($sla && $sla->total_resolved > 0)
            ? round(($sla->met * 100.0) / $sla->total_resolved, 1)
            : null;

        // ── Branch breakdown (all branches) ───────────────────────────────────
        $branchRows = (clone $base)
            ->select(
                'branch_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM((status='new')::int) as new"),
                DB::raw("SUM((status='in_progress')::int) as in_progress"),
                DB::raw("SUM((status='resolved')::int) as resolved"),
                DB::raw("SUM((status='closed')::int) as closed"),
                DB::raw('SUM(is_spam::int) as spam_count')
            )
            ->groupBy('branch_id')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($r) {
                $r->branch_name     = optional(Branch::find($r->branch_id))->name ?? '—';
                $r->resolution_rate = $r->total > 0
                    ? round(($r->resolved / $r->total) * 100, 1)
                    : 0;
                $r->spam_rate       = $r->total > 0
                    ? round(($r->spam_count / $r->total) * 100, 1)
                    : 0;
                return $r;
            });

        // ── Staff performance (all staff, not top 5) ──────────────────────────
        // Issue-level counts: total assigned, currently open, currently closed
        $staffRows = (clone $base)
            ->whereNotNull('assigned_user_id')
            ->select(
                'assigned_user_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM((status IN ('new','in_progress'))::int) as open"),
                DB::raw("SUM((status='closed')::int) as closed")
            )
            ->groupBy('assigned_user_id')
            ->orderByDesc('total')
            ->with(['assignedTo:id,name', 'assignedTo.branches:id,name'])
            ->get();

        // Activity-log counts: how many times each user personally triggered → resolved
        // Scoped to issues created within the date range (and branch if branch_manager)
        $resolvedByActorQuery = DB::table('issue_activities as ia')
            ->join('issues as i', 'i.id', '=', 'ia.issue_id')
            ->where('ia.tenant_id', $tenantId)
            ->where('i.tenant_id', $tenantId)
            ->where('ia.type', 'status_changed')
            ->whereRaw("ia.data->>'to' = 'resolved'")
            ->whereDate('i.created_at', '>=', $from)
            ->whereDate('i.created_at', '<=', $to);

        if ($user->hasRole('branch_manager')) {
            $resolvedByActorQuery->whereIn('i.branch_id', $branchIds ?: [-1]);
        }

        // keyed by actor_id → count of distinct issues they resolved
        $resolvedByActor = $resolvedByActorQuery
            ->select('ia.actor_id', DB::raw('COUNT(DISTINCT ia.issue_id) as c'))
            ->groupBy('ia.actor_id')
            ->pluck('c', 'actor_id');

        $staffRows = $staffRows->map(function ($r) use ($resolvedByActor) {
            $r->resolved_by_actor = (int) ($resolvedByActor[$r->assigned_user_id] ?? 0);
            $r->resolution_rate   = $r->total > 0
                ? round(($r->resolved_by_actor / $r->total) * 100, 1)
                : 0;
            return $r;
        });

        // ── Categories (all, ordered desc) ────────────────────────────────────
        $categoryRows = (clone $base)
            ->select('issue_category_id', DB::raw('COUNT(*) as c'))
            ->groupBy('issue_category_id')
            ->orderByDesc('c')
            ->with('issueCategory:id,name')
            ->get();

        // ── Category recurrence trend (monthly, regardless of page grain) ──────
        $catTrendRaw = (clone $base)
            ->whereNotNull('issue_category_id')
            ->selectRaw("date_trunc('month', created_at) as month, issue_category_id, COUNT(*) as c")
            ->groupBy(DB::raw("date_trunc('month', created_at)"), 'issue_category_id')
            ->orderBy(DB::raw("date_trunc('month', created_at)"))
            ->get();

        $catTrendMonths = $catTrendRaw
            ->pluck('month')
            ->map(fn($m) => \Illuminate\Support\Carbon::parse($m)->format('M Y'))
            ->unique()->values()->all();

        // Eager-load category names in one query
        $catNames = \App\Models\IssueCategory::whereIn('id', $catTrendRaw->pluck('issue_category_id')->unique())
            ->pluck('name', 'id');

        $catTrendSeries = $catTrendRaw
            ->groupBy('issue_category_id')
            ->map(function ($rows, $catId) use ($catTrendMonths, $catNames) {
                $byMonth = $rows->keyBy(fn($r) => \Illuminate\Support\Carbon::parse($r->month)->format('M Y'));
                return [
                    'name' => $catNames[$catId] ?? 'Unknown',
                    'data' => collect($catTrendMonths)
                        ->map(fn($m) => (int) ($byMonth[$m]->c ?? 0))
                        ->all(),
                ];
            })->values()->all();

        // ── CSAT ──────────────────────────────────────────────────────────────
        // Scoped to surveys created for issues in the selected date range
        $csatBase = DB::table('csat_responses as c')
            ->join('issues as i', 'i.id', '=', 'c.issue_id')
            ->where('c.tenant_id', $tenantId)
            ->whereDate('i.created_at', '>=', $from)
            ->whereDate('i.created_at', '<=', $to);

        if ($user->hasRole('branch_manager')) {
            $csatBase->whereIn('i.branch_id', $branchIds ?: [-1]);
        }

        $csatTotal        = (clone $csatBase)->count();
        $csatReceived     = (clone $csatBase)->whereNotNull('c.submitted_at')->count();
        $csatAvg          = $csatReceived > 0
            ? round((clone $csatBase)->whereNotNull('c.submitted_at')->avg('c.rating'), 1)
            : null;
        $csatResponseRate = $csatTotal > 0 ? round($csatReceived / $csatTotal * 100, 1) : null;
        $csatDist         = (clone $csatBase)
            ->whereNotNull('c.submitted_at')
            ->select('c.rating', DB::raw('COUNT(*) as cnt'))
            ->groupBy('c.rating')
            ->orderBy('c.rating')
            ->pluck('cnt', 'rating');

        $csatBranchRows   = (clone $csatBase)
            ->select(
                'i.branch_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN c.submitted_at IS NOT NULL THEN 1 ELSE 0 END) as received'),
                DB::raw('AVG(CASE WHEN c.submitted_at IS NOT NULL THEN c.rating ELSE NULL END) as avg_rating')
            )
            ->groupBy('i.branch_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($r) {
                $r->branch_name    = optional(Branch::find($r->branch_id))->name ?? '—';
                $r->avg_rating     = $r->avg_rating !== null ? round($r->avg_rating, 1) : null;
                $r->response_rate  = $r->total > 0 ? round($r->received / $r->total * 100, 1) : null;
                return $r;
            });

        // ── Most Improved Categories (first half vs second half of selected range) ──
        $rangeDays  = max(2, \Illuminate\Support\Carbon::parse($from)->diffInDays(\Illuminate\Support\Carbon::parse($to)) + 1);
        $midpoint   = \Illuminate\Support\Carbon::parse($from)->addDays((int) floor($rangeDays / 2))->toDateString();

        $firstHalf = (clone $base)
            ->whereNotNull('issue_category_id')
            ->whereDate('created_at', '<', $midpoint)
            ->select('issue_category_id', DB::raw('COUNT(*) as c'))
            ->groupBy('issue_category_id')
            ->pluck('c', 'issue_category_id');

        $secondHalf = (clone $base)
            ->whereNotNull('issue_category_id')
            ->whereDate('created_at', '>=', $midpoint)
            ->select('issue_category_id', DB::raw('COUNT(*) as c'))
            ->groupBy('issue_category_id')
            ->pluck('c', 'issue_category_id');

        $improvedCategories = $firstHalf
            ->filter(fn($c) => $c > 0)
            ->map(fn($c, $id) => [
                'id'     => $id,
                'name'   => \App\Models\IssueCategory::find($id)?->name ?? '—',
                'first'  => (int) $c,
                'second' => (int) ($secondHalf[$id] ?? 0),
                'drop'   => (int) $c - (int) ($secondHalf[$id] ?? 0),
            ])
            ->filter(fn($row) => $row['drop'] > 0)
            ->sortByDesc('drop')
            ->take(3)
            ->values();

        // ── Repeat complaint rate ─────────────────────────────────────────────────
        $repeatContacts = (clone $base)
            ->whereNotNull('roster_contact_id')
            ->select('roster_contact_id', DB::raw('COUNT(*) as c'))
            ->groupBy('roster_contact_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $totalUniqueContacts = (clone $base)
            ->whereNotNull('roster_contact_id')
            ->distinct('roster_contact_id')
            ->count('roster_contact_id');

        $repeatRate = $totalUniqueContacts > 0
            ? round($repeatContacts / $totalUniqueContacts * 100, 1)
            : null;

        return view('tenant.admin.reports.index', [
            'catTrendMonths'     => $catTrendMonths,
            'catTrendSeries'     => $catTrendSeries,
            'improvedCategories' => $improvedCategories,
            'repeatContacts'     => $repeatContacts,
            'totalUniqueContacts' => $totalUniqueContacts,
            'repeatRate'         => $repeatRate,
            'from'               => $from,
            'to'                 => $to,
            'grain'              => $grain,
            'total'              => $total,
            'open'               => $open,
            'resolved'           => $resolved,
            'closed'             => $closed,
            'avgResolutionHours' => $avgResolutionHours,
            'trendRows'          => $trendRows,
            'sla'                => $sla,
            'slaRate'            => $slaRate,
            'branchRows'         => $branchRows,
            'staffRows'          => $staffRows,
            'categoryRows'       => $categoryRows,
            'csatTotal'          => $csatTotal,
            'csatReceived'       => $csatReceived,
            'csatAvg'            => $csatAvg,
            'csatResponseRate'   => $csatResponseRate,
            'csatDist'           => $csatDist,
            'csatBranchRows'     => $csatBranchRows,
            'user'               => $user,
        ]);
    }
}
