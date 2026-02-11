<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmReport;
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
        // Only get parent tickets (no parent_ticket_id), with their children eager loaded
        $query = CorrectiveMaintenanceRequest::with(['assignedUser', 'handler', 'technicians', 'childTickets.assignedUser', 'childTickets.technicians'])
            ->whereNull('parent_ticket_id');

        // Search filter - also search in child tickets
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('requestor_name', 'like', "%{$search}%")
                  ->orWhere('requestor_email', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('equipment_name', 'like', "%{$search}%")
                  ->orWhereHas('childTickets', function($child) use ($search) {
                      $child->where('ticket_number', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter - include parent if it or any child matches
        if ($request->filled('status')) {
            $status = $request->status;
            $query->where(function($q) use ($status) {
                $q->where('status', $status)
                  ->orWhereHas('childTickets', function($child) use ($status) {
                      $child->where('status', $status);
                  });
            });
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

        // Statistics (count all tickets including children)
        $stats = [
            'total' => CorrectiveMaintenanceRequest::count(),
            'pending' => CorrectiveMaintenanceRequest::where('status', 'pending')->count(),
            'received' => CorrectiveMaintenanceRequest::where('status', 'received')->count(),
            'in_progress' => CorrectiveMaintenanceRequest::where('status', 'in_progress')->count(),
            'done' => CorrectiveMaintenanceRequest::where('status', 'done')->count(),
            'further_repair' => CorrectiveMaintenanceRequest::where('status', 'further_repair')->count(),
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
        $ticket->load(['assignedUser', 'handler', 'technicians', 'report.asset', 'report.submitter', 'parentTicket.report', 'childTickets']);

        // Get maintenance staff and supervisors for manual assignment
        $maintenanceStaff = User::role(['staff_maintenance', 'supervisor_maintenance'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.corrective-maintenance.show', compact('ticket', 'maintenanceStaff'));
    }

    /**
     * List all submitted CM reports
     */
    public function reports(Request $request)
    {
        $query = CmReport::with(['cmRequest', 'asset', 'submitter']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('submitted_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('submitted_at', '<=', $request->date_to);
        }

        $reports = $query->latest('submitted_at')->paginate(20)->appends($request->except('page'));

        return view('admin.corrective-maintenance.reports', compact('reports'));
    }

    /**
     * Create a sub-ticket from a further_repair ticket
     */
    public function createSubTicket(CorrectiveMaintenanceRequest $ticket)
    {
        if ($ticket->status !== 'further_repair') {
            return redirect()->back()->with('error', 'Sub-ticket can only be created from further repair tickets.');
        }

        // Prevent creating sub-ticket from a child ticket (only 1 level allowed)
        if ($ticket->parent_ticket_id) {
            return redirect()->back()->with('error', 'Cannot create sub-ticket from a child ticket. Only one level of sub-tickets is allowed.');
        }

        // Prevent creating multiple sub-tickets
        if ($ticket->childTickets()->count() > 0) {
            return redirect()->back()->with('error', 'This ticket already has a sub-ticket.');
        }

        // Generate sub-ticket number based on parent ticket number
        // Format: {parent_ticket_number}-{sub_sequence} e.g., CMR-20260128-0002-0001
        $parentTicketNumber = $ticket->ticket_number;
        $existingChildCount = CorrectiveMaintenanceRequest::where('parent_ticket_id', $ticket->id)->count();
        $subSequence = str_pad($existingChildCount + 1, 4, '0', STR_PAD_LEFT);
        $subTicketNumber = $parentTicketNumber . '-' . $subSequence;

        $subTicket = CorrectiveMaintenanceRequest::create([
            'ticket_number' => $subTicketNumber,
            'requestor_name' => $ticket->requestor_name,
            'requestor_email' => $ticket->requestor_email,
            'requestor_phone' => $ticket->requestor_phone,
            'requestor_department' => $ticket->requestor_department,
            'location' => $ticket->location,
            'equipment_name' => $ticket->equipment_name,
            'equipment_id' => $ticket->equipment_id,
            'priority' => $ticket->priority,
            'problem_category' => $ticket->problem_category,
            'problem_description' => '[Sub-ticket of ' . $ticket->ticket_number . '] ' . $ticket->problem_description,
            'additional_notes' => $ticket->report ? 'Previous report notes: ' . $ticket->report->notes : null,
            'status' => 'received', // Sub-ticket starts as received (skip pending)
            'received_at' => now(),
            'received_by' => auth()->id(),
            'parent_ticket_id' => $ticket->id,
            'handled_by' => auth()->id(),
        ]);

        // Auto-assign technicians based on current shift
        $subTicket->autoAssignTechnicians();

        // Note: No email sent to requestor for sub-ticket received
        // They can track progress via parent ticket

        return redirect()->route('admin.corrective-maintenance.show', $subTicket)
            ->with('success', 'Sub-ticket ' . $subTicket->ticket_number . ' created and marked as received.');
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

        // Send notification to supervisors and admins
        try {
            $supervisors = User::role('supervisor_maintenance')->get();
            $admins = User::role('admin')->get();

            foreach ($supervisors as $supervisor) {
                Mail::to($supervisor->email)->send(new MaintenanceRequestReceived($ticket));
            }
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new MaintenanceRequestReceived($ticket));
            }

            \Log::info('Ticket received notifications sent to supervisors and admins', [
                'ticket' => $ticket->ticket_number,
                'supervisors_count' => $supervisors->count(),
                'admins_count' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send received notification to supervisors/admins: ' . $e->getMessage());
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

        // Also add to technicians pivot table so user panel can see the ticket
        $ticket->technicians()->syncWithoutDetaching([
            $request->assigned_to => ['notified_at' => now()],
        ]);

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

        // Send notification to supervisors and admins (if not already assigned)
        try {
            $supervisors = User::role('supervisor_maintenance')->where('id', '!=', $request->assigned_to)->get();
            $admins = User::role('admin')->where('id', '!=', $request->assigned_to)->get();

            foreach ($supervisors as $supervisor) {
                Mail::to($supervisor->email)->send(new MaintenanceRequestInProgress($ticket));
            }
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new MaintenanceRequestInProgress($ticket));
            }

            \Log::info('Ticket assignment notifications sent to supervisors and admins', [
                'ticket' => $ticket->ticket_number,
                'supervisors_count' => $supervisors->count(),
                'admins_count' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send assignment notification to supervisors/admins: ' . $e->getMessage());
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
        $ticket->resolution = $request->resolution ?? 'Cancelled by ' . auth()->user()->name;
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
