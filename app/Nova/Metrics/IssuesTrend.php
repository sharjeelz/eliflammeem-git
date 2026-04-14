<?php

namespace App\Nova\Metrics;

use App\Models\Issue;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;

class IssuesTrend extends Trend
{
    public function name(): string { return 'Issues Submitted'; }

    public function calculate(NovaRequest $request): \Laravel\Nova\Metrics\TrendResult
    {
        return $this->countByDays($request, Issue::class)
            ->showSumValue();
    }

    public function ranges(): array
    {
        return [7 => '7 Days', 30 => '30 Days', 60 => '60 Days', 90 => '90 Days'];
    }

    public function cacheFor(): \DateTimeInterface|\DateInterval|float|int|null
    {
        return now()->addMinutes(5);
    }
}
