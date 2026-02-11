<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;
        $path = $request->path();

        foreach ($guards as $guard) {
            try {
                $guardName = $guard ?? 'web';
                $isAuthenticated = Auth::guard($guardName)->check();
                Log::info("RedirectIfAuthenticated [{$path}] guard={$guardName} authenticated=" . ($isAuthenticated ? 'true' : 'false'));

                if ($isAuthenticated) {
                    $user = Auth::guard($guardName)->user();

                    if (!$user) {
                        Log::warning("RedirectIfAuthenticated [{$path}] Auth check true but user() returned null, clearing auth");
                        $this->clearAuthWithoutDestroyingSession($request, $guard);
                        return $next($request);
                    }

                    $roles = $user->getRoleNames()->toArray();
                    Log::info("RedirectIfAuthenticated [{$path}] User: id={$user->id} email={$user->email} roles=" . json_encode($roles));

                    if ($user->hasRole('admin')) {
                        return redirect()->route('admin.dashboard');
                    } elseif ($user->hasRole('supervisor_maintenance')) {
                        return redirect()->route('supervisor.dashboard');
                    } elseif ($user->hasRole('staff_maintenance')) {
                        return redirect()->route('user.dashboard');
                    } elseif ($user->hasRole('pic')) {
                        return redirect()->route('pic.dashboard');
                    } else {
                        Log::warning("RedirectIfAuthenticated [{$path}] User {$user->id} has no recognized role, clearing auth");
                        $this->clearAuthWithoutDestroyingSession($request, $guard);
                        return redirect()
                            ->route('login')
                            ->with('error', 'No role assigned to your account.');
                    }
                }
            } catch (\Exception $e) {
                Log::error("RedirectIfAuthenticated [{$path}] Exception: " . $e->getMessage());
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
