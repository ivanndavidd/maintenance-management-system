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
                $guardName = $guard ?? 'web';

                if (Auth::guard($guardName)->check()) {
                    $user = Auth::guard($guardName)->user();

                    if (!$user) {
                        $this->clearAuthWithoutDestroyingSession($request, $guard);
                        return $next($request);
                    }

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
                $this->clearAuthWithoutDestroyingSession($request, $guard);
                return $next($request);
            }
        }

        return $next($request);
    }

    protected function clearAuthWithoutDestroyingSession(Request $request, ?string $guard): void
    {
        $guardName = $guard ?? 'web';
        Auth::guard($guardName)->forgetUser();
        $request->session()->forget('login_' . $guardName . '_' . sha1('Illuminate\Auth\SessionGuard'));
        $request->session()->regenerateToken();
    }
}
