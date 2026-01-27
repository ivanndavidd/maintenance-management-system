<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectiveMaintenanceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MaintenanceRequestReceived;
use App\Mail\MaintenanceRequestInProgress;
use App\Mail\MaintenanceRequestCompleted;
use App\Mail\MaintenanceRequestAssigned;

class CorrectiveMaintenanceController extends Controller
{
    /**
     * Display list of all tickets
     */
    public function index(Request $request)
    {
        $query = CorrectiveMaintenanceRequest::with(['assignedUser', 'handler', 'technicians']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('requestor_name', 'like', "%{$search}%")
                  ->orWhere('requestor_email', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('equipment_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Problem category filter
        if ($request->filled('problem_category')) {
            $query->where('problem_category', $request->problem_category);
        }

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $tickets = $query->latest()->paginate(20)->appends($request->except('page'));

        // Statistics
        $stats = [
            'total' => CorrectiveMaintenanceRequest::count(),
            'pending' => CorrectiveMaintenanceRequest::where('status', 'pending')->count(),
            'received' => CorrectiveMaintenanceRequest::where('status', 'received')->count(),
            'in_progress' => CorrectiveMaintenanceRequest::where('status', 'in_progress')->count(),
            'completed' => CorrectiveMaintenanceRequest::where('status', 'completed')->count(),
            'failed' => CorrectiveMaintenanceRequest::where('status', 'failed')->count(),
        ];

        return view('admin.corrective-maintenance.index', compact('tickets', 'stats'));
    }

    /**
     * Show ticket details
     */
    public function show(CorrectiveMaintenanceRequest $ticket)
    {
        $ticket->load(['assignedUser', 'handler', 'technicians']);

        // Get maintenance staff for manual assignment (if needed as backup)
        $maintenanceStaff = User::role('staff_maintenance')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.corrective-maintenance.show', compact('ticket', 'maintenanceStaff'));
    }

    /**
     * Update ticket status to received
     */
    public function markReceived(CorrectiveMaintenanceRequest $ticket)
    {
        if ($ticket->status !== 'pending') {
            return redirect()->back()->with('error', 'Ticket is not in pending status.');
        }

        $ticket->markAsReceived(auth()->id());

        // Send email to requestor
        try {
            Mail::to($ticket->requestor_email)->send(new MaintenanceRequestReceived($ticket));
        } catch (\Exception $e) {
            \Log::error('Failed to send received email: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Ticket marked as received and email sent to requestor.');
    }

    /**
     * Assign ticket to maintenance staff and mark as in progress
     */
    public function assign(Request $request, CorrectiveMaintenanceRequest $ticket)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'work_notes' => 'nullable|string|max:1000',
        ]);

        $ticket->markAsInProgress($request->assigned_to, auth()->id());

        if ($request->filled('work_notes')) {
            $ticket->work_notes = $request->work_notes;
            $ticket->save();
        }

        // Send email to requestor
        try {
            Mail::to($ticket->requestor_email)->send(new MaintenanceRequestInProgress($ticket));
        } catch (\Exception $e) {
            \Log::error('Failed to send in-progress email to requestor: ' . $e->getMessage());
        }

        // Send email to assigned staff
        try {
            $assignedUser = User::find($request->assigned_to);
            if ($assignedUser && $assignedUser->email) {
                Mail::to($assignedUser->email)->send(new MaintenanceRequestAssigned($ticket, $assignedUser));
                \Log::info('Assignment email sent to technician', [
                    'ticket' => $ticket->ticket_number,
                    'technician' => $assignedUser->name,
                    'email' => $assignedUser->email,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send assignment email to staff: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Ticket assigned to ' . $ticket->assignedUser->name . '. Emails sent.');
    }

    /**
     * Complete the ticket
     */
    public function complete(Request $request, CorrectiveMaintenanceRequest $ticket)
    {
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
        return redirect()->back()->with('success', "Ticket {$statusText}. Email sent to requestor.");
    }

    /**
     * Cancel the ticket
     */
    public function cancel(Request $request, CorrectiveMaintenanceRequest $ticket)
    {
        $request->validate([
            'resolution' => 'nullable|string|max:1000',
        ]);

        $ticket->status = 'cancelled';
        $ticket->resolution = $request->resolution ?? 'Cancelled by admin';
        $ticket->handled_by = auth()->id();
        $ticket->save();

        return redirect()->back()->with('success', 'Ticket cancelled.');
    }

    /**
     * Update work notes
     */
    public function updateNotes(Request $request, CorrectiveMaintenanceRequest $ticket)
    {
        $request->validate([
            'work_notes' => 'required|string|max:2000',
        ]);

        $ticket->work_notes = $request->work_notes;
        $ticket->save();

        return redirect()->back()->with('success', 'Work notes updated.');
    }
}
