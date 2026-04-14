<?php

namespace App\Jobs;

use App\Models\IssueAiAnalysis;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\IssueTrendDetectedNotification;
use App\Services\PlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\PermissionRegistrar;

class DetectIssueTrends implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public int $tries = 1;

    public int $timeout = 30;

    public function __construct(
        public readonly string $tenantId,
    ) {}

    public function handle(): void
    {
        $threshold = (int) config('schoolytics.trend_alert_threshold', 3);

        tenancy()->initialize(Tenant::find($this->tenantId));

        try {
            if (! PlanService::forCurrentTenant()->allows('ai_trends')) {
                return;
            }
            $analyses = IssueAiAnalysis::where('tenant_id', $this->tenantId)
                ->where('analysis_type', 'full')
                ->where('created_at', '>=', now()->subDays(7))
                ->get(['result']);

            if ($analyses->isEmpty()) {
                return;
            }

            $themeCounts = $analyses
                ->flatMap(fn($a) => $a->result['themes'] ?? [])
                ->countBy()
                ->sortDesc();

            app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenantId);

            $admins = User::role('admin')
                ->where('tenant_id', $this->tenantId)
                ->get();

            if ($admins->isEmpty()) {
                return;
            }

            foreach ($themeCounts as $theme => $count) {
                if ($count < $threshold) {
                    continue;
                }

                $cacheKey = "trend:{$this->tenantId}:" . md5(strtolower($theme));

                if (Cache::has($cacheKey)) {
                    continue; // already alerted today
                }

                Cache::put($cacheKey, true, now()->addHours(24));

                $notification = new IssueTrendDetectedNotification($theme, $count);

                foreach ($admins as $admin) {
                    $admin->notify($notification);
                }
            }
        } finally {
            tenancy()->end();
        }
    }
}
