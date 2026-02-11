<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    protected function debug(string $msg): void
    {
        file_put_contents(storage_path('logs/debug-redirect.log'), date('H:i:s') . ' ' . $msg . "\n", FILE_APPEND);
    }

    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;
        $path = $request->path();

        foreach ($guards as $guard) {
            try {
                $guardName = $guard ?? 'web';
                $isAuthenticated = Auth::guard($guardName)->check();
                $this->debug("RedirectIfAuth [{$path}] authenticated=" . ($isAuthenticated ? 'true' : 'false'));

                if ($isAuthenticated) {
                    $user = Auth::guard($guardName)->user();

                    if (!$user) {
                        $this->debug("RedirectIfAuth [{$path}] user() returned null, clearing");
                        $this->clearAuthWithoutDestroyingSession($request, $guard);
                        return $next($request);
                    }

                    $roles = $user->getRoleNames()->toArray();
                    $this->debug("RedirectIfAuth [{$path}] user={$user->id} email={$user->email} roles=" . json_encode($roles));

                    if ($user->hasRole('admin')) {
                        $this->debug("RedirectIfAuth [{$path}] -> admin.dashboard");
                        return redirect()->route('admin.dashboard');
                    } elseif ($user->hasRole('supervisor_maintenance')) {
                        $this->debug("RedirectIfAuth [{$path}] -> supervisor.dashboard");
                        return redirect()->route('supervisor.dashboard');
                    } elseif ($user->hasRole('staff_maintenance')) {
                        $this->debug("RedirectIfAuth [{$path}] -> user.dashboard");
                        return redirect()->route('user.dashboard');
                    } elseif ($user->hasRole('pic')) {
                        $this->debug("RedirectIfAuth [{$path}] -> pic.dashboard");
                        return redirect()->route('pic.dashboard');
                    } else {
                        $this->debug("RedirectIfAuth [{$path}] NO ROLE! clearing auth -> login");
                        $this->clearAuthWithoutDestroyingSession($request, $guard);
                        return redirect()
                            ->route('login')
                            ->with('error', 'No role assigned to your account.');
                    }
                }
            } catch (\Exception $e) {
                $this->debug("RedirectIfAuth [{$path}] EXCEPTION: " . $e->getMessage());
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
