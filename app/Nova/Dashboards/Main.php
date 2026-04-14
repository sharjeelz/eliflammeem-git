<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\AiCallsByType;
use App\Nova\Metrics\AiCostTotal;
use App\Nova\Metrics\AiCostTrend;
use App\Nova\Metrics\ExpiringSubscriptions;
use App\Nova\Metrics\IssuesByStatus;
use App\Nova\Metrics\IssuesTrend;
use App\Nova\Metrics\NewTenantsTrend;
use App\Nova\Metrics\TenantsByPlan;
use App\Nova\Metrics\TotalIssues;
use App\Nova\Metrics\TotalRosterContacts;
use App\Nova\Metrics\TotalTenants;
use App\Nova\Metrics\TotalUsers;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    public function name(): string
    {
        return 'Overview';
    }

    public function cards(): array
    {
        return [
            // ── Row 1: Platform KPIs ──────────────────────────────────────────
            (new TotalTenants)->width('1/4'),
            (new TotalIssues)->width('1/4'),
            (new TotalUsers)->width('1/4'),
            (new TotalRosterContacts)->width('1/4'),

            // ── Row 2: School growth & plan mix ──────────────────────────────
            (new NewTenantsTrend)->width('2/3'),
            (new TenantsByPlan)->width('1/3'),

            // ── Row 3: Issues ─────────────────────────────────────────────────
            (new IssuesTrend)->width('2/3'),
            (new IssuesByStatus)->width('1/3'),

            // ── Row 4: AI cost & usage ────────────────────────────────────────
            (new AiCostTotal)->width('1/4'),
            (new ExpiringSubscriptions)->width('1/4'),
            (new AiCallsByType)->width('1/2'),

            // ── Row 5: AI cost trend ──────────────────────────────────────────
            (new AiCostTrend)->width('full'),
        ];
    }
}
