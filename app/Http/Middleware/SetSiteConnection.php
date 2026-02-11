<?php

namespace App\Http\Middleware;

use App\Models\Site;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetSiteConnection
{
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

        Log::info("SetSiteConnection [{$path}] route={$routeName} siteCode={$siteCode} hasAuthSession={$hasAuthSession}");

        // Skip for auth routes when no site selected
        if (!$siteCode && ($request->routeIs('login') || $request->routeIs('register') || $request->routeIs('password.*'))) {
            Log::info("SetSiteConnection [{$path}] SKIP: auth route, no site code");
            return $next($request);
        }

        if (!$siteCode) {
            Log::info("SetSiteConnection [{$path}] REDIRECT: no site code -> site.select");
            return redirect()->route('site.select');
        }

        // Get site from central database
        try {
            $site = Site::on('central')->where('code', $siteCode)->where('is_active', true)->first();
        } catch (\Exception $e) {
            Log::error("SetSiteConnection [{$path}] Central DB error: " . $e->getMessage());
            $request->session()->forget(['current_site_code', 'current_site_name']);
            $request->session()->save();
            return redirect()->route('site.select');
        }

        if (!$site) {
            Log::warning("SetSiteConnection [{$path}] Site not found for code: {$siteCode}");
            $request->session()->forget(['current_site_code', 'current_site_name']);
            $request->session()->save();
            return redirect()->route('site.select')->with('error', 'Site not found or inactive.');
        }

        // Configure and verify site database connection
        try {
            $this->configureSiteConnection($site);
            DB::connection('site')->getPdo();
            Log::info("SetSiteConnection [{$path}] DB connected: {$site->database_name}");
        } catch (\Exception $e) {
            Log::error("SetSiteConnection [{$path}] DB connection failed for {$site->database_name}: " . $e->getMessage());
            $request->session()->forget(['current_site_code', 'current_site_name']);
            $request->session()->save();
            return redirect()->route('site.select')->with('error', "Site '{$site->name}' database is not available. Please contact administrator.");
        }

        // After switching DB, check if current auth session is valid for this site
        if ($hasAuthSession) {
            $sessionUserId = $request->session()->get($authSessionKey);
            Log::info("SetSiteConnection [{$path}] Auth session found, user_id={$sessionUserId}");
            try {
                $userExists = DB::connection('site')->table('users')->where('id', $sessionUserId)->exists();
                if (!$userExists) {
                    Log::warning("SetSiteConnection [{$path}] User {$sessionUserId} NOT found in {$site->database_name}, clearing auth");
                    Auth::guard('web')->forgetUser();
                    $request->session()->forget($authSessionKey);
                    $request->session()->regenerateToken();
                }
            } catch (\Exception $e) {
                Log::error("SetSiteConnection [{$path}] Auth check error: " . $e->getMessage());
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
