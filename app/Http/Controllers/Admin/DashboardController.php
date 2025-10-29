<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceJob;
use App\Models\Machine;
use App\Models\Part;
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

            // Machines Statistics
            'total_machines' => Machine::count(),
            'operational_machines' => Machine::where('status', 'operational')->count(),
            'maintenance_machines' => Machine::where('status', 'maintenance')->count(),
            'breakdown_machines' => Machine::where('status', 'breakdown')->count(),

            // Parts Statistics
            'total_parts' => Part::count(),
            'low_stock_parts' => Part::whereColumn(
                'stock_quantity',
                '<=',
                'minimum_stock',
            )->count(),
            'out_of_stock_parts' => Part::where('stock_quantity', 0)->count(),
            'total_parts_value' =>
                Part::selectRaw('SUM(stock_quantity * unit_cost) as total')->first()->total ?? 0,

            // Users Statistics
            'total_operators' => User::role('user')->count(),
            'active_operators' => User::role('user')->where('is_active', true)->count(),

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
        // Machines needing urgent attention
        $urgentMachines = Machine::where('status', 'breakdown')
            ->orWhere(function ($query) {
                $query
                    ->where('status', 'operational')
                    ->whereNotNull('next_maintenance_date')
                    ->whereDate('next_maintenance_date', '<=', Carbon::now()->addDays(7));
            })
            ->with('department')
            ->limit(5)
            ->get();

        // High priority pending jobs
        $urgentJobs = MaintenanceJob::with(['machine', 'assignedUser'])
            ->where('status', 'pending')
            ->where('priority', 'high')
            ->latest()
            ->limit(5)
            ->get();

        // Low stock parts
        $lowStockParts = Part::whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->orderBy('stock_quantity')
            ->limit(10)
            ->get();

        // === RECENT ACTIVITIES ===
        // Recent work reports
        $recentReports = WorkReport::with(['user', 'job.machine'])
            ->latest()
            ->limit(5)
            ->get();

        // Recent completed jobs
        $recentCompletedJobs = MaintenanceJob::with(['machine', 'assignedUser'])
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

        // Machines by Status
        $machinesByStatus = Machine::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
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
        $topOperators = User::role('user')
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
                'metricsTrend', // ✅ TAMBAH INI
                'costMetrics',
                'urgentMachines',
                'urgentJobs',
                'lowStockParts',
                'recentReports',
                'recentCompletedJobs',
                'jobsByStatus',
                'jobsByPriority',
                'machinesByStatus',
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
        // Simplified calculation for now
        $totalMachines = Machine::count();
        $operationalMachines = Machine::where('status', 'operational')->count();
        $availability = $totalMachines > 0 ? ($operationalMachines / $totalMachines) * 100 : 0;

        // Performance: Assume 90% for operational machines
        $performance = 90;

        // Quality: Based on successful completions
        $totalJobs = MaintenanceJob::where('status', 'completed')->count();
        $successfulJobs = MaintenanceJob::where('status', 'completed')
            ->whereDoesntHave('workReports', function ($query) {
                // ✅ FIXED - plural
                $query->where('status', 'rejected');
            })
            ->count();
        $quality = $totalJobs > 0 ? ($successfulJobs / $totalJobs) * 100 : 100;

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
