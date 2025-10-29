<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceJob;
use App\Models\WorkReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // My Statistics
        $stats = [
            'my_pending_tasks' => MaintenanceJob::where('assigned_to', $userId)
                                               ->where('status', 'pending')->count(),
            'my_in_progress' => MaintenanceJob::where('assigned_to', $userId)
                                             ->where('status', 'in_progress')->count(),
            'completed_this_month' => MaintenanceJob::where('assigned_to', $userId)
                                                   ->where('status', 'completed')
                                                   ->whereMonth('completed_at', now()->month)->count(),
            'my_reports' => WorkReport::where('user_id', $userId)->count(),
        ];

        // My Tasks
        $myTasks = MaintenanceJob::with(['machine', 'machine.department'])
                                ->where('assigned_to', $userId)
                                ->whereIn('status', ['pending', 'in_progress'])
                                ->orderBy('priority', 'desc')
                                ->orderBy('scheduled_date')
                                ->take(10)
                                ->get();

        // My Recent Reports
        $myRecentReports = WorkReport::with('job.machine')
                                    ->where('user_id', $userId)
                                    ->latest()
                                    ->take(5)
                                    ->get();

        return view('user.dashboard', compact('stats', 'myTasks', 'myRecentReports'));
    }
}