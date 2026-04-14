<?php

namespace App\Http\Controllers\TenantAuth;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showForgotForm()
    {
        $school = School::where('tenant_id', tenant('id'))->first();

        return view('tenant.auth.forgot_password', compact('school'));
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // Send the reset link — broker scopes to users table (BelongsToTenant filters by tenant)
        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        Log::info('Password reset requested', [
            'email'  => $request->email,
            'tenant' => tenant('id'),
            'status' => $status,
        ]);

        // Show throttle error explicitly — user needs to know to wait
        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors(['email' => 'Please wait 30 seconds before requesting another reset link.'])->withInput();
        }

        // For both RESET_LINK_SENT and INVALID_USER return the same success message
        // (anti-enumeration: don't reveal whether the email exists in our system)
        return back()->with('status', 'If that email is registered, you will receive a reset link shortly.');
    }
}
