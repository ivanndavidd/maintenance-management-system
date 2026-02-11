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
            try {
                if (Auth::guard($guard)->check()) {
                    $user = Auth::guard($guard)->user();

                    // User session exists but user not found in current site DB
                    if (!$user) {
                        $this->clearAuthWithoutDestroyingSession($request, $guard);
                        return $next($request);
                    }

                    // Redirect based on role
                    if ($user->hasRole('admin')) {
                        return redirect()->route('admin.dashboard');
                    } elseif ($user->hasRole('supervisor_maintenance')) {
                        return redirect()->route('supervisor.dashboard');
                    } elseif ($user->hasRole('staff_maintenance')) {
                        return redirect()->route('user.dashboard');
                    } elseif ($user->hasRole('pic')) {
                        return redirect()->route('pic.dashboard');
                    } else {
                        $this->clearAuthWithoutDestroyingSession($request, $guard);
                        return redirect()
                            ->route('login')
                            ->with('error', 'No role assigned to your account.');
                    }
                }
            } catch (\Exception $e) {
                // Auth check failed (e.g. user table not accessible in site DB)
                $this->clearAuthWithoutDestroyingSession($request, $guard);
                return $next($request);
            }
        }

        return $next($request);
    }

    /**
     * Clear auth session data without destroying the entire session.
     * This preserves current_site_code and other session data.
     */
    protected function clearAuthWithoutDestroyingSession(Request $request, ?string $guard): void
    {
        $guardName = $guard ?? 'web';
        Auth::guard($guardName)->forgetUser();
        $request->session()->forget('login_' . $guardName . '_' . sha1('Illuminate\Auth\SessionGuard'));
        $request->session()->regenerateToken();
    }
}
