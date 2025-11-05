<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceJob;
use Illuminate\Http\Request;

class MyTaskController extends Controller
{
    /**
     * Display a listing of user's tasks
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = MaintenanceJob::where('assigned_to', $user->id)->with(['machine', 'assignedBy']);

        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Priority filter
        if ($request->has('priority') && $request->priority != '') {
            $query->where('priority', $request->priority);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")->orWhereHas('machine', function (
                    $mq,
                ) use ($search) {
                    $mq->where('name', 'like', "%{$search}%")->orWhere(
                        'code',
                        'like',
                        "%{$search}%",
                    );
                });
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tasks = $query->paginate(12);

        // Statistics
        $stats = [
            'total' => MaintenanceJob::where('assigned_to', $user->id)->count(),
            'pending' => MaintenanceJob::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->count(),
            'in_progress' => MaintenanceJob::where('assigned_to', $user->id)
                ->where('status', 'in_progress')
                ->count(),
            'completed' => MaintenanceJob::where('assigned_to', $user->id)
                ->where('status', 'completed')
                ->count(),
        ];

        return view('user.tasks.index', compact('tasks', 'stats'));
    }

    /**
     * Display the specified task
     */
    public function show(MaintenanceJob $job)
    {
        // Check if user is assigned to this task
        if ($job->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized access to this task');
        }

        $job->load(['machine', 'assignedBy', 'workReports.user']);

        return view('user.tasks.show', compact('job'));
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, MaintenanceJob $job)
    {
        // Check if user is assigned to this task
        if ($job->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'status' => 'required|in:in_progress,completed',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'in_progress' && !$job->started_at) {
            $updateData['started_at'] = now();
        }

        if ($validated['status'] === 'completed') {
            $updateData['completed_at'] = now();
        }

        $job->update($updateData);

        return redirect()
            ->route('user.tasks.show', $job)
            ->with('success', 'Task status updated successfully!');
    }
}
