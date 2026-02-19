<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SiteController extends Controller
{
    /**
     * Show the site selection page
     */
    public function select()
    {
        // If already logged in and site is selected, redirect to dashboard
        if (Auth::check() && session('current_site_code')) {
            $user = Auth::user();

            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->hasRole('supervisor_maintenance')) {
                return redirect()->route('supervisor.dashboard');
            } elseif ($user->hasRole('staff_maintenance')) {
                return redirect()->route('user.dashboard');
            }
        }

        // Skip site selection if sites table doesn't exist yet
        try {
            $sites = Site::on('central')->active()->get();
        } catch (\Exception $e) {
            // Sites table not available, skip to login
            return redirect()->route('login');
        }

        // If no sites configured, skip to login
        if ($sites->isEmpty()) {
            return redirect()->route('login');
        }

        return view('sites.select', compact('sites'));
    }

    /**
     * Handle site selection
     */
    public function choose(Request $request)
    {
        $request->validate([
            'site_code' => 'required|string',
        ]);

        $site = Site::on('central')
            ->where('code', $request->site_code)
            ->where('is_active', true)
            ->first();

        if (!$site) {
            return back()->with('error', 'Invalid site selected.');
        }

        // Store site in session
        session([
            'current_site_code' => $site->code,
            'current_site_name' => $site->name,
        ]);

        // Logout any existing user (they need to login again for the new site)
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Re-store site selection after session invalidate
            session([
                'current_site_code' => $site->code,
                'current_site_name' => $site->name,
            ]);
        }

        return redirect()->route('login')->with('success', "Selected site: {$site->name}. Please login.");
    }

    /**
     * Switch to a different site (requires re-login, for unauthenticated switching)
     */
    public function switch(Request $request)
    {
        // Logout if authenticated
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } else {
            // Just clear site selection if not logged in
            $request->session()->forget(['current_site_code', 'current_site_name']);
        }

        return redirect()->route('site.select');
    }

    /**
     * Switch site directly without re-login (for authenticated users).
     * Checks if user has access to the target site, then switches instantly.
     */
    public function switchDirect(Request $request)
    {
        $request->validate(['site_code' => 'required|string']);

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $targetSite = Site::on('central')
            ->where('code', $request->site_code)
            ->where('is_active', true)
            ->first();

        if (!$targetSite) {
            return back()->with('switch_error', 'Site not found or inactive.');
        }

        // Don't switch if already on the same site
        if (session('current_site_code') === $targetSite->code) {
            return back()->with('switch_info', "You are already on {$targetSite->name}.");
        }

        $email = Auth::user()->email;

        // Configure target site DB temporarily to check if user exists there
        try {
            \Illuminate\Support\Facades\Config::set('database.connections.site.database', $targetSite->database_name);
            \Illuminate\Support\Facades\DB::purge('site');
            \Illuminate\Support\Facades\DB::reconnect('site');

            $userInTargetSite = \Illuminate\Support\Facades\DB::connection('site')
                ->table('users')
                ->where('email', $email)
                ->where('is_active', true)
                ->first();
        } catch (\Exception $e) {
            return back()->with('switch_error', "Cannot connect to {$targetSite->name} database.");
        }

        if (!$userInTargetSite) {
            // Restore original site connection before returning error
            $currentSite = Site::on('central')->where('code', session('current_site_code'))->first();
            if ($currentSite) {
                \Illuminate\Support\Facades\Config::set('database.connections.site.database', $currentSite->database_name);
                \Illuminate\Support\Facades\DB::purge('site');
                \Illuminate\Support\Facades\DB::reconnect('site');
            }
            return back()->with('switch_error', "You don't have access to {$targetSite->name}.");
        }

        // User exists — perform the switch
        \Illuminate\Support\Facades\Config::set('database.default', 'site');
        \Auth::guard('web')->forgetUser();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $newSiteUser = \App\Models\User::where('email', $email)->first();

        if (!$newSiteUser) {
            return back()->with('switch_error', "Your account was not found in {$targetSite->name}.");
        }

        // Login with new site's user record (uses session regenerate internally)
        Auth::login($newSiteUser);

        // Set site session AFTER login so it's not lost by session regeneration
        request()->session()->put('current_site_code', $targetSite->code);
        request()->session()->put('current_site_name', $targetSite->name);
        request()->session()->put('auth_site', $targetSite->code);

        // Redirect to appropriate dashboard
        if ($newSiteUser->hasRole('admin')) {
            return redirect()->route('admin.dashboard')->with('success', "Switched to {$targetSite->name}.");
        } elseif ($newSiteUser->hasRole('supervisor_maintenance')) {
            return redirect()->route('supervisor.dashboard')->with('success', "Switched to {$targetSite->name}.");
        } elseif ($newSiteUser->hasRole('staff_maintenance')) {
            return redirect()->route('user.dashboard')->with('success', "Switched to {$targetSite->name}.");
        }

        return redirect('/dashboard')->with('success', "Switched to {$targetSite->name}.");
    }

    /**
     * Get current site info (API)
     */
    public function current()
    {
        $siteCode = session('current_site_code');

        if (!$siteCode) {
            return response()->json(['error' => 'No site selected'], 400);
        }

        $site = Site::on('central')->where('code', $siteCode)->first();

        return response()->json([
            'code' => $site->code,
            'name' => $site->name,
            'logo' => $site->logo,
        ]);
    }
}
