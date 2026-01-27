<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IncidentReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = IncidentReport::byReporter(auth()->id())
            ->with(['machine', 'assignedUser']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('report_code', 'like', "%{$search}%")
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

        // Filter by severity
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $reports = $query->latest()->paginate(15);

        return view('pic.incident-reports.index', compact('reports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $machines = Machine::orderBy('name')->get();
        $operators = User::role('staff_maintenance')->orderBy('name')->get();

        // Generate report code
        $reportCode = 'INC-' . date('Ymd') . '-' . str_pad(IncidentReport::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

        return view('pic.incident-reports.create', compact('machines', 'reportCode', 'operators'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_code' => 'required|unique:incident_reports,report_code',
            'machine_id' => 'required|exists:machines,id',
            'incident_type' => 'required|string|max:255',
            'severity' => 'required|in:low,medium,high,critical',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,mp4,mov|max:10240',
            'operator_ids' => 'nullable|array',
            'operator_ids.*' => 'exists:users,id',
            'assign_to_all' => 'nullable|boolean',
        ]);

        $validated['reported_by'] = auth()->id();
        $validated['status'] = $request->has('operator_ids') || $request->assign_to_all ? 'assigned' : 'pending';
        $validated['assigned_at'] = $request->has('operator_ids') || $request->assign_to_all ? now() : null;

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('incident-attachments', 'public');
                $attachments[] = $path;
            }
            $validated['attachments'] = $attachments;
        }

        $incidentReport = IncidentReport::create($validated);

        // Attach operators
        if ($request->assign_to_all) {
            // Assign to all operators
            $allOperators = User::role('staff_maintenance')->pluck('id')->toArray();
            $incidentReport->operators()->attach($allOperators);
        } elseif ($request->filled('operator_ids')) {
            // Assign to specific operators
            $incidentReport->operators()->attach($request->operator_ids);
        }

        return redirect()
            ->route('pic.incident-reports.index')
            ->with('success', 'Incident report submitted successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(IncidentReport $incidentReport)
    {
        // Ensure PIC can only view their own reports
        if ($incidentReport->reported_by !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $incidentReport->load(['machine', 'reporter', 'assignedUser', 'resolver', 'operators', 'completedBy']);

        return view('pic.incident-reports.show', compact('incidentReport'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IncidentReport $incidentReport)
    {
        // Ensure PIC can only edit their own pending reports
        if ($incidentReport->reported_by !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$incidentReport->isPending() && !$incidentReport->isAssigned()) {
            return redirect()
                ->route('pic.incident-reports.show', $incidentReport)
                ->with('error', 'You can only edit pending or assigned reports.');
        }

        $machines = Machine::orderBy('name')->get();
        $operators = User::role('staff_maintenance')->orderBy('name')->get();
        $incidentReport->load('operators');

        return view('pic.incident-reports.edit', compact('incidentReport', 'machines', 'operators'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IncidentReport $incidentReport)
    {
        // Ensure PIC can only update their own pending reports
        if ($incidentReport->reported_by !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$incidentReport->isPending() && !$incidentReport->isAssigned()) {
            return redirect()
                ->route('pic.incident-reports.show', $incidentReport)
                ->with('error', 'You can only edit pending or assigned reports.');
        }

        $validated = $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'incident_type' => 'required|string|max:255',
            'severity' => 'required|in:low,medium,high,critical',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,mp4,mov|max:10240',
            'operator_ids' => 'nullable|array',
            'operator_ids.*' => 'exists:users,id',
            'assign_to_all' => 'nullable|boolean',
        ]);

        // Update status if operators are assigned
        if ($request->has('operator_ids') || $request->assign_to_all) {
            $validated['status'] = 'assigned';
            $validated['assigned_at'] = $incidentReport->assigned_at ?? now();
        }

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $attachments = $incidentReport->attachments ?? [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('incident-attachments', 'public');
                $attachments[] = $path;
            }
            $validated['attachments'] = $attachments;
        }

        $incidentReport->update($validated);

        // Sync operators
        if ($request->assign_to_all) {
            $allOperators = User::role('staff_maintenance')->pluck('id')->toArray();
            $incidentReport->operators()->sync($allOperators);
        } elseif ($request->has('operator_ids')) {
            $incidentReport->operators()->sync($request->operator_ids);
        }

        return redirect()
            ->route('pic.incident-reports.show', $incidentReport)
            ->with('success', 'Incident report updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IncidentReport $incidentReport)
    {
        // Ensure PIC can only delete their own pending reports
        if ($incidentReport->reported_by !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$incidentReport->isPending()) {
            return redirect()
                ->route('pic.incident-reports.index')
                ->with('error', 'You can only delete pending reports.');
        }

        // Delete attachments
        if ($incidentReport->attachments) {
            foreach ($incidentReport->attachments as $attachment) {
                Storage::disk('public')->delete($attachment);
            }
        }

        $incidentReport->delete();

        return redirect()
            ->route('pic.incident-reports.index')
            ->with('success', 'Incident report deleted successfully!');
    }

    /**
     * Delete specific attachment
     */
    public function deleteAttachment(IncidentReport $incidentReport, $index)
    {
        if ($incidentReport->reported_by !== auth()->id() || !$incidentReport->isPending()) {
            abort(403);
        }

        $attachments = $incidentReport->attachments ?? [];

        if (isset($attachments[$index])) {
            Storage::disk('public')->delete($attachments[$index]);
            unset($attachments[$index]);
            $incidentReport->update(['attachments' => array_values($attachments)]);
        }

        return back()->with('success', 'Attachment deleted successfully!');
    }
}
