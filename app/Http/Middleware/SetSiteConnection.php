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
        $path = $request->path();
        $siteCode = session('current_site_code');
        $this->debug(">>> [{$request->method()} {$path}] siteCode={$siteCode} authSite=" . session('auth_site'));

        // Skip middleware for site selection routes
        if ($request->routeIs('site.*') || $request->routeIs('sites.*')) {
            return $next($request);
        }

        // Skip for health check & force logout
        if ($request->routeIs('force-logout') || $request->is('health') || $request->is('up')) {
            return $next($request);
        }

        // Allow auth routes if site not selected yet
        if (
            !$siteCode &&
            ($request->routeIs('login') ||
                $request->routeIs('register') ||
                $request->routeIs('password.*'))
        ) {
            $this->debug("[{$path}] no siteCode, allowing auth route");
            return $next($request);
        }

        if (!$siteCode) {
            $this->debug("[{$path}] no siteCode -> site.select");
            return redirect()->route('site.select');
        }

        // Get site from central database
        try {
            $site = Site::on('central')
                ->where('code', $siteCode)
                ->where('is_active', true)
                ->first();
        } catch (\Exception $e) {
            $this->debug("[{$path}] central DB error: {$e->getMessage()}");
            $request->session()->forget(['current_site_code', 'current_site_name']);
            return redirect()->route('site.select');
        }

        if (!$site) {
            $this->debug("[{$path}] site not found for code={$siteCode}");
            $request->session()->forget(['current_site_code', 'current_site_name']);
            return redirect()->route('site.select')->with('error', 'Site not found or inactive.');
        }

        // Configure site DB connection
        try {
            $this->configureSiteConnection($site);
            DB::connection('site')->getPdo();
            $this->debug("[{$path}] site DB OK: {$site->database_name}");
        } catch (\Exception $e) {
            $this->debug("[{$path}] site DB error: {$e->getMessage()}");
            $request->session()->forget(['current_site_code', 'current_site_name']);
            return redirect()
                ->route('site.select')
                ->with('error', "Site '{$site->name}' database is not available.");
        }

        // Validate auth session matches current site
        if (Auth::check()) {
            $user = Auth::user();
            $this->debug("[{$path}] AUTH id=" . Auth::id() . " email={$user->email} db=" . $user->getConnection()->getDatabaseName());
            try {
                $authSite = session('auth_site');
                $currentSite = session('current_site_code');

                if ($authSite !== $currentSite) {
                    $this->debug("[{$path}] SITE MISMATCH auth={$authSite} current={$currentSite} -> logout");
                    Auth::logout();
                    session()->invalidate();
                    session()->regenerateToken();
                    return redirect()->route('login');
                }

                $userExists = DB::connection('site')
                    ->table('users')
                    ->where('id', Auth::id())
                    ->where('email', Auth::user()->email)
                    ->exists();

                $this->debug("[{$path}] userExists={$userExists}");

                if (!$userExists) {
                    $this->debug("[{$path}] user NOT in site DB -> logout");
                    Auth::logout();
                    session()->invalidate();
                    session()->regenerateToken();
                    return redirect()->route('login');
                }
            } catch (\Exception $e) {
                $this->debug("[{$path}] EXCEPTION: {$e->getMessage()}");
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                return redirect()->route('login');
            }
        } else {
            $this->debug("[{$path}] not authenticated");
        }

        // Redirect authenticated user away from login page
        if (Auth::check() && ($request->routeIs('login') || $request->routeIs('register'))) {
            $user = Auth::user();
            $roles = $user->getRoleNames()->toArray();
            $this->debug("[{$path}] LOGIN PAGE roles=" . json_encode($roles));

            if ($user->hasRole('admin')) {
                $this->debug("[{$path}] -> admin.dashboard");
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

            $this->debug("[{$path}] NO ROLE -> logout");
            Auth::logout();
            return redirect()->route('login');
        }

        session(['current_site_name' => $site->name]);
        view()->share('currentSite', $site);

        $this->debug("[{$path}] PASS THROUGH");
        return $next($request);
    }

    protected function configureSiteConnection(Site $site): void
    {
        Config::set('database.connections.site.database', $site->database_name);

        DB::purge('site');
        DB::reconnect('site');

        Config::set('database.default', 'site');

        Auth::guard('web')->forgetUser();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
