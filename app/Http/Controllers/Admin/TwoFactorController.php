<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\RegenerateTwoFactorRecoveryCodes;

class TwoFactorController extends Controller
{
    /** Step 1: generate secret + QR, save to user (unconfirmed) */
    public function enable(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $enable($request->user());

        return back()->with('2fa_status', 'setup');
    }

    /** Step 2: confirm with TOTP code from authenticator app */
    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $request->validate(['code' => ['required', 'string']]);

        try {
            $confirm($request->user(), $request->code);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors(['2fa_code' => 'Invalid code. Please check your authenticator app and try again.']);
        }

        return back()->with('2fa_status', 'confirmed');
    }

    /** Disable 2FA entirely (requires password confirmation) */
    public function disable(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $request->validate(['password' => ['required', 'string']]);

        if (! Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors(['2fa_password' => 'The password you entered is incorrect.']);
        }

        $disable($request->user());

        return back()->with('2fa_status', 'disabled');
    }

    /** Regenerate recovery codes (requires password confirmation) */
    public function regenerateCodes(Request $request, RegenerateTwoFactorRecoveryCodes $regen)
    {
        $request->validate(['password' => ['required', 'string']]);

        if (! Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors(['2fa_password_regen' => 'The password you entered is incorrect.']);
        }

        $regen($request->user());

        return back()->with('2fa_status', 'codes_regenerated');
    }
}
