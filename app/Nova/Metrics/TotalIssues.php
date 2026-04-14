<?php

namespace App\Nova\Metrics;

use App\Models\Issue;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class TotalIssues extends Value
{
    public function name(): string { return 'Total Issues'; }

    public function calculate(NovaRequest $request): \Laravel\Nova\Metrics\ValueResult
    {
        return $this->count($request, Issue::class);
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
