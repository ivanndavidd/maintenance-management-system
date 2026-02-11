<?php

namespace App\Http\Middleware;

use App\Models\Site;
use App\Models\User;
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
        file_put_contents(storage_path('logs/debug-redirect.log'), date('H:i:s') . ' ' . $msg . "\n", FILE_APPEND);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName() ?? 'unknown';
        $path = $request->path();

        // Skip middleware for site selection routes
        if ($request->routeIs('site.*') || $request->routeIs('sites.*')) {
            return $next($request);
        }

        // Skip for health check, force-logout
        if ($request->routeIs('force-logout') || $request->is('health') || $request->is('up')) {
            return $next($request);
        }

        // Check if site is selected in session
        $siteCode = session('current_site_code');
        $authSessionKey = 'login_web_' . sha1('Illuminate\Auth\SessionGuard');
        $hasAuthSession = $request->session()->has($authSessionKey);

        $this->debug("[{$path}] route={$routeName} siteCode={$siteCode} hasAuth={$hasAuthSession}");

        // Skip for auth routes when no site selected
        if (!$siteCode && ($request->routeIs('login') || $request->routeIs('register') || $request->routeIs('password.*'))) {
            $this->debug("[{$path}] SKIP: auth route, no site code");
            return $next($request);
        }

        if (!$siteCode) {
            $this->debug("[{$path}] REDIRECT -> site.select (no site code)");
            return redirect()->route('site.select');
        }

        // Get site from central database
        try {
            $site = Site::on('central')->where('code', $siteCode)->where('is_active', true)->first();
        } catch (\Exception $e) {
            $this->debug("[{$path}] ERROR central DB: " . $e->getMessage());
            $request->session()->forget(['current_site_code', 'current_site_name']);
            $request->session()->save();
            return redirect()->route('site.select');
        }

        if (!$site) {
            $this->debug("[{$path}] Site not found: {$siteCode}");
            $request->session()->forget(['current_site_code', 'current_site_name']);
            $request->session()->save();
            return redirect()->route('site.select')->with('error', 'Site not found or inactive.');
        }

        // Configure and verify site database connection
        try {
            $this->configureSiteConnection($site);
            DB::connection('site')->getPdo();
            $this->debug("[{$path}] DB OK: {$site->database_name}");
        } catch (\Exception $e) {
            $this->debug("[{$path}] DB FAIL {$site->database_name}: " . $e->getMessage());
            $request->session()->forget(['current_site_code', 'current_site_name']);
            $request->session()->save();
            return redirect()->route('site.select')->with('error', "Site '{$site->name}' database is not available. Please contact administrator.");
        }

        // After switching DB, check if current auth session is valid for this site
        if ($hasAuthSession) {
            $sessionUserId = $request->session()->get($authSessionKey);
            $this->debug("[{$path}] Auth session user_id={$sessionUserId}");
            try {
                $userExists = DB::connection('site')->table('users')->where('id', $sessionUserId)->exists();
                if (!$userExists) {
                    $this->debug("[{$path}] User {$sessionUserId} NOT in {$site->database_name}, clearing");
                    Auth::guard('web')->forgetUser();
                    $request->session()->forget($authSessionKey);
                    $request->session()->regenerateToken();
                }
            } catch (\Exception $e) {
                $this->debug("[{$path}] Auth check error: " . $e->getMessage());
                Auth::guard('web')->forgetUser();
                $request->session()->forget($authSessionKey);
                $request->session()->regenerateToken();
            }
        }

        // Store site info in session for easy access
        session(['current_site_name' => $site->name]);

        // Share site info with all views
        view()->share('currentSite', $site);

        return $next($request);
    }

    /**
     * Configure the site database connection
     */
    protected function configureSiteConnection(Site $site): void
    {
        // Update the 'site' connection configuration
        Config::set('database.connections.site.database', $site->database_name);

        // Purge and reconnect
        DB::purge('site');
        DB::reconnect('site');

        // Set the default connection to site
        Config::set('database.default', 'site');
    }
}
