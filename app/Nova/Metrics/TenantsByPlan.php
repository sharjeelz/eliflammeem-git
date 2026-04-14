<?php

namespace App\Nova\Metrics;

use App\Models\Tenant;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class TenantsByPlan extends Partition
{
    public function name(): string { return 'Schools by Plan'; }

    public function calculate(NovaRequest $request): \Laravel\Nova\Metrics\PartitionResult
    {
        return $this->count($request, Tenant::class, 'plan')
            ->label(fn ($value) => ucfirst($value ?? 'unknown'));
    }

    public function colors(): array
    {
        return [
            'starter'    => '#b5b5c3',
            'growth'     => '#50cd89',
            'pro'        => '#009ef7',
            'enterprise' => '#7239ea',
        ];
    }

    public function cacheFor(): \DateTimeInterface|\DateInterval|float|int|null
    {
        return now()->addMinutes(5);
    }
}
