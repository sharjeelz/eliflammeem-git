<?php

use App\Http\Middleware\SetSpatieTeamFromTenant;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Trust ALB/reverse proxy — required for HTTPS asset URLs behind a load balancer
        $middleware->trustProxies(at: '*');

        $middleware->statefulApi();

        $middleware->group('universal', []);
        $middleware->group('tenant', [
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
            // SetSpatieTeamFromTenant::class,
        ]);

        $middleware->alias([
            'abilities'       => CheckAbilities::class,
            'ability'         => CheckForAnyAbility::class,
            'single.session'  => \App\Http\Middleware\EnsureSingleSession::class,
            'plan.feature'    => \App\Http\Middleware\RequiresPlanFeature::class,
            'onboarding'      => \App\Http\Middleware\EnsureOnboardingComplete::class,
            'public.locale'   => \App\Http\Middleware\SetPublicLocale::class,
        ]);

        $middleware->append(HandleCors::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Redirect unauthenticated tenant admin requests to /admin/login, not Nova
        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            // If the URL is a tenant admin route, send to tenant login
            if (str_starts_with($request->path(), 'admin')) {
                return redirect()->guest(url('admin/login'));
            }
        });

        $exceptions->renderable(function (AuthorizationException $e, $request) {
            activity_async(
                'User attempted to access a forbidden resource',
                $request->user(),
                [
                    'ip' => request()->ip(),
                    'url' => $request->fullUrl(),
                    'message' => $e->getMessage(),
                ],
                'user-actions'
            );

            return response()->view('errors.403', [
                'message' => $e->getMessage(),
            ], 403);
        });

        $exceptions->renderable(function (HttpException $e, $request) {
            if ($e->getStatusCode() !== 403) {
                return;
            }

            $performedOn = null;
            $route = $request->route();

            if ($route) {
                $controller = class_basename(optional($route->getController()));
                $action = optional($route->getAction())['as'] ?? $route->getActionMethod();

                // Find the first route parameter that is an Eloquent model instance
                $modelParam = collect($route->parameters())
                    ->first(fn ($param) => $param instanceof \Illuminate\Database\Eloquent\Model);

                if ($modelParam) {
                    // ✅ Here we have the actual model instance
                    $performedOn = $modelParam; // Eloquent model itself
                } else {
                    // fallback to controller@method if no model binding
                    $performedOn = "{$controller}@{$action}";
                }
            } else {
                $performedOn = $request->path();
            }
            activity_async(
                'User attempted to access a forbidden resource',
                $performedOn,
                [
                    'ip' => request()->ip(),
                    'url' => $request->fullUrl(),
                    'message' => $e->getMessage(),
                ],
                'user-actions'
            );

            return response()->view('errors.403', [
                'message' => $e->getMessage(),
            ], 403);
        });
    })->create();
