<?php

namespace App\Http\Controllers\Admin;

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

        return view('admin.users.index', compact('users', 'roles', 'departments'));
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

        return view('admin.users.edit', compact('user', 'roles', 'departments', 'userRole'));
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
}
