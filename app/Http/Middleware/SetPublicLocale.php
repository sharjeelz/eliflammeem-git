<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetPublicLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Only apply Urdu locale on the public portal — never on admin routes
        if ($request->is('admin/*') || $request->is('admin')) {
            return $next($request);
        }

        $locale = session('locale', 'en');
        if (!in_array($locale, ['en', 'ur'])) {
            $locale = 'en';
        }
        App::setLocale($locale);
        return $next($request);
    }
}
