<?php

namespace App\Nova\Metrics;

use App\Models\AiUsageLog;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class AiCostTotal extends Value
{
    public function name(): string { return 'AI Spend (USD)'; }

    public function calculate(NovaRequest $request): \Laravel\Nova\Metrics\ValueResult
    {
        return $this->sum($request, AiUsageLog::class, 'cost_usd')
            ->prefix('$')
            ->format('0,0.0000');
    }

    public function ranges(): array
    {
        return [7 => '7 Days', 30 => '30 Days', 60 => '60 Days', 'ALL' => 'All Time'];
    }

    public function cacheFor(): \DateTimeInterface|\DateInterval|float|int|null
    {
        return now()->addMinutes(5);
    }
}
