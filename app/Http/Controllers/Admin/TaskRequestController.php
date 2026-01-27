<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaskRequest;
use App\Models\User;
use App\Models\MaintenanceJob;
use Illuminate\Http\Request;

class TaskRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = TaskRequest::with(['requester', 'machine', 'reviewer', 'assignedUser', 'operators']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('task_type', 'like', "%{$search}%")
                    ->orWhereHas('machine', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('requester', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Requester filter
        if ($request->filled('requester_id')) {
            $query->where('requested_by', $request->requester_id);
        }

        $taskRequests = $query->latest()->paginate(15)->appends($request->except('page'));
        $requesters = User::role('pic')->orderBy('name')->get();

        return view('admin.task-requests.index', compact('taskRequests', 'requesters'));
    }

    public function show(TaskRequest $taskRequest)
    {
        $taskRequest->load(['requester', 'machine', 'reviewer', 'assignedUser', 'maintenanceJob', 'operators', 'completedBy']);
        $technicians = User::role('staff_maintenance')->orderBy('name')->get();

        return view('admin.task-requests.show', compact('taskRequest', 'technicians'));
    }

    public function approve(Request $request, TaskRequest $taskRequest)
    {
        $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        // Check if already reviewed
        if ($taskRequest->reviewed_at) {
            return back()->with('error', 'This task request has already been reviewed.');
        }

        $taskRequest->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $request->review_notes,
        ]);

        return redirect()->route('admin.task-requests.show', $taskRequest)
            ->with('success', 'Task request approved successfully.');
    }

    public function reject(Request $request, TaskRequest $taskRequest)
    {
        $request->validate([
            'review_notes' => 'required|string|max:1000',
        ]);

        // Check if already reviewed
        if ($taskRequest->reviewed_at) {
            return back()->with('error', 'This task request has already been reviewed.');
        }

        $taskRequest->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $request->review_notes,
        ]);

        return redirect()->route('admin.task-requests.show', $taskRequest)
            ->with('success', 'Task request rejected.');
    }

    public function assign(Request $request, TaskRequest $taskRequest)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        // Must be approved first
        if (!$taskRequest->isApproved()) {
            return back()->with('error', 'Task request must be approved before assigning.');
        }

        $taskRequest->update([
            'assigned_to' => $request->assigned_to,
            'status' => 'assigned',
        ]);

        return redirect()->route('admin.task-requests.show', $taskRequest)
            ->with('success', 'Task request assigned to technician successfully.');
    }

    public function convertToJob(TaskRequest $taskRequest)
    {
        // Must be approved
        if (!$taskRequest->isApproved()) {
            return back()->with('error', 'Task request must be approved before converting to maintenance job.');
        }

        // Check if already converted
        if ($taskRequest->job_id) {
            return back()->with('error', 'This task request has already been converted to a maintenance job.');
        }

        // Create maintenance job
        $job = MaintenanceJob::create([
            'machine_id' => $taskRequest->machine_id,
            'assigned_to' => $taskRequest->assigned_to,
            'job_type' => strtolower($taskRequest->task_type),
            'priority' => $taskRequest->priority,
            'scheduled_date' => $taskRequest->requested_date ?? now()->addDays(7),
            'description' => $taskRequest->title . "\n\n" . $taskRequest->description,
            'status' => 'pending',
        ]);

        // Link task request to job
        $taskRequest->update([
            'job_id' => $job->id,
            'status' => 'completed',
        ]);

        return redirect()->route('admin.task-requests.show', $taskRequest)
            ->with('success', 'Task request converted to maintenance job successfully.');
    }

    public function destroy(TaskRequest $taskRequest)
    {
        // Only allow deletion of pending requests
        if (!$taskRequest->isPending()) {
            return back()->with('error', 'Cannot delete task requests that have been reviewed.');
        }

        $taskRequest->delete();

        return redirect()->route('admin.task-requests.index')
            ->with('success', 'Task request deleted successfully.');
    }
}
