<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\CmReport;
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

        $assignedScope = fn($query) => $query->where(function ($q) use ($userId) {
            $q->whereHas('technicians', fn($sub) => $sub->where('user_id', $userId))
              ->orWhere('assigned_to', $userId);
        });

        $query = $assignedScope(CorrectiveMaintenanceRequest::query())->with(['technicians']);

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
            'total' => $assignedScope(CorrectiveMaintenanceRequest::query())->count(),
            'in_progress' => $assignedScope(CorrectiveMaintenanceRequest::query())->where('status', 'in_progress')->count(),
            'done' => $assignedScope(CorrectiveMaintenanceRequest::query())->whereIn('status', ['done', 'completed'])->count(),
            'further_repair' => $assignedScope(CorrectiveMaintenanceRequest::query())->whereIn('status', ['further_repair', 'failed'])->count(),
        ];

        return view('user.corrective-maintenance.index', compact('tickets', 'stats'));
    }

    /**
     * Show ticket details
     */
    public function show(CorrectiveMaintenanceRequest $ticket)
    {
        // Verify user is assigned to this ticket
        if (!$ticket->technicians()->where('user_id', auth()->id())->exists() && $ticket->assigned_to !== auth()->id()) {
            abort(403, 'You are not assigned to this ticket.');
        }

        $ticket->load([
            'technicians',
            'report.asset',
            'report.submitter',
            'childTickets.technicians',
            'childTickets.report',
            'parentTicket.report.asset'
        ]);

        $assets = Asset::where('status', 'active')->orderBy('asset_name')->get();

        return view('user.corrective-maintenance.show', compact('ticket', 'assets'));
    }

    /**
     * Update work notes
     */
    public function updateNotes(Request $request, CorrectiveMaintenanceRequest $ticket)
    {
        // Verify user is assigned
        if (!$ticket->technicians()->where('user_id', auth()->id())->exists() && $ticket->assigned_to !== auth()->id()) {
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
     * Submit report for the ticket
     */
    public function submitReport(Request $request, CorrectiveMaintenanceRequest $ticket)
    {
        // Verify user is assigned
        if (!$ticket->technicians()->where('user_id', auth()->id())->exists() && $ticket->assigned_to !== auth()->id()) {
            abort(403, 'You are not assigned to this ticket.');
        }

        if ($ticket->status !== 'in_progress') {
            return redirect()->back()->with('error', 'Report can only be submitted for in-progress tickets.');
        }

        $request->validate([
            'status' => 'required|in:done,further_repair,failed',
            'asset_id' => 'nullable|exists:assets_master,id',
            'problem_detail' => 'required|string|max:2000',
            'work_done' => 'required|string|max:2000',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Create report
        CmReport::create([
            'cm_request_id' => $ticket->id,
            'asset_id' => $request->asset_id,
            'status' => $request->status,
            'problem_detail' => $request->problem_detail,
            'work_done' => $request->work_done,
            'notes' => $request->notes,
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        // Update ticket status
        $ticket->status = $request->status;
        $ticket->report_submitted_at = now();
        $ticket->completed_at = now();
        $ticket->resolution = $request->work_done;
        $ticket->save();

        // Send email to requestor
        try {
            Mail::to($ticket->requestor_email)->send(new MaintenanceRequestCompleted($ticket));
        } catch (\Exception $e) {
            \Log::error('Failed to send report email: ' . $e->getMessage());
        }

        // Send notification to supervisors and admins
        try {
            $supervisors = User::role('supervisor_maintenance')->get();
            $admins = User::role('admin')->get();

            foreach ($supervisors as $supervisor) {
                Mail::to($supervisor->email)->send(new MaintenanceRequestCompleted($ticket));
            }
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new MaintenanceRequestCompleted($ticket));
            }

            \Log::info('CM report completion notifications sent to supervisors and admins', [
                'ticket' => $ticket->ticket_number,
                'status' => $request->status,
                'supervisors_count' => $supervisors->count(),
                'admins_count' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send completion notification to supervisors/admins: ' . $e->getMessage());
        }

        $statusLabels = [
            'done' => 'Done',
            'further_repair' => 'Further Repair',
            'failed' => 'Failed',
        ];
        $statusText = $statusLabels[$request->status] ?? ucfirst($request->status);
        return redirect()->route('user.corrective-maintenance.index')
            ->with('success', "Report submitted successfully. Status: {$statusText}.");
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
