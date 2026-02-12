<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'department']);

        // Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%") // ✅ ADDED
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->has('role') && $request->role != '') {
            $query->role($request->role);
        }

        // Department filter
        if ($request->has('department') && $request->department != '') {
            $query->where('department_id', $request->department);
        }

        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('is_active', $request->status);
        }

        $users = $query->latest()->paginate(15);

        // Get filter options
        $roles = Role::all();
        $departments = Department::all();

        // Get sites and user site access from central DB for the modal
        $sites = collect();
        $userSiteAccess = [];
        try {
            $sites = DB::connection('central')->table('sites')->where('is_active', true)->get();
            $centralUsers = DB::connection('central')->table('users')->pluck('id', 'email');
            foreach ($users as $user) {
                $centralId = $centralUsers[$user->email] ?? null;
                if ($centralId) {
                    $userSiteAccess[$user->id] = DB::connection('central')
                        ->table('site_user')
                        ->where('user_id', $centralId)
                        ->pluck('site_id')
                        ->toArray();
                }
            }
        } catch (\Exception $e) {
            // Central DB not available
        }

        return view('admin.users.index', compact('users', 'roles', 'departments', 'sites', 'userSiteAccess'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        $departments = Department::all();

        return view('admin.users.create', compact('roles', 'departments'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'employee_id' => ['required', 'string', 'max:50', 'unique:users'], // ✅ ADDED
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role' => ['required', 'exists:roles,name'],
            'is_active' => ['boolean'],
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_id' => $validated['employee_id'], // ✅ ADDED
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'department_id' => $validated['department_id'] ?? null,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Assign role
        $user->assignRole($validated['role']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['roles', 'department']);

        // Calculate user statistics from CMR
        $totalCmr = $user->assignedCmr()->count();
        $completedCmr = $user->assignedCmr()->where('status', 'completed')->count();

        $stats = [
            'total_assigned' => $totalCmr,
            'completed' => $completedCmr,
            'pending' => $user->assignedCmr()->where('status', 'pending')->count(),
            'in_progress' => $user->assignedCmr()->where('status', 'in_progress')->count(),
            'completion_rate' => $totalCmr > 0 ? round(($completedCmr / $totalCmr) * 100, 1) : 0,
        ];

        // Recent CMR tickets
        $recentCmr = $user->assignedCmr()->latest()->limit(10)->get();

        return view('admin.users.show', compact('user', 'stats', 'recentCmr'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $departments = Department::all();
        $userRole = $user->roles->first();

        // Get sites and user site access from central DB
        $sites = collect();
        $userSiteIds = [];
        try {
            $sites = DB::connection('central')->table('sites')->where('is_active', true)->get();
            $centralUserId = DB::connection('central')->table('users')
                ->where('email', $user->email)->value('id');
            if ($centralUserId) {
                $userSiteIds = DB::connection('central')->table('site_user')
                    ->where('user_id', $centralUserId)
                    ->pluck('site_id')
                    ->toArray();
            }
        } catch (\Exception $e) {
            // Central DB not available
        }

        return view('admin.users.edit', compact('user', 'roles', 'departments', 'userRole', 'sites', 'userSiteIds'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
            'employee_id' => [
                // ✅ ADDED
                'required',
                'string',
                'max:50',
                'unique:users,employee_id,' . $user->id,
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role' => ['required', 'exists:roles,name'],
            'is_active' => ['boolean'],
        ]);

        // Update user data
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_id' => $validated['employee_id'], // ✅ ADDED
            'phone' => $validated['phone'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        // Sync role
        $user->syncRoles([$validated['role']]);

        // If role is admin, sync to central database
        if ($validated['role'] === 'admin') {
            $this->syncUserToCentral($user);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'You cannot delete your own account!');
        }


        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User {$status} successfully!");
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Password reset successfully!');
    }

    /**
     * Update site access for a user in central database and sync user to each site DB
     */
    public function updateSiteAccess(Request $request, User $user)
    {
        $validated = $request->validate([
            'site_ids' => ['nullable', 'array'],
            'site_ids.*' => ['integer'],
        ]);

        $siteIds = $validated['site_ids'] ?? [];

        try {
            $centralUserId = $this->getOrCreateCentralUser($user);

            // Sync site_user entries in central DB
            DB::connection('central')->table('site_user')
                ->where('user_id', $centralUserId)
                ->delete();

            foreach ($siteIds as $siteId) {
                DB::connection('central')->table('site_user')->insert([
                    'user_id' => $centralUserId,
                    'site_id' => $siteId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Sync user to each selected site's database
            $selectedSites = DB::connection('central')->table('sites')
                ->whereIn('id', $siteIds)
                ->get();

            $userRole = $user->roles->first()?->name ?? 'staff_maintenance';

            foreach ($selectedSites as $site) {
                $this->syncUserToSiteDatabase($user, $site->database_name, $userRole);
            }

            return redirect()->back()->with('success', 'Site access updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update site access: ' . $e->getMessage());
        }
    }

    /**
     * Sync user to a specific site database (create or update)
     */
    protected function syncUserToSiteDatabase(User $user, string $databaseName, string $roleName): void
    {
        try {
            Config::set('database.connections.site_sync.driver', 'mysql');
            Config::set('database.connections.site_sync.host', config('database.connections.site.host'));
            Config::set('database.connections.site_sync.port', config('database.connections.site.port'));
            Config::set('database.connections.site_sync.database', $databaseName);
            Config::set('database.connections.site_sync.username', config('database.connections.site.username'));
            Config::set('database.connections.site_sync.password', config('database.connections.site.password'));
            Config::set('database.connections.site_sync.charset', 'utf8mb4');
            Config::set('database.connections.site_sync.collation', 'utf8mb4_unicode_ci');

            DB::purge('site_sync');

            $existingUser = DB::connection('site_sync')
                ->table('users')
                ->where('email', $user->email)
                ->first();

            if ($existingUser) {
                DB::connection('site_sync')->table('users')
                    ->where('id', $existingUser->id)
                    ->update([
                        'name' => $user->name,
                        'employee_id' => $user->employee_id,
                        'phone' => $user->phone,
                        'password' => $user->password,
                        'is_active' => $user->is_active,
                        'department_id' => $user->department_id,
                        'updated_at' => now(),
                    ]);
                $siteUserId = $existingUser->id;
            } else {
                $siteUserId = DB::connection('site_sync')->table('users')->insertGetId([
                    'employee_id' => $user->employee_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'email_verified_at' => $user->email_verified_at,
                    'password' => $user->password,
                    'is_active' => $user->is_active,
                    'department_id' => $user->department_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Ensure role exists in target site DB
            $roleId = DB::connection('site_sync')->table('roles')
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->value('id');

            if (!$roleId) {
                $roleId = DB::connection('site_sync')->table('roles')->insertGetId([
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Sync role assignment
            DB::connection('site_sync')->table('model_has_roles')
                ->where('model_id', $siteUserId)
                ->where('model_type', 'App\\Models\\User')
                ->delete();

            DB::connection('site_sync')->table('model_has_roles')->insert([
                'role_id' => $roleId,
                'model_type' => 'App\\Models\\User',
                'model_id' => $siteUserId,
            ]);

            DB::purge('site_sync');
        } catch (\Exception $e) {
            // Log but don't fail - site DB might not be available
            \Log::warning("Failed to sync user {$user->email} to site DB {$databaseName}: " . $e->getMessage());
        }
    }

    /**
     * Sync user to central database with admin role and current site access
     */
    protected function syncUserToCentral(User $user): void
    {
        try {
            $centralUserId = $this->getOrCreateCentralUser($user);

            // Assign admin role in central DB
            $roleId = DB::connection('central')->table('roles')
                ->where('name', 'admin')
                ->where('guard_name', 'web')
                ->value('id');

            if ($roleId) {
                DB::connection('central')->table('model_has_roles')->updateOrInsert(
                    [
                        'role_id' => $roleId,
                        'model_type' => 'App\\Models\\User',
                        'model_id' => $centralUserId,
                    ]
                );
            }

            // Add current site to site_user if not exists
            $siteCode = session('current_site_code');
            if ($siteCode) {
                $siteId = DB::connection('central')->table('sites')
                    ->where('code', $siteCode)->value('id');

                if ($siteId) {
                    DB::connection('central')->table('site_user')->updateOrInsert(
                        ['user_id' => $centralUserId, 'site_id' => $siteId],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        } catch (\Exception $e) {
            // Central DB not available, skip silently
        }
    }

    /**
     * Get or create user in central database, returns central user ID
     */
    protected function getOrCreateCentralUser(User $user): int
    {
        $centralUserId = DB::connection('central')->table('users')
            ->where('email', $user->email)->value('id');

        if ($centralUserId) {
            // Update existing central user
            DB::connection('central')->table('users')
                ->where('id', $centralUserId)
                ->update([
                    'name' => $user->name,
                    'employee_id' => $user->employee_id,
                    'phone' => $user->phone,
                    'password' => $user->password,
                    'is_active' => $user->is_active,
                    'updated_at' => now(),
                ]);
        } else {
            $centralUserId = DB::connection('central')->table('users')->insertGetId([
                'employee_id' => $user->employee_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'email_verified_at' => $user->email_verified_at,
                'password' => $user->password,
                'is_active' => $user->is_active,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $centralUserId;
    }
}
