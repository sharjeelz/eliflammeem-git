<?php

namespace App\Http\Controllers\TenantAuth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request)
    {
        if (! session('tfa_user_id')) {
            return redirect()->route('tenant.login');
        }

        $school = \App\Models\School::where('tenant_id', tenant('id'))->first();

        return view('tenant.admin.two_factor_challenge', compact('school'));
    }

    public function store(Request $request, TwoFactorAuthenticationProvider $provider)
    {
        $userId   = session('tfa_user_id');
        $tenantId = session('tfa_tenant_id');
        $remember = session('tfa_remember', false);

        if (! $userId || ! $tenantId) {
            return redirect()->route('tenant.login');
        }

        $user = User::withTrashed()
            ->where('id', $userId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $user) {
            return redirect()->route('tenant.login');
        }

        $valid = false;

        if ($code = $request->input('code')) {
            // Validate TOTP code (strip spaces)
            $valid = $user->two_factor_secret &&
                     $provider->verify(decrypt($user->two_factor_secret), str_replace(' ', '', $code));

        } elseif ($recoveryCode = $request->input('recovery_code')) {
            // Validate recovery code and consume it
            $codes = json_decode(decrypt($user->two_factor_recovery_codes ?? ''), true);
            $idx   = is_array($codes) ? array_search($recoveryCode, $codes) : false;

            if ($idx !== false) {
                $valid = true;
                unset($codes[$idx]);
                $user->forceFill([
                    'two_factor_recovery_codes' => encrypt(json_encode(array_values($codes))),
                ])->save();
            }
        }

        if (! $valid) {
            return back()->withErrors(['code' => 'The provided authentication code was invalid.']);
        }

        // Clear 2FA session state and complete the login
        $request->session()->forget(['tfa_user_id', 'tfa_tenant_id', 'tfa_remember']);
        $request->session()->regenerate();

        Auth::guard('web')->login($user, $remember);

        $user->update([
            'last_login'            => now(),
            'last_login_ip'         => $request->ip(),
            'last_login_user_agent' => $request->userAgent(),
            'login_count'           => $user->login_count + 1,
            'active_session_id'     => session()->getId(),
        ]);

        activity_async('User logged in (2FA)', null, ['ip' => $request->ip()], 'user-actions');

        return redirect()->intended(route('tenant.admin.dashboard'));
    }
}
