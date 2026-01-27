<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CorrectiveMaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MaintenanceRequestCompleted;

class CorrectiveMaintenanceController extends Controller
{
    /**
     * Display list of assigned corrective maintenance tickets
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        $query = CorrectiveMaintenanceRequest::whereHas('technicians', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with(['technicians']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->latest()->paginate(15)->appends($request->except('page'));

        // Statistics for current user
        $stats = [
            'total' => CorrectiveMaintenanceRequest::whereHas('technicians', fn($q) => $q->where('user_id', $userId))->count(),
            'in_progress' => CorrectiveMaintenanceRequest::whereHas('technicians', fn($q) => $q->where('user_id', $userId))->where('status', 'in_progress')->count(),
            'completed' => CorrectiveMaintenanceRequest::whereHas('technicians', fn($q) => $q->where('user_id', $userId))->where('status', 'completed')->count(),
            'failed' => CorrectiveMaintenanceRequest::whereHas('technicians', fn($q) => $q->where('user_id', $userId))->where('status', 'failed')->count(),
        ];

        return view('user.corrective-maintenance.index', compact('tickets', 'stats'));
    }

    /**
     * Show ticket details
     */
    public function show(CorrectiveMaintenanceRequest $ticket)
    {
        // Verify user is assigned to this ticket
        if (!$ticket->technicians()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You are not assigned to this ticket.');
        }

        $ticket->load('technicians');

        return view('user.corrective-maintenance.show', compact('ticket'));
    }

    /**
     * Update work notes
     */
    public function updateNotes(Request $request, CorrectiveMaintenanceRequest $ticket)
    {
        // Verify user is assigned
        if (!$ticket->technicians()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You are not assigned to this ticket.');
        }

        $request->validate([
            'work_notes' => 'required|string|max:2000',
        ]);

        $ticket->work_notes = $request->work_notes;
        $ticket->save();

        return redirect()->back()->with('success', 'Work notes updated.');
    }

    /**
     * Complete the ticket
     */
    public function complete(Request $request, CorrectiveMaintenanceRequest $ticket)
    {
        // Verify user is assigned
        if (!$ticket->technicians()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You are not assigned to this ticket.');
        }

        $request->validate([
            'resolution' => 'required|string|max:2000',
            'status' => 'required|in:completed,failed',
        ]);

        if ($request->status === 'completed') {
            $ticket->markAsCompleted($request->resolution, auth()->id());
        } else {
            $ticket->markAsFailed($request->resolution, auth()->id());
        }

        // Send email to requestor
        try {
            Mail::to($ticket->requestor_email)->send(new MaintenanceRequestCompleted($ticket));
        } catch (\Exception $e) {
            \Log::error('Failed to send completion email: ' . $e->getMessage());
        }

        $statusText = $request->status === 'completed' ? 'completed' : 'marked as failed';
        return redirect()->route('user.corrective-maintenance.index')
            ->with('success', "Ticket {$statusText} successfully.");
    }

    /**
     * Acknowledge assignment
     */
    public function acknowledge(CorrectiveMaintenanceRequest $ticket)
    {
        // Verify user is assigned
        $pivot = $ticket->technicians()->where('user_id', auth()->id())->first();

        if (!$pivot) {
            abort(403, 'You are not assigned to this ticket.');
        }

        // Update acknowledged_at
        $ticket->technicians()->updateExistingPivot(auth()->id(), [
            'acknowledged_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Assignment acknowledged.');
    }
}
