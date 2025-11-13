<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceJob;
use App\Models\WorkReport;
use App\Models\UrgentAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Task Statistics
        $tasks = [
            'pending' => MaintenanceJob::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->count(),
            'in_progress' => MaintenanceJob::where('assigned_to', $user->id)
                ->where('status', 'in_progress')
                ->count(),
            'completed_this_month' => MaintenanceJob::where('assigned_to', $user->id)
                ->where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->count(),
        ];

        // Work Reports Statistics
        // ✅ FIXED: Using user_id
        $reports = [
            'total' => WorkReport::where('user_id', $user->id)->count(),
            'pending' => WorkReport::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'validated' => WorkReport::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
        ];

        // Urgent Alerts Statistics (check if table exists)
        $urgentAlerts = [
            'active' => 0,
            'pending' => 0,
            'critical' => 0,
        ];

        $activeAlerts = collect(); // Empty collection by default

        // Only query if urgent_alerts table exists
        if (Schema::hasTable('urgent_alerts')) {
            try {
                $urgentAlerts = [
                    'active' => UrgentAlert::where('assigned_to', $user->id)
                        ->whereIn('status', [
                            'pending',
                            'acknowledged',
                            'on_the_way',
                            'in_progress',
                        ])
                        ->count(),
                    'pending' => UrgentAlert::where('assigned_to', $user->id)
                        ->where('status', 'pending')
                        ->count(),
                    'critical' => UrgentAlert::where('assigned_to', $user->id)
                        ->whereIn('status', [
                            'pending',
                            'acknowledged',
                            'on_the_way',
                            'in_progress',
                        ])
                        ->where('priority', 'critical')
                        ->count(),
                ];

                // Get Active Urgent Alerts (Top 5)
                $activeAlerts = UrgentAlert::where('assigned_to', $user->id)
                    ->whereIn('status', ['pending', 'acknowledged', 'on_the_way', 'in_progress'])
                    ->with(['creator', 'machine'])
                    ->orderByRaw("FIELD(priority, 'critical', 'urgent', 'high')")
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            } catch (\Exception $e) {
                // If urgent_alerts table doesn't exist or has issues, use defaults
            }
        }

        // Recent Tasks (Last 5) - Exclude completed tasks
        $recentTasks = MaintenanceJob::where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed'])
            ->with('machine')
            ->latest()
            ->limit(5)
            ->get();

        // Recent Work Reports (Last 5)
        // ✅ FIXED: Using user_id
        $recentReports = WorkReport::where('user_id', $user->id)
            ->with(['job.machine'])
            ->latest()
            ->limit(5)
            ->get();

        // Performance Metrics
        $totalCompleted = MaintenanceJob::where('assigned_to', $user->id)
            ->where('status', 'completed')
            ->count();

        $totalAssigned = MaintenanceJob::where('assigned_to', $user->id)->count();

        $completionRate =
            $totalAssigned > 0 ? round(($totalCompleted / $totalAssigned) * 100, 1) : 0;

        return view(
            'user.dashboard',
            compact(
                'tasks',
                'reports',
                'urgentAlerts',
                'activeAlerts',
                'recentTasks',
                'recentReports',
                'completionRate',
            ),
        );
    }
}
