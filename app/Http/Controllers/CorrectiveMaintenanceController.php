<?php

namespace App\Http\Controllers;

use App\Models\CorrectiveMaintenanceRequest;
use App\Models\User;
use App\Services\ShiftAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MaintenanceRequestReceived;
use App\Mail\MaintenanceTicketAssigned;

class CorrectiveMaintenanceController extends Controller
{
    /**
     * Show the public request form (no login required)
     */
    public function create()
    {
        return view('maintenance-request.create');
    }

    /**
     * Store a new maintenance request (no login required)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'requestor_name' => 'required|string|max:255',
            'requestor_email' => 'required|email|max:255',
            'problem_category' => 'required|in:conveyor_totebox,conveyor_paket,lift_merah,lift_kuning,chute,others',
            'problem_description' => 'required|string|max:2000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
        ]);

        // Set default values for removed fields
        $validated['priority'] = 'medium';

        // Generate ticket number
        $validated['ticket_number'] = CorrectiveMaintenanceRequest::generateTicketNumber();
        $validated['status'] = 'pending';

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = $validated['ticket_number'] . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('maintenance-attachments', $filename, 'public');
            $validated['attachment_path'] = $path;
        }

        // Create ticket
        $ticket = CorrectiveMaintenanceRequest::create($validated);

        // Get shift assignment service
        $shiftService = new ShiftAssignmentService();
        $shiftInfo = $shiftService->getShiftInfo();

        // Send received email to requestor
        try {
            Mail::to($ticket->requestor_email)->send(new MaintenanceRequestReceived($ticket));
            $ticket->received_email_sent_at = now();
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

            \Log::info('New CM ticket notifications sent to supervisors and admins', [
                'ticket' => $ticket->ticket_number,
                'supervisors_count' => $supervisors->count(),
                'admins_count' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send CM ticket notification to supervisors/admins: ' . $e->getMessage());
        }

        // Auto-assign technicians based on current shift
        $assignedIds = $ticket->autoAssignTechnicians();

        if (!empty($assignedIds)) {
            // Send notification emails to assigned technicians
            $ticket->load('technicians');

            foreach ($ticket->technicians as $technician) {
                try {
                    $techShiftInfo = $technician->pivot->shift_info ?? ($shiftInfo ? $shiftInfo['name'] : null);
                    Mail::to($technician->email)->send(new MaintenanceTicketAssigned($ticket, $technician, $techShiftInfo));

                    \Log::info('Sent assignment email to technician', [
                        'ticket' => $ticket->ticket_number,
                        'technician' => $technician->name,
                        'email' => $technician->email,
                        'shift' => $techShiftInfo,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send assignment email to technician: ' . $e->getMessage(), [
                        'technician_id' => $technician->id,
                        'ticket_number' => $ticket->ticket_number,
                    ]);
                }
            }

            // Notify supervisors and admins about the assignment
            try {
                $supervisors = User::role('supervisor_maintenance')->get();
                $admins = User::role('admin')->get();

                foreach ($supervisors as $supervisor) {
                    Mail::to($supervisor->email)->send(new MaintenanceTicketAssigned($ticket, $supervisor, $shiftInfo ? $shiftInfo['name'] : null));
                }
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new MaintenanceTicketAssigned($ticket, $admin, $shiftInfo ? $shiftInfo['name'] : null));
                }

                \Log::info('CM ticket assignment notifications sent to supervisors and admins', [
                    'ticket' => $ticket->ticket_number,
                    'supervisors_count' => $supervisors->count(),
                    'admins_count' => $admins->count(),
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send assignment notification to supervisors/admins: ' . $e->getMessage());
            }

            // Update ticket status
            $ticket->status = 'in_progress';
            $ticket->received_at = now();
            $ticket->in_progress_at = now();
            $ticket->progress_email_sent_at = now();
            $ticket->save();

            \Log::info('Ticket auto-assigned to technicians on duty', [
                'ticket' => $ticket->ticket_number,
                'technicians' => $assignedIds,
                'shift' => $shiftInfo,
            ]);
        } else {
            // No technicians on duty, mark as received only
            $ticket->status = 'received';
            $ticket->received_at = now();
            $ticket->save();

            \Log::info('No technicians on duty for auto-assignment', [
                'ticket' => $ticket->ticket_number,
                'datetime' => now()->toDateTimeString(),
            ]);
        }

        return redirect()->route('maintenance-request.success', $ticket->ticket_number);
    }

    /**
     * Show success page with ticket details
     */
    public function success($ticketNumber)
    {
        $ticket = CorrectiveMaintenanceRequest::with('technicians')
            ->where('ticket_number', $ticketNumber)
            ->firstOrFail();

        return view('maintenance-request.success', compact('ticket'));
    }

    /**
     * Track ticket status (public access by ticket number)
     */
    public function track(Request $request)
    {
        $ticketNumber = $request->query('ticket');

        if ($ticketNumber) {
            $ticket = CorrectiveMaintenanceRequest::with([
                'assignedUser',
                'technicians',
                'report',
                'childTickets.technicians',
                'childTickets.report'
            ])
                ->where('ticket_number', $ticketNumber)
                ->first();
            return view('maintenance-request.track', compact('ticket', 'ticketNumber'));
        }

        return view('maintenance-request.track', ['ticket' => null, 'ticketNumber' => null]);
    }

    /**
     * Search and redirect to track page
     */
    public function trackSearch(Request $request)
    {
        $ticketNumber = $request->query('ticket');

        if (!$ticketNumber) {
            return redirect()->route('maintenance-request.track')
                ->with('error', 'Please enter a ticket number.');
        }

        $ticket = CorrectiveMaintenanceRequest::with([
            'assignedUser',
            'technicians',
            'report',
            'childTickets.technicians',
            'childTickets.report'
        ])
            ->where('ticket_number', $ticketNumber)
            ->first();

        if (!$ticket) {
            return redirect()->route('maintenance-request.track')
                ->with('error', 'Ticket not found. Please check the ticket number and try again.');
        }

        return view('maintenance-request.track', compact('ticket'));
    }
}
