<?php

namespace App\Http\Middleware;

use App\Services\PlanService;
use Closure;
use Illuminate\Http\Request;

class RequiresPlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): mixed
    {
        $plan = PlanService::forCurrentTenant();

        if (! $plan->allows($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => "The '{$feature}' feature is not available on your current plan ({$plan->planName()}). Please upgrade.",
                ], 403);
            }

            return redirect()->route('tenant.admin.dashboard')
                ->with('plan_error', "The '{$feature}' feature requires a higher plan. Please contact your administrator to upgrade.");
        }

        return $next($request);
    }
}
