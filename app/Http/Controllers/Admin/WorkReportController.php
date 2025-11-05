<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkReport;
use App\Models\MaintenanceJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class WorkReportController extends Controller
{
    /**
     * Display a listing of work reports (Admin view)
     */
    public function index(Request $request)
    {
        $query = WorkReport::with(['job.machine', 'user', 'validator']);

        // Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('report_code', 'like', "%{$search}%")
                    ->orWhere('work_performed', 'like', "%{$search}%")
                    ->orWhere('issues_found', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // User filter
        if ($request->has('user') && $request->user != '') {
            $query->where('user_id', $request->user);
        }

        // Job filter
        if ($request->has('job') && $request->job != '') {
            $query->where('job_id', $request->job);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('work_start', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('work_start', '<=', $request->date_to);
        }

        $reports = $query->latest()->paginate(15);

        // Get filter options
        $users = User::role(['user', 'admin'])
            ->orderBy('name')
            ->get();
        $jobs = MaintenanceJob::with('machine')->orderBy('created_at', 'desc')->limit(50)->get();
        $statuses = ['pending', 'approved', 'rejected', 'draft'];

        return view('admin.work-reports.index', compact('reports', 'users', 'jobs', 'statuses'));
    }

    /**
     * Display user's own work reports
     */
    public function myReports(Request $request)
    {
        $query = WorkReport::with(['job.machine'])->where('user_id', auth()->id());

        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $reports = $query->latest()->paginate(15);
        $statuses = ['pending', 'approved', 'rejected', 'draft'];

        return view('admin.work-reports.my-reports', compact('reports', 'statuses'));
    }

    /**
     * Show the form for creating a new work report
     */
    public function create(Request $request)
    {
        // Get user's assigned jobs that are completed or in progress
        $jobs = MaintenanceJob::with('machine')
            ->where('assigned_to', auth()->id())
            ->whereIn('status', ['in_progress', 'completed'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Pre-select job if passed via query string
        $selectedJobId = $request->query('job_id');

        // Generate report code
        $reportCode = 'RPT-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        return view('admin.work-reports.create', compact('jobs', 'selectedJobId', 'reportCode'));
    }

    /**
     * Store a newly created work report
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_code' => ['required', 'string', 'max:50', 'unique:work_reports'],
            'job_id' => ['required', 'exists:maintenance_jobs,id'],
            'work_start' => ['required', 'date'],
            'work_end' => ['required', 'date', 'after:work_start'],
            'downtime_minutes' => ['nullable', 'integer', 'min:0'],
            'work_performed' => ['required', 'string'],
            'issues_found' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'machine_condition' => ['required', 'in:excellent,good,fair,poor,critical'],
            'status' => ['required', 'in:pending,draft'],
            'attachments.*' => ['nullable', 'image', 'max:5120'], // 5MB max per image
        ]);

        // Add user_id
        $validated['user_id'] = auth()->id();

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('work-reports', 'public');
                $attachments[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
            $validated['attachments'] = $attachments;
        }

        $report = WorkReport::create($validated);

        return redirect()
            ->route('admin.work-reports.my-reports')
            ->with('success', 'Work report submitted successfully!');
    }

    /**
     * Display the specified work report
     */
    public function show(WorkReport $workReport)
    {
        $workReport->load(['job.machine', 'user', 'validator']);

        return view('admin.work-reports.show', compact('workReport'));
    }

    /**
     * Show the form for editing the specified work report
     */
    public function edit(WorkReport $workReport)
    {
        // Only allow editing own reports and only if status is draft or pending
        if (
            $workReport->user_id !== auth()->id() ||
            !in_array($workReport->status, ['draft', 'pending'])
        ) {
            abort(403, 'Unauthorized action.');
        }

        $jobs = MaintenanceJob::with('machine')
            ->where('assigned_to', auth()->id())
            ->whereIn('status', ['in_progress', 'completed'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.work-reports.edit', compact('workReport', 'jobs'));
    }

    /**
     * Update the specified work report
     */
    public function update(Request $request, WorkReport $workReport)
    {
        // Only allow editing own reports
        if ($workReport->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'report_code' => [
                'required',
                'string',
                'max:50',
                'unique:work_reports,report_code,' . $workReport->id,
            ],
            'job_id' => ['required', 'exists:maintenance_jobs,id'],
            'work_start' => ['required', 'date'],
            'work_end' => ['required', 'date', 'after:work_start'],
            'downtime_minutes' => ['nullable', 'integer', 'min:0'],
            'work_performed' => ['required', 'string'],
            'issues_found' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'machine_condition' => ['required', 'in:excellent,good,fair,poor,critical'],
            'status' => ['required', 'in:pending,draft'],
            'attachments.*' => ['nullable', 'image', 'max:5120'],
        ]);

        // Handle new file uploads
        if ($request->hasFile('attachments')) {
            $existingAttachments = $workReport->attachments ?? [];

            foreach ($request->file('attachments') as $file) {
                $path = $file->store('work-reports', 'public');
                $existingAttachments[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
            $validated['attachments'] = $existingAttachments;
        }

        $workReport->update($validated);

        return redirect()
            ->route('admin.work-reports.my-reports')
            ->with('success', 'Work report updated successfully!');
    }

    /**
     * Remove the specified work report
     */
    public function destroy(WorkReport $workReport)
    {
        // Only allow deleting own reports if draft
        if ($workReport->user_id !== auth()->id() || $workReport->status !== 'draft') {
            abort(403, 'Unauthorized action.');
        }

        // Delete attachments
        if ($workReport->attachments) {
            foreach ($workReport->attachments as $attachment) {
                Storage::disk('public')->delete($attachment['path']);
            }
        }

        $workReport->delete();

        return redirect()
            ->route('admin.work-reports.my-reports')
            ->with('success', 'Work report deleted successfully!');
    }

    /**
     * Validate (approve/reject) work report
     */
    public function validateReport(Request $request, WorkReport $workReport)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'admin_comments' => ['nullable', 'string'],
        ]);

        $validated['validated_by'] = auth()->id();
        $validated['validated_at'] = now();

        $workReport->update($validated);

        $message =
            $validated['status'] === 'approved'
                ? 'Work report approved successfully!'
                : 'Work report rejected.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Delete attachment from report
     */
    public function deleteAttachment(WorkReport $workReport, $index)
    {
        // Only allow deleting own report attachments
        if ($workReport->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $attachments = $workReport->attachments ?? [];

        if (isset($attachments[$index])) {
            // Delete file from storage
            Storage::disk('public')->delete($attachments[$index]['path']);

            // Remove from array
            unset($attachments[$index]);

            // Re-index array
            $attachments = array_values($attachments);

            $workReport->update(['attachments' => $attachments]);
        }

        return redirect()->back()->with('success', 'Attachment deleted successfully!');
    }
    /**
     * Approve/Validate work report
     */
    public function approve(Request $request, WorkReport $workReport)
    {
        $validated = $request->validate([
            'admin_comments' => 'nullable|string|max:1000',
        ]);

        $workReport->update([
            'status' => 'approved',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
            'admin_comments' => $validated['admin_comments'] ?? null,
        ]);

        // Update job status to completed if not already
        if ($workReport->job && $workReport->job->status !== 'completed') {
            $workReport->job->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        return redirect()
            ->route('admin.work-reports.show', $workReport)
            ->with('success', 'Work report approved successfully!');
    }

    /**
     * Reject work report / Request revision
     */
    public function reject(Request $request, WorkReport $workReport)
    {
        $validated = $request->validate([
            'admin_comments' => 'required|string|max:1000',
        ]);

        $workReport->update([
            'status' => 'revision_needed',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
            'admin_comments' => $validated['admin_comments'],
        ]);

        return redirect()
            ->route('admin.work-reports.show', $workReport)
            ->with('success', 'Work report sent back for revision.');
    }
}
