<?php

namespace App\Nova\Metrics;

use App\Models\AiUsageLog;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;

class AiCostTrend extends Trend
{
    public function name(): string { return 'AI Cost Over Time (USD)'; }

    public function calculate(NovaRequest $request): \Laravel\Nova\Metrics\TrendResult
    {
        return $this->sumByDays($request, AiUsageLog::class, 'cost_usd')
            ->prefix('$')
            ->showSumValue();
    }

    public function ranges(): array
    {
        return [7 => '7 Days', 30 => '30 Days', 60 => '60 Days'];
    }

    public function cacheFor(): \DateTimeInterface|\DateInterval|float|int|null
    {
        return now()->addMinutes(5);
    }
}
