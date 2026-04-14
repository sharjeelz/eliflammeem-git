<?php

namespace App\Nova\Metrics;

use App\Models\Tenant;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class TotalTenants extends Value
{
    public function name(): string { return 'Total Schools'; }

    public function calculate(NovaRequest $request): \Laravel\Nova\Metrics\ValueResult
    {
        return $this->count($request, Tenant::class);
    }

    public function ranges(): array
    {
        return [30 => '30 Days', 60 => '60 Days', 365 => '1 Year', 'ALL' => 'All Time'];
    }

    public function cacheFor(): \DateTimeInterface|\DateInterval|float|int|null
    {
        return now()->addMinutes(5);
    }
}
