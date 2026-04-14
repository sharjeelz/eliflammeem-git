<?php

namespace App\Console\Commands;

use App\Mail\DailyTrendDigestMail;
use App\Models\IssueAiAnalysis;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\PermissionRegistrar;

class SendDailyTrendDigest extends Command
{
    protected $signature = 'trends:send-digest 
                            {--tenant= : Process only a specific tenant ID}
                            {--dry-run : Preview trends without sending emails}';

    protected $description = 'Send daily trend digest email to admins for all tenants';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $specificTenant = $this->option('tenant');

        if ($dryRun) {
            $this->warn('🧪 DRY RUN MODE - No emails will be sent');
        }

        // Get tenants to process
        $tenants = $specificTenant
            ? Tenant::where('id', $specificTenant)->get()
            : Tenant::all();

        if ($tenants->isEmpty()) {
            $this->error('No tenants found');

            return 1;
        }

        $this->info("Processing {$tenants->count()} tenant(s)...\n");

        $totalEmails = 0;
        $totalTrends = 0;

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);

            try {
                $result = $this->processTenant($tenant, $dryRun);
                $totalEmails += $result['emails'];
                $totalTrends += $result['trends'];
            } catch (\Throwable $e) {
                $this->error("✗ {$tenant->name}: {$e->getMessage()}");
            } finally {
                tenancy()->end();
            }
        }

        $this->newLine();
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 Summary:");
        $this->info("   Tenants processed: {$tenants->count()}");
        $this->info("   Total trends detected: {$totalTrends}");
        $this->info("   Emails queued: {$totalEmails}");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        if ($dryRun) {
            $this->warn('🧪 DRY RUN - No emails were actually sent');
        } else {
            $this->info('✅ Digest emails queued successfully!');
        }

        return 0;
    }

    private function processTenant(Tenant $tenant, bool $dryRun): array
    {
        // Get threshold from config (default: 3)
        $threshold = (int) config('schoolytics.trend_alert_threshold', 3);

        // Get admins for this tenant
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
        $admins = User::role('admin')
            ->where('tenant_id', $tenant->id)
            ->get();

        if ($admins->isEmpty()) {
            $this->line("  ⚠ {$tenant->name}: No admins found, skipping");

            return ['emails' => 0, 'trends' => 0];
        }

        // Analyze last 7 days of AI analyses
        $analyses = IssueAiAnalysis::where('tenant_id', $tenant->id)
            ->where('analysis_type', 'full')
            ->where('created_at', '>=', now()->subDays(7))
            ->get(['result']);

        // Extract and count themes
        $themeCounts = $analyses
            ->flatMap(fn ($a) => $a->result['themes'] ?? [])
            ->countBy()
            ->sortDesc();

        // Filter trends that meet threshold
        $trendCounts = $themeCounts->filter(fn ($count) => $count >= $threshold);

        // Get first domain for URL generation
        $domain = $tenant->domains()->first();
        $protocol = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'http';
        $port = parse_url(config('app.url'), PHP_URL_PORT);
        $baseIssuesUrl = $domain
            ? "{$protocol}://{$domain->domain}" . ($port ? ":{$port}" : '') . '/admin/issues'
            : url('admin/issues');

        // Build structured trends array with filtered URLs
        $trends = $trendCounts->map(function ($count, $theme) use ($baseIssuesUrl) {
            return [
                'theme' => $theme,
                'count' => $count,
                'url' => $baseIssuesUrl . '?theme=' . urlencode($theme),
            ];
        })->values()->toArray();

        // Send emails to admins
        $emailsSent = 0;
        foreach ($admins as $admin) {
            if (! $admin->email) {
                continue;
            }

            if ($dryRun) {
                $this->line("  [DRY RUN] Would email: {$admin->email} ({$admin->name})");
            } else {
                Mail::to($admin->email)->queue(
                    new DailyTrendDigestMail(
                        trends: $trends,
                        schoolName: $tenant->name,
                        adminName: $admin->name,
                        issuesUrl: $baseIssuesUrl,
                        tenantId: $tenant->id,
                    )
                );
            }

            $emailsSent++;
        }

        // Output result
        $trendCount = count($trends);
        $icon = $trendCount > 0 ? '📈' : '✓';
        $status = $trendCount > 0
            ? "{$trendCount} trend" . ($trendCount > 1 ? 's' : '')
            : 'no trends';

        $this->line("  {$icon} {$tenant->name}: {$status}, {$emailsSent} admin" . ($emailsSent > 1 ? 's' : '') . ' notified');

        if ($trendCount > 0 && $this->output->isVerbose()) {
            foreach ($trends as $trend) {
                $this->line("      • {$trend['theme']}: {$trend['count']} issues");
            }
        }

        return [
            'emails' => $emailsSent,
            'trends' => $trendCount,
        ];
    }
}
