<?php

namespace App\Providers;

use App\Models\Issue;
use App\Models\SupportTicket;
use App\Observers\IssueObserver;
use App\Observers\SupportTicketObserver;
use App\Policies\IssuePolicy;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Guarantee Carbon always uses the configured app timezone,
        // overriding any PHP.ini / server timezone that may differ.
        $tz = config('app.timezone', 'Asia/Riyadh');
        date_default_timezone_set($tz);
        Carbon::setLocale(config('app.locale', 'en'));

        Paginator::useBootstrapFive();

        Issue::observe(IssueObserver::class);
        SupportTicket::observe(SupportTicketObserver::class);

        Gate::policy(Issue::class, IssuePolicy::class);

        Gate::define(
            'manage-users',
            fn ($user) => $user?->hasRole('admin') || $user?->hasRole('branch_manager')
        );

        RateLimiter::for('api_keys', function (Request $request) {
            $apiKey = $request->attributes->get('api_key');
            $by     = $apiKey ? 'apikey:' . $apiKey->id : 'ip:' . $request->ip();

            return Limit::perMinute(120)->by($by)->response(function () {
                return response()->json(['error' => 'Too many requests. Limit: 120 per minute.'], 429);
            });
        });

        RateLimiter::for('api_auth_failures', function (Request $request) {
            return Limit::perMinute(10)->by('api_fail:' . $request->ip())
                ->response(function () {
                    return response()->json(['error' => 'Too many failed requests from your IP.'], 429);
                });
        });
    }
}
