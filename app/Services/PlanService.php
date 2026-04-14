<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Tenant;

class PlanService
{
    private ?Plan $plan;

    public function __construct(private readonly string $planKey)
    {
        $this->plan = Plan::findCached($planKey) ?? Plan::findCached('starter');
    }

    /**
     * Resolve the plan service for the currently initialised tenant.
     */
    public static function forCurrentTenant(): self
    {
        /** @var Tenant|null $tenant */
        $tenant = tenancy()->tenant;
        return new self($tenant?->plan ?? 'starter');
    }

    /** Check whether a named feature is available on this plan. */
    public function allows(string $feature): bool
    {
        if (! $this->plan) {
            return false;
        }
        $col = "feat_{$feature}";
        return (bool) ($this->plan->{$col} ?? false);
    }

    /**
     * Return the hard cap for a count-based limit (branches / users / contacts).
     * Returns null when the plan is unlimited.
     */
    public function limit(string $key): ?int
    {
        return $this->plan?->{$key} ?? null;
    }

    /**
     * Returns true when `$current` is below the plan cap (or the cap is unlimited).
     */
    public function withinLimit(string $key, int $current): bool
    {
        $max = $this->limit($key);
        return $max === null || $current < $max;
    }

    /**
     * Monthly issue submission cap — null means unlimited.
     */
    public function monthlyIssueLimit(): ?int
    {
        $val = $this->plan?->max_issues_per_month;
        return $val === null ? null : (int) $val;
    }

    /**
     * Daily API request cap per key — null means unlimited, 0 means no access.
     */
    public function apiDailyLimit(): ?int
    {
        $val = $this->plan?->feat_api_daily_limit;
        return $val === null ? null : (int) $val;
    }

    /**
     * Daily chatbot Q&A cap — null means unlimited, 0 means feature off.
     */
    public function chatbotDailyLimit(): ?int
    {
        $val = $this->plan?->feat_chatbot_daily;
        return $val === null ? null : (int) $val;
    }

    /** Human-readable plan label. */
    public function planName(): string
    {
        return $this->plan?->label ?? ucfirst($this->planKey);
    }

    /** The raw plan key (starter / growth / pro / enterprise). */
    public function planKey(): string
    {
        return $this->planKey;
    }

    /** Returns the limit as a formatted string for display ("3", "Unlimited"). */
    public function limitLabel(string $key): string
    {
        $val = $this->limit($key);
        return $val === null ? 'Unlimited' : (string) $val;
    }
}
