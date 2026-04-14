<?php

namespace App\Nova\Metrics;

use App\Models\Tenant;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;

class NewTenantsTrend extends Trend
{
    public function name(): string { return 'New Schools Over Time'; }

    public function calculate(NovaRequest $request): \Laravel\Nova\Metrics\TrendResult
    {
        return $this->countByDays($request, Tenant::class)
            ->showSumValue();
    }

    public function ranges(): array
    {
        return [30 => '30 Days', 60 => '60 Days', 90 => '90 Days', 365 => '1 Year'];
    }

    public function cacheFor(): \DateTimeInterface|\DateInterval|float|int|null
    {
        return now()->addMinutes(5);
    }
}
