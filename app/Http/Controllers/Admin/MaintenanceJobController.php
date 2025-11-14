<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceJob;
use App\Models\Machine;
use App\Models\User;
use App\Models\JobCompletionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

        // Overdue filter
        if ($request->has('overdue') && $request->overdue == '1') {
            $query->where('status', '!=', 'completed')
                  ->whereNotNull('scheduled_date')
                  ->whereDate('scheduled_date', '<', now());
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortColumns = ['job_code', 'title', 'type', 'priority', 'status', 'scheduled_date', 'created_at'];

        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder);

        $jobs = $query->paginate(15);

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
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_type' => ['nullable', 'required_if:is_recurring,1', 'in:daily,weekly,monthly,yearly'],
            'recurrence_interval' => ['nullable', 'required_if:is_recurring,1', 'integer', 'min:1'],
            'recurrence_end_date' => ['nullable', 'date', 'after:scheduled_date'],
        ]);

        // Add created_by
        $validated['created_by'] = auth()->id();

        // Handle recurring flag
        $validated['is_recurring'] = $request->has('is_recurring') && $request->is_recurring == '1';

        // Clear recurring fields if not recurring
        if (!$validated['is_recurring']) {
            $validated['recurrence_type'] = null;
            $validated['recurrence_interval'] = null;
            $validated['recurrence_end_date'] = null;
        }

        $job = MaintenanceJob::create($validated);

        return redirect()
            ->route('admin.jobs.index')
            ->with('success', 'Maintenance job created successfully!' . ($validated['is_recurring'] ? ' Recurring jobs will be created automatically.' : ''));
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

            // Log job completion for KPI tracking
            $this->logJobCompletion($job, $validated['completed_at']);
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

            // Log job completion for KPI tracking
            $this->logJobCompletion($job, $updates['completed_at']);
        }

        $job->update($updates);

        return redirect()->back()->with('success', 'Job status updated successfully!');
    }

    /**
     * Log job completion for KPI tracking
     */
    private function logJobCompletion(MaintenanceJob $job, $completedAt)
    {
        if (!$job->scheduled_date || !$job->assigned_to) {
            return;
        }

        $scheduledDate = Carbon::parse($job->scheduled_date);
        $completionDate = Carbon::parse($completedAt);
        $daysLate = $scheduledDate->diffInDays($completionDate, false);

        // Determine completion status
        if ($daysLate > 0) {
            $status = 'late';
        } elseif ($daysLate < 0) {
            $status = 'early';
            $daysLate = abs($daysLate) * -1; // Keep negative for early
        } else {
            $status = 'on_time';
        }

        JobCompletionLog::create([
            'job_id' => $job->id,
            'user_id' => $job->assigned_to,
            'scheduled_date' => $job->scheduled_date,
            'completed_at' => $completedAt,
            'days_late' => $daysLate,
            'completion_status' => $status,
            'job_code' => $job->job_code,
            'job_title' => $job->title,
            'job_type' => $job->type,
            'priority' => $job->priority,
        ]);
    }
}
