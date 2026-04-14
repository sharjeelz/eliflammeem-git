<?php

namespace App\Http\Controllers\TenantAuth;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, string $token)
    {
        $school = School::where('tenant_id', tenant('id'))->first();

        return view('tenant.auth.reset_password', [
            'token'  => $token,
            'email'  => $request->query('email', ''),
            'school' => $school,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // Pass raw password — the User model's 'hashed' cast handles bcrypt
                $user->password = $password;
                $user->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Clear login rate-limit so the user can sign in immediately after reset
            RateLimiter::clear(Str::lower($request->input('email')) . '|' . $request->ip());
            return redirect()->route('tenant.login')->with('status', 'Your password has been reset. Please sign in.');
        }

        return back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
    }
}
