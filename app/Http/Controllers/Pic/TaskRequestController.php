<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\TaskRequest;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TaskRequest::byRequester(auth()->id())
            ->with(['machine', 'reviewer']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('machine', function ($mq) use ($search) {
                        $mq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $taskRequests = $query->latest()->paginate(15);

        return view('pic.task-requests.index', compact('taskRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $machines = Machine::orderBy('name')->get();
        $operators = User::role('staff_maintenance')->orderBy('name')->get();

        // Generate request code
        $requestCode = 'TASK-' . date('Ymd') . '-' . str_pad(TaskRequest::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

        return view('pic.task-requests.create', compact('machines', 'requestCode', 'operators'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_code' => 'required|unique:task_requests,request_code',
            'machine_id' => 'nullable|exists:machines,id',
            'task_type' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high,urgent',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requested_date' => 'nullable|date|after_or_equal:today',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'assign_to_all' => 'nullable|boolean',
            'operator_ids' => 'nullable|array',
            'operator_ids.*' => 'exists:users,id',
        ]);

        $validated['requested_by'] = auth()->id();
        $validated['status'] = 'pending';

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('task-attachments', 'public');
                $attachments[] = $path;
            }
            $validated['attachments'] = $attachments;
        }

        // Remove operator assignment fields from validated data
        $assignToAll = $validated['assign_to_all'] ?? false;
        $operatorIds = $validated['operator_ids'] ?? [];
        unset($validated['assign_to_all'], $validated['operator_ids']);

        $taskRequest = TaskRequest::create($validated);

        // Assign operators if specified
        if ($assignToAll) {
            // Assign to all operators
            $allOperators = User::role('staff_maintenance')->pluck('id');
            $taskRequest->operators()->sync($allOperators);
        } elseif (!empty($operatorIds)) {
            // Assign to specific operators
            $taskRequest->operators()->sync($operatorIds);
        }

        return redirect()
            ->route('pic.task-requests.index')
            ->with('success', 'Task request submitted successfully! Waiting for admin approval.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskRequest $taskRequest)
    {
        // Ensure PIC can only view their own requests
        if ($taskRequest->requested_by !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $taskRequest->load(['machine', 'requester', 'reviewer', 'assignedUser', 'maintenanceJob', 'operators', 'completedBy']);

        return view('pic.task-requests.show', compact('taskRequest'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskRequest $taskRequest)
    {
        // Ensure PIC can only delete their own pending requests
        if ($taskRequest->requested_by !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$taskRequest->isPending()) {
            return redirect()
                ->route('pic.task-requests.index')
                ->with('error', 'You can only delete pending requests.');
        }

        // Delete attachments
        if ($taskRequest->attachments) {
            foreach ($taskRequest->attachments as $attachment) {
                Storage::disk('public')->delete($attachment);
            }
        }

        $taskRequest->delete();

        return redirect()
            ->route('pic.task-requests.index')
            ->with('success', 'Task request deleted successfully!');
    }
}
