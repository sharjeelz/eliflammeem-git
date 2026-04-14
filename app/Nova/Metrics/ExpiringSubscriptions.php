<?php

namespace App\Nova\Metrics;

use App\Models\Tenant;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class ExpiringSubscriptions extends Value
{
    public function name(): string { return 'Subscriptions Expiring (30d)'; }

    public function calculate(NovaRequest $request): \Laravel\Nova\Metrics\ValueResult
    {
        $count = Tenant::whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '>=', now())
            ->where('subscription_ends_at', '<=', now()->addDays(30))
            ->count();

        $expired = Tenant::whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '<', now())
            ->count();

        return $this->result($count)
            ->suffix('expiring soon')
            ->previous($expired)
            ->allowZeroResult();
    }

    public function ranges(): array { return []; }

    public function cacheFor(): \DateTimeInterface|\DateInterval|float|int|null
    {
        return now()->addMinutes(10);
    }
}
