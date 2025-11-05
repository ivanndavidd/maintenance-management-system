<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\WorkReport;
use App\Models\MaintenanceJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MyReportController extends Controller
{
    /**
     * Display a listing of user's work reports
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = WorkReport::where('user_id', $user->id)->with(['job.machine']);

        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Date filter
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('report_code', 'like', "%{$search}%")
                    ->orWhere('work_performed', 'like', "%{$search}%")
                    ->orWhereHas('job.machine', function ($mq) use ($search) {
                        $mq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $reports = $query->latest()->paginate(15);

        // Statistics
        $stats = [
            'total' => WorkReport::where('user_id', $user->id)->count(),
            'submitted' => WorkReport::where('user_id', $user->id)
                ->where('status', 'submitted')
                ->count(),
            'approved' => WorkReport::where('user_id', $user->id)
                ->where('status', 'approved')
                ->count(),
            'draft' => WorkReport::where('user_id', $user->id)->where('status', 'draft')->count(),
        ];

        return view('user.reports.index', compact('reports', 'stats'));
    }

    /**
     * Show the form for creating a new report
     */
    public function create(Request $request)
    {
        $jobId = $request->get('job_id');

        // Get user's tasks that can have reports
        $jobs = MaintenanceJob::where('assigned_to', auth()->id())
            ->whereIn('status', ['in_progress', 'completed'])
            ->with('machine')
            ->latest()
            ->get();

        $selectedJob = null;
        if ($jobId) {
            $selectedJob = MaintenanceJob::find($jobId);

            // Check if user is assigned to this job
            if ($selectedJob && $selectedJob->assigned_to !== auth()->id()) {
                abort(403, 'Unauthorized access to this job');
            }
        }

        return view('user.reports.create', compact('jobs', 'selectedJob'));
    }

    /**
     * Store a newly created report
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'required|exists:maintenance_jobs,id',
            'work_start' => 'required|date',
            'work_end' => 'required|date|after:work_start',
            'work_performed' => 'required|string',
            'issues_found' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'machine_condition' => 'required|in:good,fair,poor',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Check if user is assigned to this job
        $job = MaintenanceJob::findOrFail($validated['job_id']);
        if ($job->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Calculate downtime
        $workStart = \Carbon\Carbon::parse($validated['work_start']);
        $workEnd = \Carbon\Carbon::parse($validated['work_end']);
        $downtimeMinutes = $workStart->diffInMinutes($workEnd);

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('work-reports', 'public');
                $attachments[] = $path;
            }
        }

        // Generate report code
        $reportCode =
            'WR-' . date('Ymd') . '-' . str_pad(WorkReport::count() + 1, 4, '0', STR_PAD_LEFT);

        // Create report - ✅ FIXED: Using 'submitted' instead of 'pending'
        $report = WorkReport::create([
            'report_code' => $reportCode,
            'job_id' => $validated['job_id'],
            'user_id' => auth()->id(),
            'work_start' => $validated['work_start'],
            'work_end' => $validated['work_end'],
            'downtime_minutes' => $downtimeMinutes,
            'work_performed' => $validated['work_performed'],
            'issues_found' => $validated['issues_found'] ?? null,
            'recommendations' => $validated['recommendations'] ?? null,
            'machine_condition' => $validated['machine_condition'],
            'status' => 'submitted', // ✅ CHANGED FROM 'pending'
            'attachments' => $attachments,
        ]);

        return redirect()
            ->route('user.reports.show', $report)
            ->with('success', 'Work report submitted successfully! Waiting for validation.');
    }

    /**
     * Display the specified report
     */
    public function show(WorkReport $report)
    {
        // Check if user owns this report
        if ($report->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this report');
        }

        $report->load(['job.machine', 'user', 'validator']);

        return view('user.reports.show', compact('report'));
    }

    /**
     * Show the form for editing the report
     */
    public function edit(WorkReport $report)
    {
        // Check if user owns this report
        if ($report->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Only allow editing draft or submitted reports
        if (!in_array($report->status, ['draft', 'submitted', 'revision_needed'])) {
            return redirect()
                ->route('user.reports.show', $report)
                ->with('error', 'Cannot edit approved reports');
        }

        $jobs = MaintenanceJob::where('assigned_to', auth()->id())
            ->whereIn('status', ['in_progress', 'completed'])
            ->with('machine')
            ->latest()
            ->get();

        return view('user.reports.edit', compact('report', 'jobs'));
    }

    /**
     * Update the specified report
     */
    public function update(Request $request, WorkReport $report)
    {
        // Check if user owns this report
        if ($report->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Only allow editing draft or submitted reports
        if (!in_array($report->status, ['draft', 'submitted', 'revision_needed'])) {
            return redirect()
                ->route('user.reports.show', $report)
                ->with('error', 'Cannot edit approved reports');
        }

        $validated = $request->validate([
            'work_start' => 'required|date',
            'work_end' => 'required|date|after:work_start',
            'work_performed' => 'required|string',
            'issues_found' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'machine_condition' => 'required|in:good,fair,poor',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Calculate downtime
        $workStart = \Carbon\Carbon::parse($validated['work_start']);
        $workEnd = \Carbon\Carbon::parse($validated['work_end']);
        $downtimeMinutes = $workStart->diffInMinutes($workEnd);

        // Handle file uploads
        $attachments = $report->attachments ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('work-reports', 'public');
                $attachments[] = $path;
            }
        }

        // Update report
        $report->update([
            'work_start' => $validated['work_start'],
            'work_end' => $validated['work_end'],
            'downtime_minutes' => $downtimeMinutes,
            'work_performed' => $validated['work_performed'],
            'issues_found' => $validated['issues_found'] ?? null,
            'recommendations' => $validated['recommendations'] ?? null,
            'machine_condition' => $validated['machine_condition'],
            'attachments' => $attachments,
        ]);

        return redirect()
            ->route('user.reports.show', $report)
            ->with('success', 'Work report updated successfully!');
    }

    /**
     * Remove the specified report
     */
    public function destroy(WorkReport $report)
    {
        // Check if user owns this report
        if ($report->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Only allow deleting draft or submitted reports
        if (!in_array($report->status, ['draft', 'submitted', 'revision_needed'])) {
            return redirect()
                ->route('user.reports.index')
                ->with('error', 'Cannot delete approved reports');
        }

        // Delete attachments
        if ($report->attachments) {
            foreach ($report->attachments as $attachment) {
                Storage::disk('public')->delete($attachment);
            }
        }

        $report->delete();

        return redirect()
            ->route('user.reports.index')
            ->with('success', 'Work report deleted successfully!');
    }
}
