<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use Illuminate\Http\Request;

class AssignedIncidentController extends Controller
{
    /**
     * Display a listing of assigned incidents
     */
    public function index(Request $request)
    {
        $query = IncidentReport::whereHas('operators', function ($q) {
            $q->where('user_id', auth()->id());
        })->with(['machine', 'reporter', 'operators', 'completedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by severity
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $incidents = $query->latest()->paginate(15);

        return view('user.assigned-incidents.index', compact('incidents'));
    }

    /**
     * Display the specified incident
     */
    public function show(IncidentReport $incidentReport)
    {
        // Verify user is assigned to this incident
        if (!$incidentReport->operators->contains(auth()->id())) {
            abort(403, 'You are not assigned to this incident.');
        }

        $incidentReport->load(['machine', 'reporter', 'operators', 'completedBy']);

        return view('user.assigned-incidents.show', compact('incidentReport'));
    }

    /**
     * Mark incident as in progress
     */
    public function startWork(IncidentReport $incidentReport)
    {
        // Verify user is assigned
        if (!$incidentReport->operators->contains(auth()->id())) {
            abort(403, 'You are not assigned to this incident.');
        }

        // Check if not already completed
        if ($incidentReport->completed_by) {
            return back()->with('error', 'This incident has already been completed.');
        }

        $incidentReport->update([
            'status' => 'in_progress'
        ]);

        return back()->with('success', 'Started working on this incident.');
    }

    /**
     * Complete the incident
     */
    public function complete(Request $request, IncidentReport $incidentReport)
    {
        // Verify user is assigned
        if (!$incidentReport->operators->contains(auth()->id())) {
            abort(403, 'You are not assigned to this incident.');
        }

        // Check if not already completed
        if ($incidentReport->completed_by) {
            return back()->with('error', 'This incident has already been completed.');
        }

        $validated = $request->validate([
            'resolution_notes' => 'required|string|max:1000',
        ]);

        $incidentReport->update([
            'status' => 'resolved',
            'completed_by' => auth()->id(),
            'completed_at' => now(),
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
            'resolution_notes' => $validated['resolution_notes'],
        ]);

        return redirect()
            ->route('user.assigned-incidents.index')
            ->with('success', 'Incident marked as resolved successfully!');
    }
}
