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
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
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

        // Skip for auth routes when no site selected
        if (!$siteCode && ($request->routeIs('login') || $request->routeIs('register') || $request->routeIs('password.*'))) {
            return $next($request);
        }

        if (!$siteCode) {
            return redirect()->route('site.select');
        }

        // Get site from central database
        try {
            $site = Site::on('central')->where('code', $siteCode)->where('is_active', true)->first();
        } catch (\Exception $e) {
            // Central DB not available — clear and redirect
            $request->session()->forget(['current_site_code', 'current_site_name']);
            $request->session()->save();
            return redirect()->route('site.select');
        }

        if (!$site) {
            $request->session()->forget(['current_site_code', 'current_site_name']);
            $request->session()->save();
            return redirect()->route('site.select')->with('error', 'Site not found or inactive.');
        }

        // Configure and verify site database connection
        try {
            $this->configureSiteConnection($site);
            DB::connection('site')->getPdo();
        } catch (\Exception $e) {
            $request->session()->forget(['current_site_code', 'current_site_name']);
            $request->session()->save();
            return redirect()->route('site.select')->with('error', "Site '{$site->name}' database is not available. Please contact administrator.");
        }

        // After switching DB, check if current auth session is valid for this site
        if (Auth::check()) {
            try {
                $userId = Auth::id();
                $userExists = DB::connection('site')->table('users')->where('id', $userId)->exists();
                if (!$userExists) {
                    Auth::logout();
                }
            } catch (\Exception $e) {
                Auth::logout();
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
