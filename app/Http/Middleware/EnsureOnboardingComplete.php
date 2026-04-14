<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = tenancy()->tenant;

        if (! $tenant) {
            return $next($request);
        }

        $status = $tenant->registration_status;

        // Only block if onboarding is in-progress — existing tenants with no status pass through
        if (in_array($status, ['pending', 'profile_complete', 'terms_accepted'])) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Please complete the setup wizard first.'], 403);
            }

            return redirect()->route('tenant.admin.onboarding');
        }

        return $next($request);
    }
}
