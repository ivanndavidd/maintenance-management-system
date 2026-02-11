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
    protected function debug(string $msg): void
    {
        file_put_contents(
            storage_path('logs/debug-redirect.log'),
            date('H:i:s') . ' ' . $msg . "\n",
            FILE_APPEND,
        );
    }

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName() ?? 'unknown';
        $path = $request->path();

        $this->debug(">>> [{$path}] route={$routeName} method={$request->method()}");

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

        /*
        |--------------------------------------------------------------------------
        | SAFE AUTH VALIDATION (NO SESSION HACKING)
        |--------------------------------------------------------------------------
        */

        if (Auth::check()) {
            try {
                $userExists = DB::connection('site')
                    ->table('users')
                    ->where('id', Auth::id())
                    ->exists();

                if (!$userExists) {
                    $this->debug("[{$path}] User not found in site DB → logout");
                    Auth::logout();
                    return redirect()->route('login');
                }
            } catch (\Exception $e) {
                $this->debug("[{$path}] Auth validation error: " . $e->getMessage());
                Auth::logout();
                return redirect()->route('login');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Redirect authenticated user away from login page
        |--------------------------------------------------------------------------
        */

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

            // If no valid role
            Auth::logout();
            return redirect()->route('login');
        }

        // Store site name in session
        session(['current_site_name' => $site->name]);

        // Share to views
        view()->share('currentSite', $site);

        return $next($request);
    }

    protected function configureSiteConnection(Site $site): void
    {
        Config::set('database.connections.site.database', $site->database_name);

        DB::purge('site');
        DB::reconnect('site');
        Config::set('database.default', 'site');
    }
}
