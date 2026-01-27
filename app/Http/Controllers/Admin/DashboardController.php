<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceJob;
use App\Models\User;
use App\Models\WorkReport;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // === OVERALL STATISTICS ===
        $stats = [
            // Jobs Statistics
            'total_jobs' => MaintenanceJob::count(),
            'pending_jobs' => MaintenanceJob::where('status', 'pending')->count(),
            'in_progress_jobs' => MaintenanceJob::where('status', 'in_progress')->count(),
            'completed_jobs' => MaintenanceJob::where('status', 'completed')->count(),

            // Users Statistics
            'total_operators' => User::role('staff_maintenance')->count(),
            'active_operators' => User::role('staff_maintenance')->where('is_active', true)->count(),

            // Work Reports Statistics
            'total_reports_today' => WorkReport::whereDate('created_at', Carbon::today())->count(),
            'pending_reports' => WorkReport::where('status', 'pending')->count(),
            'completed_reports_today' => WorkReport::where('status', 'completed')
                ->whereDate('updated_at', Carbon::today())
                ->count(),
        ];

        // === INDUSTRIAL MAINTENANCE METRICS ===
        $metrics = $this->calculateIndustrialMetrics();

        // === URGENT ITEMS ===
        // High priority pending jobs
        $urgentJobs = MaintenanceJob::with(['assignedUser'])
            ->where('status', 'pending')
            ->where('priority', 'high')
            ->latest()
            ->limit(5)
            ->get();

        // === RECENT ACTIVITIES ===
        // Recent work reports
        $recentReports = WorkReport::with(['user', 'job'])
            ->latest()
            ->limit(5)
            ->get();

        // Recent completed jobs
        $recentCompletedJobs = MaintenanceJob::with(['assignedUser'])
            ->where('status', 'completed')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        // === CHARTS DATA ===
        // Jobs by Status (for pie chart)
        $jobsByStatus = MaintenanceJob::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            });

        // Jobs by Priority
        $jobsByPriority = MaintenanceJob::selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->priority => $item->count];
            });

        // Last 7 days jobs trend
        $last7DaysJobs = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $last7DaysJobs[] = [
                'date' => $date->format('M d'),
                'count' => MaintenanceJob::whereDate('created_at', $date->toDateString())->count(),
                'completed' => MaintenanceJob::where('status', 'completed')
                    ->whereDate('updated_at', $date->toDateString())
                    ->count(),
            ];
        }

        // Jobs by Department
        $jobsByDepartment = collect();

        // === COST TRACKING ===
        $costMetrics = $this->calculateCostMetrics();

        // === PERFORMANCE METRICS ===
        // Average completion time
        $avgCompletionTime =
            MaintenanceJob::where('status', 'completed')
                ->whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, started_at, completed_at)) as avg_hours')
                ->first()->avg_hours ?? 0;

        // Top 5 operators by completed tasks this month
        $topOperators = User::role('staff_maintenance')
            ->withCount([
                'workReports as completed_this_month' => function ($query) {
                    $query
                        ->where('status', 'completed')
                        ->whereMonth('updated_at', Carbon::now()->month);
                },
            ])
            ->having('completed_this_month', '>', 0)
            ->orderBy('completed_this_month', 'desc')
            ->limit(5)
            ->get();

        // === METRICS TREND DATA ===
        $metricsTrend = $this->calculateMetricsTrend();

        return view(
            'admin.dashboard',
            compact(
                'stats',
                'metrics',
                'metricsTrend',
                'costMetrics',
                'urgentJobs',
                'recentReports',
                'recentCompletedJobs',
                'jobsByStatus',
                'jobsByPriority',
                'last7DaysJobs',
                'jobsByDepartment',
                'avgCompletionTime',
                'topOperators',
            ),
        );
    }

    /**
     * Calculate Industrial Maintenance Metrics
     * MTBF, MTTR, OEE
     */
    private function calculateIndustrialMetrics()
    {
        // === MTBF (Mean Time Between Failures) ===
        // Calculate average time between breakdown jobs
        $breakdownJobs = MaintenanceJob::where('type', 'breakdown')
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->orderBy('completed_at')
            ->get();

        $mtbf = 0;
        if ($breakdownJobs->count() > 1) {
            $totalTimeBetweenFailures = 0;
            for ($i = 1; $i < $breakdownJobs->count(); $i++) {
                $timeDiff = $breakdownJobs[$i]->completed_at->diffInHours(
                    $breakdownJobs[$i - 1]->completed_at,
                );
                $totalTimeBetweenFailures += $timeDiff;
            }
            $mtbf = $totalTimeBetweenFailures / ($breakdownJobs->count() - 1);
        }

        // === MTTR (Mean Time To Repair) ===
        // Average time to complete repair jobs
        $mttr =
            MaintenanceJob::where('status', 'completed')
                ->whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, started_at, completed_at)) as avg_hours')
                ->first()->avg_hours ?? 0;

        // === OEE (Overall Equipment Effectiveness) ===
        // OEE = Availability × Performance × Quality
        // Simplified calculation based on maintenance jobs completion

        // Availability: Based on completed vs total jobs
        $totalJobs = MaintenanceJob::count();
        $completedJobs = MaintenanceJob::where('status', 'completed')->count();
        $availability = $totalJobs > 0 ? ($completedJobs / $totalJobs) * 100 : 100;

        // Performance: Assume 90% for now (can be refined later)
        $performance = 90;

        // Quality: Based on successful completions
        $successfulJobs = MaintenanceJob::where('status', 'completed')
            ->whereDoesntHave('workReports', function ($query) {
                $query->where('status', 'rejected');
            })
            ->count();
        $quality = $completedJobs > 0 ? ($successfulJobs / $completedJobs) * 100 : 100;

        $oee = ($availability * $performance * $quality) / 10000;

        return [
            'mtbf' => round($mtbf, 1), // hours
            'mttr' => round($mttr, 1), // hours
            'oee' => round($oee, 1), // percentage
            'availability' => round($availability, 1), // percentage
            'performance' => $performance, // percentage
            'quality' => round($quality, 1), // percentage
        ];
    }

    /**
     * Calculate Cost Metrics
     * Total parts cost, labor cost, resource usage
     */
    private function calculateCostMetrics()
    {
        // Total parts cost (this month)
        // TODO: Will calculate when report_parts table is ready
        $partsCostThisMonth = 0; // Temporary set to 0

        // Total labor time (this month) in hours
        $laborHoursThisMonth =
            MaintenanceJob::where('status', 'completed')
                ->whereMonth('completed_at', Carbon::now()->month)
                ->whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->selectRaw('SUM(TIMESTAMPDIFF(HOUR, started_at, completed_at)) as total_hours')
                ->first()->total_hours ?? 0;

        // Assume average labor cost per hour (IDR)
        $laborCostPerHour = 50000; // Rp 50,000/hour
        $laborCostThisMonth = $laborHoursThisMonth * $laborCostPerHour;

        // Total maintenance cost
        $totalCostThisMonth = $partsCostThisMonth + $laborCostThisMonth;

        return [
            'parts_cost' => $partsCostThisMonth,
            'labor_hours' => $laborHoursThisMonth,
            'labor_cost' => $laborCostThisMonth,
            'total_cost' => $totalCostThisMonth,
        ];
    }

    /**
     * Calculate MTBF and MTTR Trend (Last 30 days)
     */
    private function calculateMetricsTrend()
    {
        $trend = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->toDateString();

            // Calculate MTBF for this day
            $breakdownJobsUpToDate = MaintenanceJob::where('type', 'breakdown')
                ->where('status', 'completed')
                ->whereNotNull('completed_at')
                ->whereDate('completed_at', '<=', $dateStr)
                ->orderBy('completed_at')
                ->get();

            $mtbf = 0;
            if ($breakdownJobsUpToDate->count() > 1) {
                $totalTimeBetweenFailures = 0;
                for ($j = 1; $j < $breakdownJobsUpToDate->count(); $j++) {
                    $timeDiff = $breakdownJobsUpToDate[$j]->completed_at->diffInHours(
                        $breakdownJobsUpToDate[$j - 1]->completed_at,
                    );
                    $totalTimeBetweenFailures += $timeDiff;
                }
                $mtbf = $totalTimeBetweenFailures / ($breakdownJobsUpToDate->count() - 1);
            }

            // Calculate MTTR for this day
            $mttr =
                MaintenanceJob::where('status', 'completed')
                    ->whereDate('completed_at', $dateStr)
                    ->whereNotNull('started_at')
                    ->whereNotNull('completed_at')
                    ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, started_at, completed_at)) as avg_hours')
                    ->first()->avg_hours ?? 0;

            $trend[] = [
                'date' => $date->format('M d'),
                'mtbf' => round($mtbf, 1),
                'mttr' => round($mttr, 1),
            ];
        }

        return $trend;
    }
}
