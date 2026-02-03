<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display user profile
     */
    public function index()
    {
        $user = auth()->user();

        // Get user statistics
        // PM Tasks
        $pmTasksTotal = \App\Models\PmTask::where('assigned_user_id', $user->id)->count();
        $pmTasksCompleted = \App\Models\PmTask::where('assigned_user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        // CM Tasks (assigned as technician)
        $cmTasksTotal = \App\Models\CorrectiveMaintenanceRequest::whereHas('technicians', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();
        $cmTasksCompleted = \App\Models\CorrectiveMaintenanceRequest::whereHas('technicians', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('status', 'done')->count();

        $stats = [
            'total_tasks' => $pmTasksTotal + $cmTasksTotal,
            'completed_tasks' => $pmTasksCompleted + $cmTasksCompleted,
            'pm_tasks' => $pmTasksTotal,
            'cm_tasks' => $cmTasksTotal,
        ];

        // Calculate completion rate
        $stats['completion_rate'] =
            $stats['total_tasks'] > 0
                ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100, 1)
                : 0;

        // Member since
        $stats['member_since'] = $user->created_at->diffForHumans();

        return view('profile.index', compact('user', 'stats'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user->update($validated);

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully!');
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = auth()->user();

        // Check current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect',
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('profile.index')
            ->with('success', 'Password changed successfully!');
    }
}
