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
        // Check if site is selected in session
        $siteCode = session('current_site_code');

        // Skip middleware for site selection routes and auth routes
        if ($request->routeIs('site.*') || $request->routeIs('sites.*')) {
            return $next($request);
        }

        // Skip for login/register routes when no site selected (allow central DB auth for admins)
        if (!$siteCode && ($request->routeIs('login') || $request->routeIs('password.*'))) {
            return $next($request);
        }

        if (!$siteCode) {
            // Redirect to site selection page if not on it already
            return redirect()->route('site.select');
        }

        // Get site from central database
        $site = Site::on('central')->where('code', $siteCode)->where('is_active', true)->first();

        if (!$site) {
            // Clear invalid session and redirect to site selection
            session()->forget(['current_site_code', 'current_site_name']);
            return redirect()->route('site.select')->with('error', 'Site not found or inactive.');
        }

        // Configure the site connection dynamically
        $this->configureSiteConnection($site);

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
