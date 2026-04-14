<?php

namespace App\Http\Controllers\TenantAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Database\Models\Domain;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('tenant.admin.dashboard');
        }

        $school = \App\Models\School::where('tenant_id', tenant('id'))->first();

        return view('tenant.admin.login', compact('school'));
    }

    public function login(Request $request)
    {
        $creds = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Rate limit: 5 attempts per email+IP, then 2-minute lockout
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, maxAttempts: 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$minutes} minute(s).",
            ])->onlyInput('email');
        }

        $host = $request->getHost();
        $domain = Domain::where('domain', $host)->firstOrFail();
        $tenant = $domain->tenant;
        tenancy()->initialize($tenant);

        // 'web' guard uses users table; BelongsToTenant scopes to current tenant automatically
        if (Auth::guard('web')->attempt($creds, $request->boolean('remember'))) {
            $user = Auth::guard('web')->user();

            // Check if this user has confirmed 2FA and is admin/branch_manager
            app(PermissionRegistrar::class)->setPermissionsTeamId(tenant('id'));

            // Block non-admins from logging in when school is suspended
            $school = \App\Models\School::where('tenant_id', tenant('id'))->first();
            if ($school && $school->status === 'inactive' && ! $user->hasRole('admin')) {
                Auth::guard('web')->logout();
                return back()->withErrors([
                    'email' => 'This school is currently suspended. Please contact your administrator.',
                ])->onlyInput('email');
            }

            if ($user->hasAnyRole(['admin', 'branch_manager']) && $user->hasEnabledTwoFactorAuthentication()) {
                // Step back out — require TOTP before granting session
                Auth::guard('web')->logout();

                session([
                    'tfa_user_id'   => $user->id,
                    'tfa_tenant_id' => tenant('id'),
                    'tfa_remember'  => $request->boolean('remember'),
                ]);

                return redirect()->route('tenant.admin.two-factor.challenge');
            }

            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $user->update([
                'last_login'            => now(),
                'last_login_ip'         => $request->ip(),
                'last_login_user_agent' => $request->userAgent(),
                'login_count'           => $user->login_count + 1,
                'active_session_id'     => session()->getId(),
            ]);

            activity_async('User logged in', null, ['ip' => $request->ip()], 'user-actions');

            return redirect()->intended(route('tenant.admin.dashboard'));
        }

        RateLimiter::hit($throttleKey, decaySeconds: 120);
        $remaining = RateLimiter::remaining($throttleKey, maxAttempts: 5);

        return back()->withErrors([
            'email' => $remaining > 0
                ? "Invalid credentials. {$remaining} attempt(s) remaining before a 2-minute lockout."
                : 'Invalid credentials.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('web')->user();

        if ($user) {
            $user->update(['active_session_id' => null]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tenant.login');
    }
}
