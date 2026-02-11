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
     * Switch to a different site
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
