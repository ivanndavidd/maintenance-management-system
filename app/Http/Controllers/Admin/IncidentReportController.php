<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use App\Models\User;
use Illuminate\Http\Request;

class IncidentReportController extends Controller
{
    public function index(Request $request)
    {
        $query = IncidentReport::with(['reporter', 'machine', 'assignedUser']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('report_code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('incident_type', 'like', "%{$search}%")
                    ->orWhereHas('machine', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('reporter', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Severity filter
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        // Reporter filter
        if ($request->filled('reporter_id')) {
            $query->where('reported_by', $request->reporter_id);
        }

        $incidentReports = $query->latest()->paginate(15)->appends($request->except('page'));
        $reporters = User::role('pic')->orderBy('name')->get();

        return view('admin.incident-reports.index', compact('incidentReports', 'reporters'));
    }

    public function show(IncidentReport $incidentReport)
    {
        $incidentReport->load(['reporter', 'machine', 'assignedUser', 'resolver']);
        $technicians = User::role('staff_maintenance')->orderBy('name')->get();

        return view('admin.incident-reports.show', compact('incidentReport', 'technicians'));
    }

    public function assign(Request $request, IncidentReport $incidentReport)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $incidentReport->update([
            'assigned_to' => $request->assigned_to,
            'assigned_at' => now(),
            'status' => 'assigned',
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.incident-reports.show', $incidentReport)
            ->with('success', 'Incident report assigned to technician successfully.');
    }

    public function updateStatus(Request $request, IncidentReport $incidentReport)
    {
        $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,resolved,closed',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data = ['status' => $request->status];

        if ($request->status === 'in_progress' && !$incidentReport->assigned_to) {
            return back()->with('error', 'Cannot start progress without assigning a technician.');
        }

        if ($request->status === 'resolved') {
            $data['resolved_at'] = now();
            $data['resolved_by'] = auth()->id();
            if ($request->filled('notes')) {
                $data['resolution_notes'] = $request->notes;
            }
        }

        if ($request->filled('notes') && $request->status !== 'resolved') {
            $data['admin_notes'] = $request->notes;
        }

        $incidentReport->update($data);

        return redirect()->route('admin.incident-reports.show', $incidentReport)
            ->with('success', 'Incident report status updated successfully.');
    }

    public function destroy(IncidentReport $incidentReport)
    {
        // Only allow deletion of pending reports
        if (!$incidentReport->isPending()) {
            return back()->with('error', 'Cannot delete incident reports that are already assigned or in progress.');
        }

        $incidentReport->delete();

        return redirect()->route('admin.incident-reports.index')
            ->with('success', 'Incident report deleted successfully.');
    }
}
