<?php

namespace App\Nova\Metrics;

use App\Models\User;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class TotalUsers extends Value
{
    public function name(): string { return 'Total Staff Users'; }

    public function calculate(NovaRequest $request): \Laravel\Nova\Metrics\ValueResult
    {
        return $this->count($request, User::class);
    }

    public function ranges(): array
    {
        return [30 => '30 Days', 60 => '60 Days', 'ALL' => 'All Time'];
    }

    public function cacheFor(): \DateTimeInterface|\DateInterval|float|int|null
    {
        return now()->addMinutes(5);
    }
}
