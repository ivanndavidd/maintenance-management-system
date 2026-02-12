<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetSiteConnection
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware for site selection routes
        if ($request->routeIs('site.*') || $request->routeIs('sites.*')) {
            return $next($request);
        }

        // Skip for health check & force logout
        if ($request->routeIs('force-logout') || $request->is('health') || $request->is('up')) {
            return $next($request);
        }

        // Allow auth routes if site not selected yet
        $siteCode = session('current_site_code');

        if (
            !$siteCode &&
            ($request->routeIs('login') ||
                $request->routeIs('register') ||
                $request->routeIs('password.*'))
        ) {
            return $next($request);
        }

        if (!$siteCode) {
            return redirect()->route('site.select');
        }

        // Get site from central database
        try {
            $site = Site::on('central')
                ->where('code', $siteCode)
                ->where('is_active', true)
                ->first();
        } catch (\Exception $e) {
            $request->session()->forget(['current_site_code', 'current_site_name']);
            return redirect()->route('site.select');
        }

        if (!$site) {
            $request->session()->forget(['current_site_code', 'current_site_name']);
            return redirect()->route('site.select')->with('error', 'Site not found or inactive.');
        }

        // Configure site DB connection
        try {
            $this->configureSiteConnection($site);
            DB::connection('site')->getPdo();
        } catch (\Exception $e) {
            $request->session()->forget(['current_site_code', 'current_site_name']);
            return redirect()
                ->route('site.select')
                ->with('error', "Site '{$site->name}' database is not available.");
        }

        // Validate auth session matches current site
        if (Auth::check()) {
            try {
                $authSite = session('auth_site');
                $currentSite = session('current_site_code');

                if ($authSite !== $currentSite) {
                    Auth::logout();
                    session()->invalidate();
                    session()->regenerateToken();
                    return redirect()->route('login');
                }

                // Validate user exists in site DB
                $userExists = DB::connection('site')
                    ->table('users')
                    ->where('id', Auth::id())
                    ->where('email', Auth::user()->email)
                    ->exists();

                if (!$userExists) {
                    Auth::logout();
                    session()->invalidate();
                    session()->regenerateToken();
                    return redirect()->route('login');
                }
            } catch (\Exception $e) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                return redirect()->route('login');
            }
        }

        // Redirect authenticated user away from login page
        if (Auth::check() && ($request->routeIs('login') || $request->routeIs('register'))) {
            $user = Auth::user();

            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->hasRole('supervisor_maintenance')) {
                return redirect()->route('supervisor.dashboard');
            }

            if ($user->hasRole('staff_maintenance')) {
                return redirect()->route('user.dashboard');
            }

            if ($user->hasRole('pic')) {
                return redirect()->route('pic.dashboard');
            }

            Auth::logout();
            return redirect()->route('login');
        }

        // Store site name in session & share to views
        session(['current_site_name' => $site->name]);
        view()->share('currentSite', $site);

        return $next($request);
    }

    protected function configureSiteConnection(Site $site): void
    {
        Config::set('database.connections.site.database', $site->database_name);

        DB::purge('site');
        DB::reconnect('site');

        // Set 'site' as the default connection so that all models
        // (including Spatie Permission) use the correct site database
        Config::set('database.default', 'site');

        // Force Auth guard to forget any cached user so it re-resolves
        // from the newly configured site connection
        Auth::guard('web')->forgetUser();

        // Clear Spatie permission cache for fresh role/permission checks
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
