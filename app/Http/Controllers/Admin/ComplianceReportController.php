<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockOpnameSchedule;
use Illuminate\Http\Request;

class ComplianceReportController extends Controller
{
    /**
     * Display a listing of closed tickets (compliance reports)
     */
    public function index(Request $request)
    {
        $query = StockOpnameSchedule::where('ticket_status', 'closed')
            ->with(['closedByUser', 'assignedUsers']);

        // Filter by execution type
        if ($request->filled('execution_type')) {
            $query->where('execution_type', $request->execution_type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('closed_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('closed_at', '<=', $request->end_date);
        }

        // Search by schedule code
        if ($request->filled('search')) {
            $query->where('schedule_code', 'like', '%' . $request->search . '%');
        }

        $closedTickets = $query->orderBy('closed_at', 'desc')->paginate(20);

        // Statistics
        $stats = [
            'total_closed' => StockOpnameSchedule::where('ticket_status', 'closed')->count(),
            'early_completion' => StockOpnameSchedule::where('ticket_status', 'closed')->where('execution_type', 'early')->count(),
            'ontime_completion' => StockOpnameSchedule::where('ticket_status', 'closed')->where('execution_type', 'ontime')->count(),
            'late_completion' => StockOpnameSchedule::where('ticket_status', 'closed')->where('execution_type', 'late')->count(),
        ];

        return view('admin.opname.compliance.index', compact('closedTickets', 'stats'));
    }

    /**
     * Display the specified compliance report details
     */
    public function show(StockOpnameSchedule $schedule)
    {
        // Make sure it's a closed ticket
        if ($schedule->ticket_status !== 'closed') {
            return redirect()->route('admin.opname.schedules.show', $schedule)
                ->with('error', 'This ticket is not closed yet.');
        }

        // Load relationships
        $schedule->load([
            'closedByUser',
            'assignedUsers',
            'scheduleItems.executedByUser',
            'scheduleItems.sparepart',
            'scheduleItems.tool',
            'scheduleItems.asset'
        ]);

        // Get all completed items with details
        $items = $schedule->scheduleItems()
            ->where('execution_status', 'completed')
            ->with(['executedByUser', 'sparepart', 'tool', 'asset'])
            ->get();

        // Get analytics
        $analytics = $schedule->getAnalytics();

        return view('admin.opname.compliance.show', compact('schedule', 'items', 'analytics'));
    }
}
