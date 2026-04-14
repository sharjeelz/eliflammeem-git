<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class HttpClientServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Disable SSL verification in local development (Windows SSL cert issue)
        if ($this->app->environment('local')) {
            Http::macro('withoutVerifying', function () {
                return Http::withOptions([
                    'verify' => false,
                ]);
            });

            // Set default options for all HTTP requests in development
            Http::globalOptions([
                'verify' => false,
            ]);
        }
    }
}
