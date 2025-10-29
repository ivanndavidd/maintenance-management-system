<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceJob;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MaintenanceJobController extends Controller
{
    /**
     * Display a listing of jobs
     */
    public function index(Request $request)
    {
        $query = MaintenanceJob::with(['machine', 'assignedUser', 'creator']);

        // Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('job_code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Priority filter
        if ($request->has('priority') && $request->priority != '') {
            $query->where('priority', $request->priority);
        }

        // Type filter
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        // Machine filter
        if ($request->has('machine') && $request->machine != '') {
            $query->where('machine_id', $request->machine);
        }

        // Assigned user filter
        if ($request->has('assigned_to') && $request->assigned_to != '') {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }

        $jobs = $query->latest()->paginate(15);

        // Get filter options
        $machines = Machine::orderBy('name')->get();
        $users = User::role(['user', 'admin'])
            ->orderBy('name')
            ->get();
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        $priorities = ['low', 'medium', 'high', 'critical'];
        $types = ['preventive', 'breakdown', 'corrective', 'inspection'];

        return view(
            'admin.jobs.index',
            compact('jobs', 'machines', 'users', 'statuses', 'priorities', 'types'),
        );
    }

    /**
     * Show the form for creating a new job
     */
    public function create()
    {
        $machines = Machine::where('status', '!=', 'retired')->orderBy('name')->get();
        $users = User::role(['user', 'admin'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Generate job code
        $jobCode = 'JOB-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        return view('admin.jobs.create', compact('machines', 'users', 'jobCode'));
    }

    /**
     * Store a newly created job
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_code' => ['required', 'string', 'max:50', 'unique:maintenance_jobs'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'machine_id' => ['required', 'exists:machines,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'type' => ['required', 'in:preventive,breakdown,corrective,inspection'],
            'priority' => ['required', 'in:low,medium,high,critical'],
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
            'scheduled_date' => ['nullable', 'date'],
            'estimated_duration' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        // Add created_by
        $validated['created_by'] = auth()->id();

        $job = MaintenanceJob::create($validated);

        return redirect()
            ->route('admin.jobs.index')
            ->with('success', 'Maintenance job created successfully!');
    }

    /**
     * Display the specified job
     */
    public function show(MaintenanceJob $job)
    {
        $job->load(['machine.category', 'assignedUser', 'creator']);

        // Calculate job metrics
        $metrics = [
            'total_reports' => 0,
            'completed_reports' => 0,
            'pending_reports' => 0,
            'actual_duration' => $job->actual_duration ?? 0,
            'estimated_duration' => $job->estimated_duration ?? 0,
            'duration_variance' => $job->estimated_duration
                ? ($job->actual_duration ?? 0) - $job->estimated_duration
                : 0,
        ];

        // Build timeline
        $timeline = [];

        $timeline[] = [
            'date' => $job->created_at,
            'title' => 'Job Created',
            'description' => 'Created by ' . $job->creator->name,
            'icon' => 'fa-plus-circle',
            'color' => 'primary',
        ];

        if ($job->assigned_to) {
            $timeline[] = [
                'date' => $job->created_at,
                'title' => 'Assigned',
                'description' => 'Assigned to ' . $job->assignedUser->name,
                'icon' => 'fa-user',
                'color' => 'info',
            ];
        }

        if ($job->started_at) {
            $timeline[] = [
                'date' => $job->started_at,
                'title' => 'Work Started',
                'description' => 'Job started',
                'icon' => 'fa-play',
                'color' => 'warning',
            ];
        }

        if ($job->completed_at) {
            $timeline[] = [
                'date' => $job->completed_at,
                'title' => 'Job Completed',
                'description' => 'Job completed successfully',
                'icon' => 'fa-check-circle',
                'color' => 'success',
            ];
        }

        // Sort timeline by date descending
        usort($timeline, function ($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return view('admin.jobs.show', compact('job', 'metrics', 'timeline'));
    }

    /**
     * Show the form for editing the specified job
     */
    public function edit(MaintenanceJob $job)
    {
        $machines = Machine::where('status', '!=', 'retired')->orderBy('name')->get();
        $users = User::role(['user', 'admin'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.jobs.edit', compact('job', 'machines', 'users'));
    }

    /**
     * Update the specified job
     */
    public function update(Request $request, MaintenanceJob $job)
    {
        $validated = $request->validate([
            'job_code' => [
                'required',
                'string',
                'max:50',
                'unique:maintenance_jobs,job_code,' . $job->id,
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'machine_id' => ['required', 'exists:machines,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'type' => ['required', 'in:preventive,breakdown,corrective,inspection'],
            'priority' => ['required', 'in:low,medium,high,critical'],
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
            'scheduled_date' => ['nullable', 'date'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'estimated_duration' => ['nullable', 'integer', 'min:1'],
            'actual_duration' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        // Auto-set timestamps based on status
        if ($validated['status'] === 'in_progress' && !$job->started_at) {
            $validated['started_at'] = now();
        }

        if ($validated['status'] === 'completed' && !$job->completed_at) {
            $validated['completed_at'] = now();
        }

        $job->update($validated);

        return redirect()
            ->route('admin.jobs.index')
            ->with('success', 'Maintenance job updated successfully!');
    }

    /**
     * Remove the specified job
     */
    public function destroy(MaintenanceJob $job)
    {
        $job->delete();

        return redirect()
            ->route('admin.jobs.index')
            ->with('success', 'Maintenance job deleted successfully!');
    }

    /**
     * Update job status
     */
    public function updateStatus(Request $request, MaintenanceJob $job)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
        ]);

        $updates = ['status' => $validated['status']];

        // Auto-set timestamps
        if ($validated['status'] === 'in_progress' && !$job->started_at) {
            $updates['started_at'] = now();
        }

        if ($validated['status'] === 'completed' && !$job->completed_at) {
            $updates['completed_at'] = now();
        }

        $job->update($updates);

        return redirect()->back()->with('success', 'Job status updated successfully!');
    }
}
