<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Redirect based on role
                if ($user->hasRole(['admin', 'super-admin'])) {
                    return redirect()->route('admin.dashboard');
                } elseif ($user->hasRole('user')) {
                    return redirect()->route('user.dashboard');
                } else {
                    // No role assigned
                    Auth::logout();
                    return redirect()
                        ->route('login')
                        ->with('error', 'No role assigned to your account.');
                }
            }
        }

        return $next($request);
    }
}
