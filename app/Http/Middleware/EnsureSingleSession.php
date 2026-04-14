<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSingleSession
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('web')->user();

        if ($user) {
            $storedId  = $user->active_session_id;
            $currentId = session()->getId();

            // Kick if: session was force-terminated (sentinel) OR a different session is now active
            if ($storedId === 'terminated' || ($storedId && $storedId !== $currentId)) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = $storedId === 'terminated'
                    ? 'Your session was terminated by an administrator.'
                    : 'Your account was signed in from another location. You have been logged out.';

                return redirect()->route('tenant.login')->withErrors(['email' => $message]);
            }
        }

        return $next($request);
    }
}
