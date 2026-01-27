<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TaskRequest;
use Illuminate\Http\Request;

class AssignedTaskRequestController extends Controller
{
    /**
     * Display a listing of assigned task requests
     */
    public function index(Request $request)
    {
        $query = TaskRequest::whereHas('operators', function ($q) {
            $q->where('user_id', auth()->id());
        })->with(['machine', 'requester', 'operators', 'completedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $taskRequests = $query->latest()->paginate(15);

        return view('user.assigned-task-requests.index', compact('taskRequests'));
    }

    /**
     * Display the specified task request
     */
    public function show(TaskRequest $taskRequest)
    {
        // Verify user is assigned to this task request
        if (!$taskRequest->operators->contains(auth()->id())) {
            abort(403, 'You are not assigned to this task request.');
        }

        $taskRequest->load(['machine', 'requester', 'operators', 'completedBy']);

        return view('user.assigned-task-requests.show', compact('taskRequest'));
    }

    /**
     * Mark task request as in progress
     */
    public function startWork(TaskRequest $taskRequest)
    {
        // Verify user is assigned
        if (!$taskRequest->operators->contains(auth()->id())) {
            abort(403, 'You are not assigned to this task request.');
        }

        // Check if not already completed
        if ($taskRequest->completed_by) {
            return back()->with('error', 'This task request has already been completed.');
        }

        $taskRequest->update([
            'status' => 'assigned'
        ]);

        return back()->with('success', 'Started working on this task request.');
    }

    /**
     * Complete the task request
     */
    public function complete(Request $request, TaskRequest $taskRequest)
    {
        // Verify user is assigned
        if (!$taskRequest->operators->contains(auth()->id())) {
            abort(403, 'You are not assigned to this task request.');
        }

        // Check if not already completed
        if ($taskRequest->completed_by) {
            return back()->with('error', 'This task request has already been completed.');
        }

        $validated = $request->validate([
            'completion_notes' => 'required|string|max:1000',
        ]);

        $taskRequest->update([
            'status' => 'completed',
            'completed_by' => auth()->id(),
            'completed_at' => now(),
            'review_notes' => $validated['completion_notes'],
        ]);

        return redirect()
            ->route('user.assigned-task-requests.index')
            ->with('success', 'Task request marked as completed successfully!');
    }
}
