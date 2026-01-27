<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use App\Models\TaskRequest;
use App\Models\Machine;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // Statistics for incident reports
        $incidentStats = [
            'total' => IncidentReport::byReporter($userId)->count(),
            'pending' => IncidentReport::byReporter($userId)->pending()->count(),
            'in_progress' => IncidentReport::byReporter($userId)->inProgress()->count(),
            'resolved' => IncidentReport::byReporter($userId)->resolved()->count(),
            'critical' => IncidentReport::byReporter($userId)->critical()->count(),
        ];

        // Statistics for task requests
        $taskRequestStats = [
            'total' => TaskRequest::byRequester($userId)->count(),
            'pending' => TaskRequest::byRequester($userId)->pending()->count(),
            'approved' => TaskRequest::byRequester($userId)->approved()->count(),
            'rejected' => TaskRequest::byRequester($userId)->rejected()->count(),
            'completed' => TaskRequest::byRequester($userId)->completed()->count(),
        ];

        // Recent incident reports (last 5)
        $recentIncidents = IncidentReport::byReporter($userId)
            ->with(['machine', 'assignedUser'])
            ->latest()
            ->take(5)
            ->get();

        // Recent task requests (last 5)
        $recentTaskRequests = TaskRequest::byRequester($userId)
            ->with(['machine', 'reviewer'])
            ->latest()
            ->take(5)
            ->get();

        // Critical/High severity incidents
        $criticalIncidents = IncidentReport::byReporter($userId)
            ->high()
            ->whereIn('status', ['pending', 'assigned', 'in_progress'])
            ->with('machine')
            ->latest()
            ->take(5)
            ->get();

        // Machines count
        $machinesCount = Machine::count();

        return view('pic.dashboard', compact(
            'incidentStats',
            'taskRequestStats',
            'recentIncidents',
            'recentTaskRequests',
            'criticalIncidents',
            'machinesCount'
        ));
    }
}
