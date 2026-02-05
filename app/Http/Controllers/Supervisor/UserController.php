<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Allowed roles that supervisor can manage
     */
    protected array $allowedRoles = ['supervisor_maintenance', 'staff_maintenance'];

    /**
     * Display a listing of users (only supervisor_maintenance and staff_maintenance)
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'department'])
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', $this->allowedRoles);
            })
            ->orWhereDoesntHave('roles'); // Include users without roles (new registrations)

        // Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Role filter (only allowed roles)
        if ($request->has('role') && $request->role != '' && in_array($request->role, $this->allowedRoles)) {
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

        // Get filter options - only allowed roles
        $roles = Role::whereIn('name', $this->allowedRoles)->get();
        $departments = Department::all();

        return view('admin.users.index', compact('users', 'roles', 'departments'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::whereIn('name', $this->allowedRoles)->get();
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
            'employee_id' => ['required', 'string', 'max:50', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role' => ['required', 'exists:roles,name', 'in:' . implode(',', $this->allowedRoles)],
            'is_active' => ['boolean'],
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_id' => $validated['employee_id'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'department_id' => $validated['department_id'] ?? null,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Assign role
        $user->assignRole($validated['role']);

        return redirect()
            ->route('supervisor.users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        // Verify user has allowed role or no role
        if (!$this->canManageUser($user)) {
            abort(403, 'You are not authorized to view this user.');
        }

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
        // Verify user has allowed role or no role
        if (!$this->canManageUser($user)) {
            abort(403, 'You are not authorized to edit this user.');
        }

        $roles = Role::whereIn('name', $this->allowedRoles)->get();
        $departments = Department::all();
        $userRole = $user->roles->first();

        return view('admin.users.edit', compact('user', 'roles', 'departments', 'userRole'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        // Verify user has allowed role or no role
        if (!$this->canManageUser($user)) {
            abort(403, 'You are not authorized to update this user.');
        }

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
                'required',
                'string',
                'max:50',
                'unique:users,employee_id,' . $user->id,
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role' => ['required', 'exists:roles,name', 'in:' . implode(',', $this->allowedRoles)],
            'is_active' => ['boolean'],
        ]);

        // Update user data
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_id' => $validated['employee_id'],
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

        return redirect()
            ->route('supervisor.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Verify user has allowed role or no role
        if (!$this->canManageUser($user)) {
            abort(403, 'You are not authorized to delete this user.');
        }

        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('supervisor.users.index')
                ->with('error', 'You cannot delete your own account!');
        }

        $user->delete();

        return redirect()
            ->route('supervisor.users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        // Verify user has allowed role or no role
        if (!$this->canManageUser($user)) {
            abort(403, 'You are not authorized to change this user status.');
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('supervisor.users.index')
            ->with('success', "User {$status} successfully!");
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        // Verify user has allowed role or no role
        if (!$this->canManageUser($user)) {
            abort(403, 'You are not authorized to reset this user password.');
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('supervisor.users.show', $user)
            ->with('success', 'Password reset successfully!');
    }

    /**
     * Check if current supervisor can manage this user
     */
    private function canManageUser(User $user): bool
    {
        // Can manage users with allowed roles
        if ($user->hasAnyRole($this->allowedRoles)) {
            return true;
        }

        // Can manage users without any role (new registrations)
        if ($user->roles->isEmpty()) {
            return true;
        }

        return false;
    }
}
