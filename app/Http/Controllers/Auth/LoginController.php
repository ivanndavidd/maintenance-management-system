<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Handle a login request to the application.
     * Override to support central admin authentication across all sites.
     */

    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application.
        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        // Try to authenticate - first check site DB, then central DB for admins
        if ($this->attemptLoginWithFallback($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Attempt login with fallback to central database for admins.
     */

    protected function attemptLoginWithFallback(Request $request): bool
    {
        $email = $request->input('email');
        $password = $request->input('password');

        // First, check if user is a central admin and sync their role to site DB
        try {
            $centralUser = $this->getCentralAdminUser($email, $password);

            if ($centralUser) {
                // Sync central admin to current site database (creates or updates user + assigns admin role)
                $siteUser = $this->syncCentralAdminToSite($centralUser);

                if ($siteUser) {
                    Auth::login($siteUser, $request->boolean('remember'));
                    return true;
                }
            }
        } catch (\Exception $e) {
            // Central DB not available, skip central login check
        }

        // If not a central admin, try normal site database login
        $siteUser = User::where('email', $email)->where('is_active', true)->first();

        if ($siteUser && Hash::check($password, $siteUser->password)) {
            // If user has admin role, verify they have site access in central DB
            if ($siteUser->hasRole('admin')) {
                try {
                    $currentSiteCode = session('current_site_code');
                    if ($currentSiteCode) {
                        $centralUserId = DB::connection('central')
                            ->table('users')
                            ->where('email', $email)
                            ->value('id');

                        if ($centralUserId) {
                            $hasSiteAccess = DB::connection('central')
                                ->table('site_user')
                                ->join('sites', 'site_user.site_id', '=', 'sites.id')
                                ->where('site_user.user_id', $centralUserId)
                                ->where('sites.code', $currentSiteCode)
                                ->exists();

                            if (!$hasSiteAccess) {
                                return false;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Central DB not available, allow site-level login
                }
            }

            Auth::login($siteUser, $request->boolean('remember'));
            return true;
        }

        return false;
    }

    /**
     * Get admin user from central database.
     */
    protected function getCentralAdminUser(string $email, string $password): ?object
    {
        // Query central database
        $centralUser = DB::connection('central')
            ->table('users')
            ->where('email', $email)
            ->where('is_active', true)
            ->first();

        if (!$centralUser) {
            return null;
        }

        // Verify password
        if (!Hash::check($password, $centralUser->password)) {
            return null;
        }

        // Check if user has admin role in central database
        $hasAdminRole = DB::connection('central')
            ->table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $centralUser->id)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->where('roles.name', 'admin')
            ->exists();

        if (!$hasAdminRole) {
            return null;
        }

        // Check if user has access to the current site
        $currentSiteCode = session('current_site_code');
        if ($currentSiteCode) {
            $hasSiteAccess = DB::connection('central')
                ->table('site_user')
                ->join('sites', 'site_user.site_id', '=', 'sites.id')
                ->where('site_user.user_id', $centralUser->id)
                ->where('sites.code', $currentSiteCode)
                ->exists();

            if (!$hasSiteAccess) {
                return null;
            }
        }

        return $centralUser;
    }

    /**
     * Sync central admin user to current site database.
     */
    protected function syncCentralAdminToSite(object $centralUser): ?User
    {
        // Ensure all required roles exist in site database
        $this->ensureRolesExist();

        // Check if user already exists in site database
        $siteUser = User::where('email', $centralUser->email)->first();

        if ($siteUser) {
            // Update existing user
            $siteUser->update([
                'name' => $centralUser->name,
                'password' => $centralUser->password,
                'is_active' => true,
            ]);
        } else {
            // Create new user in site database
            $siteUser = User::create([
                'name' => $centralUser->name,
                'email' => $centralUser->email,
                'password' => $centralUser->password,
                'is_active' => true,
                'email_verified_at' => $centralUser->email_verified_at,
            ]);
        }

        // Ensure user has admin role in site database
        if (!$siteUser->hasRole('admin')) {
            $siteUser->assignRole('admin');
        }

        return $siteUser;
    }

    /**
     * Ensure all required roles exist in the current site database.
     */
    protected function ensureRolesExist(): void
    {
        $roles = ['admin', 'supervisor_maintenance', 'staff_maintenance'];

        foreach ($roles as $roleName) {
            \Spatie\Permission\Models\Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }
    }

    /**
     * Override sendFailedLoginResponse to show custom message for inactive users
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        // Check if user exists but is inactive
        if ($user && !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => [
                    'Your account is pending approval. Please wait for administrator activation.',
                ],
            ]);
        }

        // Default error message
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        // Store the site where the user authenticated
        $request->session()->put('auth_site', session('current_site_code'));

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended($this->redirectPath());
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // ✅ Hapus auth_site
        $request->session()->forget('auth_site');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
