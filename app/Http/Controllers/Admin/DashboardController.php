<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\CmReport;
use App\Models\CorrectiveMaintenanceRequest;
use App\Models\PmSchedule;
use App\Models\PmTask;
use App\Models\Sparepart;
use App\Models\StockOpnameScheduleItem;
use App\Models\StockOpnameUserAssignment;
use App\Models\ShiftAssignment;
use App\Models\Tool;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // === CMR Statistics ===
        $cmrStats = CorrectiveMaintenanceRequest::selectRaw("
            COUNT(*) as total,
            SUM(status IN ('pending', 'received')) as pending,
            SUM(status = 'in_progress') as in_progress,
            SUM(status IN ('completed', 'done')) as completed,
            SUM(status = 'further_repair') as further_repair
        ")->first();

        $userStats = Cache::remember('dashboard_user_stats', 300, function () {
            return [
                'total_operators' => User::role('staff_maintenance')->count(),
                'active_operators' => User::role('staff_maintenance')->where('is_active', true)->count(),
            ];
        });

        $stats = [
            'total_cmr' => (int) $cmrStats->total,
            'pending_cmr' => (int) $cmrStats->pending,
            'in_progress_cmr' => (int) $cmrStats->in_progress,
            'completed_cmr' => (int) $cmrStats->completed,
            'further_repair_cmr' => (int) $cmrStats->further_repair,
            'total_operators' => $userStats['total_operators'],
            'active_operators' => $userStats['active_operators'],
        ];

        // === PM Statistics ===
        $pmStats = PmSchedule::selectRaw("
            COUNT(*) as total,
            SUM(status = 'draft') as draft,
            SUM(status = 'active') as active,
            SUM(status = 'completed') as completed
        ")->first();

        $stats['total_pm'] = (int) $pmStats->total;
        $stats['draft_pm'] = (int) $pmStats->draft;
        $stats['active_pm'] = (int) $pmStats->active;
        $stats['completed_pm'] = (int) $pmStats->completed;

        // === CMR by Status (for chart) ===
        $cmrByStatus = CorrectiveMaintenanceRequest::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // === CMR Trend (Last 7 Days) ===
        $sevenDaysAgo = Carbon::now()->subDays(6)->toDateString();
        $last7DaysCreated = CorrectiveMaintenanceRequest::selectRaw("
            DATE(created_at) as date, COUNT(*) as count
        ")
            ->whereDate('created_at', '>=', $sevenDaysAgo)
            ->groupByRaw('DATE(created_at)')
            ->pluck('count', 'date');

        $last7DaysCompleted = CorrectiveMaintenanceRequest::selectRaw("
            DATE(completed_at) as date, COUNT(*) as count
        ")
            ->whereIn('status', ['completed', 'done'])
            ->whereDate('completed_at', '>=', $sevenDaysAgo)
            ->groupByRaw('DATE(completed_at)')
            ->pluck('count', 'date');

        $last7DaysTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->toDateString();
            $last7DaysTrend[] = [
                'date' => $date->format('M d'),
                'created' => $last7DaysCreated[$dateStr] ?? 0,
                'completed' => $last7DaysCompleted[$dateStr] ?? 0,
            ];
        }

        // === Recent CMR Tickets ===
        $recentCmr = CorrectiveMaintenanceRequest::with(['technicians'])
            ->latest()
            ->limit(5)
            ->get();

        // === Sparepart Stock Summary ===
        $sparepartStats = DB::connection('site')->table('spareparts')->selectRaw("
            COUNT(*) as total,
            SUM(quantity = 0) as out_of_stock,
            SUM(quantity > 0 AND quantity <= minimum_stock) as low_stock
        ")->first();

        // === Repair Cost per year (monthly breakdown, all available years) ===
        $availableYears = DB::connection('site')->table('sparepart_usages')
            ->selectRaw('YEAR(used_at) as year')
            ->whereNotNull('used_at')
            ->groupByRaw('YEAR(used_at)')
            ->orderBy('year')
            ->pluck('year')
            ->toArray();

        // Always include current year even if no data yet
        $currentYear = Carbon::now()->year;
        if (!in_array($currentYear, $availableYears)) {
            $availableYears[] = $currentYear;
            sort($availableYears);
        }

        $costByYearRaw = DB::connection('site')->table('sparepart_usages as u')
            ->join('spareparts as s', 's.id', '=', 'u.sparepart_id')
            ->whereIn(DB::raw('YEAR(u.used_at)'), $availableYears)
            ->selectRaw("YEAR(u.used_at) as year, MONTH(u.used_at) as month_num, SUM(u.quantity_used * s.parts_price) as total_cost")
            ->groupByRaw("YEAR(u.used_at), MONTH(u.used_at)")
            ->get();

        // Build [year => [1..12 => cost]] structure
        $costTrend = [];
        foreach ($availableYears as $year) {
            $months = [];
            for ($m = 1; $m <= 12; $m++) {
                $row = $costByYearRaw->first(fn($r) => $r->year == $year && $r->month_num == $m);
                $months[] = [
                    'month' => Carbon::create($year, $m, 1)->format('M'),
                    'cost'  => $row ? (float) $row->total_cost : 0,
                ];
            }
            $costTrend[$year] = $months;
        }

        // === Calendar Data for Supervisor ===
        $todayTasks = collect();
        $calendarData = [];

        if (auth()->user()->hasRole('supervisor_maintenance')) {
            $userId = auth()->id();
            $today = Carbon::today();

            // Get today's tasks
            $todayTasks = $this->getTodayTasks($userId, $today);

            // Get calendar data for the entire year
            $calendarData = $this->getYearCalendarData($userId);
        }

        return view('admin.dashboard', compact(
            'stats',
            'cmrByStatus',
            'last7DaysTrend',
            'recentCmr',
            'todayTasks',
            'calendarData',
            'sparepartStats',
            'costTrend'
        ));
    }

    /**
     * Get calendar data for the entire year (to avoid multiple API calls)
     */
    private function getYearCalendarData($userId)
    {
        $currentYear = Carbon::now()->year;
        $data = [];

        // Get data for entire year (January to December)
        for ($month = 1; $month <= 12; $month++) {
            $monthData = $this->getCalendarData($userId, $month, $currentYear);
            $data = array_merge($data, $monthData);
        }

        return $data;
    }

    /**
     * Get all tasks for today
     */
    private function getTodayTasks($userId, $today)
    {
        $tasks = [];

        // Corrective Maintenance Tasks
        $cmTasks = CorrectiveMaintenanceRequest::where(function ($q) use ($userId) {
            $q->whereHas('technicians', fn($sub) => $sub->where('user_id', $userId))
              ->orWhere('assigned_to', $userId);
        })
        ->whereIn('status', ['pending', 'in_progress'])
        ->whereDate('created_at', $today)
        ->get()
        ->map(function($task) {
            return [
                'type' => 'corrective',
                'id' => $task->ticket_number,
                'title' => $task->equipment_name ?? 'Corrective Maintenance',
                'status' => $task->status,
                'priority' => $task->priority,
                'url' => route('supervisor.my-tasks.corrective-maintenance.show', $task->id)
            ];
        });

        // Preventive Maintenance Tasks
        $pmTasks = PmTask::with('sprGroup.cleaningGroup')
            ->where('assigned_user_id', $userId)
            ->where('task_date', $today)
            ->whereIn('status', ['pending', 'in_progress'])
            ->get()
            ->map(function($task) {
                $scheduleId = $task->sprGroup?->cleaningGroup?->pm_schedule_id;
                return [
                    'type' => 'preventive',
                    'id' => $task->id,
                    'title' => $task->task_name,
                    'status' => $task->status,
                    'shift' => $task->assigned_shift_id,
                    'url' => $scheduleId ? route('supervisor.my-tasks.preventive-maintenance.show', $scheduleId) : '#'
                ];
            });

        // Stock Opname Tasks
        $stockTasks = StockOpnameUserAssignment::where('user_id', $userId)
            ->whereHas('schedule', function($q) use ($today) {
                $q->where('execution_date', $today)
                  ->whereIn('status', ['active']);
            })
            ->with('schedule')
            ->get()
            ->map(function($assignment) {
                return [
                    'type' => 'stock_opname',
                    'id' => $assignment->schedule->schedule_code,
                    'title' => 'Stock Opname - ' . $assignment->schedule->schedule_code,
                    'status' => $assignment->schedule->status,
                    'shift' => $assignment->shift_id ?? null,
                    'url' => route('supervisor.my-tasks.stock-opname.show', $assignment->schedule->id)
                ];
            });

        return $cmTasks->concat($pmTasks)->concat($stockTasks);
    }

    /**
     * Get calendar data for the current month
     */
    private function getCalendarData($userId, $month = null, $year = null)
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $data = [];

        // Loop through each day in the month
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');

            $data[$dateStr] = [
                'shift' => null,
                'tasks' => []
            ];

            // Get shift assignment for this date
            $dayOfWeek = $this->getDayOfWeekString($date);
            $shiftAssignment = ShiftAssignment::where('user_id', $userId)
                ->where('day_of_week', $dayOfWeek)
                ->whereHas('shiftSchedule', function($q) use ($date) {
                    $q->where('start_date', '<=', $date)
                      ->where('end_date', '>=', $date)
                      ->where('status', 'active');
                })
                ->first();

            if ($shiftAssignment) {
                $data[$dateStr]['shift'] = $shiftAssignment->shift_id;
            }

            // Get PM Tasks count
            $pmTasksCount = PmTask::where('assigned_user_id', $userId)
                ->where('task_date', $date)
                ->count();

            for ($i = 0; $i < $pmTasksCount; $i++) {
                $data[$dateStr]['tasks'][] = 'pm';
            }

            // Get CM Tasks count
            $cmTasksCount = CorrectiveMaintenanceRequest::where(function ($q) use ($userId) {
                $q->whereHas('technicians', fn($sub) => $sub->where('user_id', $userId))
                  ->orWhere('assigned_to', $userId);
            })
            ->whereDate('created_at', $date)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

            for ($i = 0; $i < $cmTasksCount; $i++) {
                $data[$dateStr]['tasks'][] = 'cm';
            }

            // Get Stock Opname Tasks count
            $stockTasksCount = StockOpnameUserAssignment::where('user_id', $userId)
                ->whereHas('schedule', function($q) use ($date) {
                    $q->where('execution_date', $date)
                      ->whereIn('status', ['active']);
                })
                ->count();

            for ($i = 0; $i < $stockTasksCount; $i++) {
                $data[$dateStr]['tasks'][] = 'stock';
            }
        }

        return $data;
    }

    /**
     * Get day of week string from Carbon date
     */
    private function getDayOfWeekString($date): string
    {
        $dayMap = [
            Carbon::MONDAY => 'monday',
            Carbon::TUESDAY => 'tuesday',
            Carbon::WEDNESDAY => 'wednesday',
            Carbon::THURSDAY => 'thursday',
            Carbon::FRIDAY => 'friday',
            Carbon::SATURDAY => 'saturday',
            Carbon::SUNDAY => 'sunday',
        ];

        return $dayMap[$date->dayOfWeek] ?? 'monday';
    }

    /**
     * KPI Monitor Data (Admin Only) - AJAX endpoint
     */
    public function kpiData(Request $request)
    {
        $period = $request->get('period', '1M');
        $now = Carbon::now();

        if ($period === 'custom') {
            $dateFrom = Carbon::parse($request->get('date_from'))->startOfDay();
            $dateTo = Carbon::parse($request->get('date_to'))->endOfDay();
        } else {
            $dateTo = $now->copy()->endOfDay();
            $dateFrom = match ($period) {
                '3M' => $now->copy()->subMonths(3)->startOfDay(),
                '6M' => $now->copy()->subMonths(6)->startOfDay(),
                '1Y' => $now->copy()->subYear()->startOfDay(),
                default => $now->copy()->subMonth()->startOfDay(),
            };
        }

        return response()->json([
            'period' => $period,
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'pm' => $this->getPmKpi($dateFrom, $dateTo),
            'cm' => $this->getCmKpi($dateFrom, $dateTo),
            'stock_opname' => $this->getStockOpnameKpi($dateFrom, $dateTo),
        ]);
    }

    private function getPmKpi(Carbon $dateFrom, Carbon $dateTo): array
    {
        $today = Carbon::today();
        $rangeFrom = $dateFrom->toDateString();
        $rangeTo = $dateTo->toDateString();

        // On-time: completed AND completed_at <= end of task_date day
        $onTime = PmTask::whereBetween('task_date', [$rangeFrom, $rangeTo])
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereRaw('completed_at <= DATE_ADD(task_date, INTERVAL 1 DAY)')
            ->count();

        // Late: completed BUT completed_at > end of task_date day
        $late = PmTask::whereBetween('task_date', [$rangeFrom, $rangeTo])
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereRaw('completed_at > DATE_ADD(task_date, INTERVAL 1 DAY)')
            ->count();

        // Not done: task_date has passed AND status is NOT completed
        $notDone = PmTask::whereBetween('task_date', [$rangeFrom, $rangeTo])
            ->where('task_date', '<', $today->toDateString())
            ->where('status', '!=', 'completed')
            ->count();

        $total = PmTask::whereBetween('task_date', [$rangeFrom, $rangeTo])->count();

        return [
            'on_time' => $onTime,
            'late' => $late,
            'not_done' => $notDone,
            'total' => $total,
        ];
    }

    private function getCmKpi(Carbon $dateFrom, Carbon $dateTo): array
    {
        $base = CorrectiveMaintenanceRequest::whereBetween('created_at', [$dateFrom, $dateTo]);

        $open          = (clone $base)->whereIn('status', ['pending', 'received', 'in_progress'])->count();
        $furtherRepair = (clone $base)->where('status', 'further_repair')->count();
        $closed        = (clone $base)->whereIn('status', ['completed', 'done'])->count();
        $cancelled     = (clone $base)->where('status', 'cancelled')->count();
        $total         = (clone $base)->count();

        try {
            $severityCounts = CmReport::whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereNotNull('severity')
                ->selectRaw('severity, COUNT(*) as count')
                ->groupBy('severity')
                ->pluck('count', 'severity');
        } catch (\Exception $e) {
            $severityCounts = collect();
        }

        return [
            'open'          => $open,
            'further_repair'=> $furtherRepair,
            'closed'        => $closed,
            'cancelled'     => $cancelled,
            'total'         => $total,
            'severity'      => [
                'critical' => (int) ($severityCounts['critical'] ?? 0),
                'medium'   => (int) ($severityCounts['medium']   ?? 0),
                'minor'    => (int) ($severityCounts['minor']    ?? 0),
            ],
        ];
    }

    private function getStockOpnameKpi(Carbon $dateFrom, Carbon $dateTo): array
    {
        $today = Carbon::today()->toDateString();
        $rangeFrom = $dateFrom->toDateString();
        $rangeTo = $dateTo->toDateString();

        // Accuracy: completed schedule items with execution_date in range
        $completedItems = StockOpnameScheduleItem::whereHas('schedule', function ($q) use ($rangeFrom, $rangeTo) {
                $q->whereBetween('execution_date', [$rangeFrom, $rangeTo]);
            })
            ->where('execution_status', 'completed')
            ->get(['physical_quantity', 'system_quantity']);

        $totalCompleted = $completedItems->count();
        $accurate = $completedItems->filter(fn($item) => $item->physical_quantity == $item->system_quantity)->count();
        $withDiscrepancy = $totalCompleted - $accurate;
        $accuracyPercent = $totalCompleted > 0 ? round(($accurate / $totalCompleted) * 100, 1) : 0;

        // Missed jobs: schedule items from schedules in range, execution_date passed, NOT completed
        $missedJobs = StockOpnameScheduleItem::whereHas('schedule', function ($q) use ($rangeFrom, $rangeTo, $today) {
                $q->whereBetween('execution_date', [$rangeFrom, $rangeTo])
                  ->where('execution_date', '<', $today);
            })
            ->where('execution_status', '!=', 'completed')
            ->where('is_active', true)
            ->count();

        // Uncovered items: items in master data with NO schedule item ever
        $coveredSparepartIds = StockOpnameScheduleItem::where('item_type', 'sparepart')
            ->distinct()->pluck('item_id')->toArray();
        $uncoveredSpareparts = Sparepart::whereNotIn('id', $coveredSparepartIds)->count();

        $coveredToolIds = StockOpnameScheduleItem::where('item_type', 'tool')
            ->distinct()->pluck('item_id')->toArray();
        $uncoveredTools = Tool::whereNotIn('id', $coveredToolIds)->count();

        $coveredAssetIds = StockOpnameScheduleItem::where('item_type', 'asset')
            ->distinct()->pluck('item_id')->toArray();
        $uncoveredAssets = Asset::whereNotIn('id', $coveredAssetIds)->count();

        return [
            'accuracy' => [
                'percent' => $accuracyPercent,
                'accurate' => $accurate,
                'discrepancy' => $withDiscrepancy,
                'total_completed' => $totalCompleted,
            ],
            'missed_jobs' => $missedJobs,
            'uncovered' => [
                'spareparts' => $uncoveredSpareparts,
                'tools' => $uncoveredTools,
                'assets' => $uncoveredAssets,
                'total' => $uncoveredSpareparts + $uncoveredTools + $uncoveredAssets,
            ],
        ];
    }

    /**
     * MTBF & MTTR metrics — AJAX endpoint
     */
    public function maintenanceMetrics(Request $request)
    {
        $period = $request->get('period', '1M');
        $now    = Carbon::now();

        if ($period === 'custom') {
            $dateFrom = Carbon::parse($request->get('date_from'))->startOfDay();
            $dateTo   = Carbon::parse($request->get('date_to'))->endOfDay();
        } else {
            $dateTo   = $now->copy()->endOfDay();
            $dateFrom = match ($period) {
                '3M'    => $now->copy()->subMonths(3)->startOfDay(),
                '6M'    => $now->copy()->subMonths(6)->startOfDay(),
                '1Y'    => $now->copy()->subYear()->startOfDay(),
                default => $now->copy()->subMonth()->startOfDay(),
            };
        }

        // granularity: daily | weekly | monthly (auto-default based on period if not set)
        $granularity = $request->get('granularity');
        if (!in_array($granularity, ['daily', 'weekly', 'monthly'])) {
            $granularity = in_array($period, ['3M', '6M', '1Y']) ? 'weekly' : 'daily';
        }

        $mttr = $this->getMttrByGroup($dateFrom, $dateTo);
        $mtbf = $this->getMtbfByGroup($dateFrom, $dateTo);

        // Total failures = count of CM reports in period with an asset_id (linked to equipment)
        $totalFailures = DB::connection('site')->table('cm_reports')
            ->whereBetween('submitted_at', [$dateFrom, $dateTo])
            ->count();

        // Availability = (period_hours - total_repair_hours) / period_hours * 100
        $periodHours = $dateFrom->diffInHours($dateTo);
        $totalRepairMinutes = DB::connection('site')->table('corrective_maintenance_requests as req')
            ->join('cm_reports as r', 'r.cm_request_id', '=', 'req.id')
            ->whereBetween('r.submitted_at', [$dateFrom, $dateTo])
            ->whereNotNull('req.in_progress_at')
            ->whereNotNull('req.report_submitted_at')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, req.in_progress_at, req.report_submitted_at) <= 1440')
            ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, req.in_progress_at, req.report_submitted_at)) as total_minutes')
            ->value('total_minutes') ?? 0;
        $totalRepairHours = $totalRepairMinutes / 60;
        $availability = $periodHours > 0
            ? round(max(0, ($periodHours - $totalRepairHours) / $periodHours * 100), 1)
            : 100;

        // MTBF & MTTR trend by granularity
        $trend = $this->getMetricsTrend($dateFrom, $dateTo, $granularity);

        // Failure count by problem_category
        $categoryRows = DB::connection('site')->table('corrective_maintenance_requests')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('problem_category')
            ->selectRaw('problem_category, COUNT(*) as count')
            ->groupBy('problem_category')
            ->orderByDesc('count')
            ->get();

        $byCategory = $categoryRows->map(fn($r) => [
            'category' => $r->problem_category,
            'count'    => (int) $r->count,
        ])->values()->toArray();

        // Downtime timeline: each CM ticket as an event (start=created_at, end=submitted_at)
        // Mapped to fractional hours within a 24h day (time-of-day only, date collapsed)
        $downtimeEvents = DB::connection('site')->table('cm_reports as r')
            ->join('corrective_maintenance_requests as req', 'req.id', '=', 'r.cm_request_id')
            ->join('assets_master as a', 'a.id', '=', 'r.asset_id')
            ->join('group_assets as g', 'g.group_id', '=', 'a.group_id')
            ->whereBetween('r.submitted_at', [$dateFrom, $dateTo])
            ->whereNotNull('r.asset_id')
            ->whereNotNull('req.in_progress_at')
            ->whereNotNull('req.report_submitted_at')
            ->selectRaw('g.group_name, req.in_progress_at as start_at, req.report_submitted_at as end_at, r.id as report_id')
            ->orderBy('g.group_name')
            ->orderBy('req.in_progress_at')
            ->get();

        // Group events by group_name, convert time-of-day to fractional hours (0–24)
        $groupedEvents = [];
        foreach ($downtimeEvents as $ev) {
            $start = Carbon::parse($ev->start_at);
            $end   = Carbon::parse($ev->end_at);
            $startH = $start->hour + $start->minute / 60 + $start->second / 3600;
            $endH   = $end->hour   + $end->minute   / 60 + $end->second   / 3600;
            // If end < start (crosses midnight), cap at 24
            if ($endH < $startH) $endH = 24;
            $endH = min(24, $endH);

            $groupedEvents[$ev->group_name][] = [
                'x'     => [$startH, $endH],
                'label' => $start->format('H:i') . '–' . $end->format('H:i') . ' (' . round($end->diffInMinutes($start)) . 'm)',
            ];
        }

        $downtimeTimeline = [];
        foreach ($groupedEvents as $group => $events) {
            $downtimeTimeline[] = [
                'group'  => $group,
                'events' => $events,
            ];
        }

        return response()->json([
            'period'            => $period,
            'granularity'       => $granularity,
            'date_from'         => $dateFrom->toDateString(),
            'date_to'           => $dateTo->toDateString(),
            'mttr'              => $mttr,
            'mtbf'              => $mtbf,
            'availability'      => $availability,
            'total_failures'    => $totalFailures,
            'trend'             => $trend,
            'by_category'       => $byCategory,
            'downtime_timeline' => $downtimeTimeline,
        ]);
    }

    private function getMetricsTrend(Carbon $dateFrom, Carbon $dateTo, string $granularity): array
    {
        // bucket expressions and operating hours per bucket
        switch ($granularity) {
            case 'weekly':
                $bucketExpr    = "DATE_ADD(DATE(r.submitted_at), INTERVAL(-(WEEKDAY(r.submitted_at))) DAY)";
                $bucketExprAll = "DATE_ADD(DATE(submitted_at), INTERVAL(-(WEEKDAY(submitted_at))) DAY)";
                $bucketHours   = 168; // 7 × 24
                break;
            case 'monthly':
                $bucketExpr    = "DATE_FORMAT(r.submitted_at, '%Y-%m-01')";
                $bucketExprAll = "DATE_FORMAT(submitted_at, '%Y-%m-01')";
                $bucketHours   = 720; // ~30 × 24 (approximate; close enough for trend)
                break;
            default: // daily
                $bucketExpr    = "DATE(r.submitted_at)";
                $bucketExprAll = "DATE(submitted_at)";
                $bucketHours   = 24;
        }

        // MTTR per bucket = avg repair duration for tickets in that bucket (outliers > 24h excluded)
        $mttrRows = DB::connection('site')->table('cm_reports as r')
            ->join('corrective_maintenance_requests as req', 'req.id', '=', 'r.cm_request_id')
            ->whereBetween('r.submitted_at', [$dateFrom, $dateTo])
            ->whereNotNull('req.in_progress_at')
            ->whereNotNull('req.report_submitted_at')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, req.in_progress_at, req.report_submitted_at) <= 1440')
            ->selectRaw("{$bucketExpr} as bucket_date, AVG(TIMESTAMPDIFF(MINUTE, req.in_progress_at, req.report_submitted_at)) as avg_minutes")
            ->groupByRaw($bucketExpr)
            ->orderByRaw($bucketExpr)
            ->get()
            ->keyBy('bucket_date');

        // MTBF per bucket = bucket operating hours / number of failures in that bucket
        $mtbfRows = DB::connection('site')->table('cm_reports')
            ->whereBetween('submitted_at', [$dateFrom, $dateTo])
            ->selectRaw("{$bucketExprAll} as bucket_date, COUNT(*) as failure_count")
            ->groupByRaw($bucketExprAll)
            ->orderByRaw($bucketExprAll)
            ->get()
            ->keyBy('bucket_date');

        $allDates = collect($mttrRows->keys())->merge($mtbfRows->keys())->unique()->sort()->values();

        return $allDates->map(function ($date) use ($mttrRows, $mtbfRows, $bucketHours) {
            $mttrRow = $mttrRows->get($date);
            $mtbfRow = $mtbfRows->get($date);

            $mttr = $mttrRow ? round($mttrRow->avg_minutes / 60, 2) : null;
            $mtbf = ($mtbfRow && $mtbfRow->failure_count > 0)
                ? round($bucketHours / $mtbfRow->failure_count, 1)
                : null;

            return [
                'label' => $date,
                'mttr'  => $mttr,
                'mtbf'  => $mtbf,
            ];
        })->values()->toArray();
    }

    private function getMttrByGroup(Carbon $dateFrom, Carbon $dateTo): array
    {
        // MTTR = work_duration = in_progress_at → report_submitted_at
        // Outliers > 24h (1440 min) are excluded — likely forgotten-to-close tickets
        $rows = DB::connection('site')->table('cm_reports as r')
            ->join('corrective_maintenance_requests as req', 'req.id', '=', 'r.cm_request_id')
            ->join('assets_master as a', 'a.id', '=', 'r.asset_id')
            ->join('group_assets as g', 'g.group_id', '=', 'a.group_id')
            ->whereBetween('r.submitted_at', [$dateFrom, $dateTo])
            ->whereNotNull('r.asset_id')
            ->whereNotNull('req.in_progress_at')
            ->whereNotNull('req.report_submitted_at')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, req.in_progress_at, req.report_submitted_at) <= 1440')
            ->selectRaw('g.group_name, AVG(TIMESTAMPDIFF(MINUTE, req.in_progress_at, req.report_submitted_at)) as avg_minutes, COUNT(*) as ticket_count')
            ->groupBy('g.group_id', 'g.group_name')
            ->orderBy('avg_minutes', 'desc')
            ->get();

        // Overall average
        $overallRow = DB::connection('site')->table('cm_reports as r')
            ->join('corrective_maintenance_requests as req', 'req.id', '=', 'r.cm_request_id')
            ->whereBetween('r.submitted_at', [$dateFrom, $dateTo])
            ->whereNotNull('req.in_progress_at')
            ->whereNotNull('req.report_submitted_at')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, req.in_progress_at, req.report_submitted_at) <= 1440')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, req.in_progress_at, req.report_submitted_at)) as avg_minutes, COUNT(*) as ticket_count')
            ->first();

        return [
            'overall_hours'  => $overallRow->avg_minutes ? round($overallRow->avg_minutes / 60, 1) : 0,
            'overall_count'  => (int) $overallRow->ticket_count,
            'by_group'       => $rows->map(fn($r) => [
                'group'        => $r->group_name,
                'avg_hours'    => $r->avg_minutes ? round($r->avg_minutes / 60, 1) : 0,
                'ticket_count' => (int) $r->ticket_count,
            ])->values()->toArray(),
        ];
    }

    private function getMtbfByGroup(Carbon $dateFrom, Carbon $dateTo): array
    {
        // MTBF = Total Operating Time / Number of Failures
        // Operating time = entire period span in hours
        $periodHours = max(1, $dateFrom->diffInHours($dateTo));

        // Failure count per group
        $rows = DB::connection('site')->table('cm_reports as r')
            ->join('assets_master as a', 'a.id', '=', 'r.asset_id')
            ->join('group_assets as g', 'g.group_id', '=', 'a.group_id')
            ->whereBetween('r.submitted_at', [$dateFrom, $dateTo])
            ->whereNotNull('r.asset_id')
            ->selectRaw('g.group_id, g.group_name, COUNT(*) as failure_count')
            ->groupBy('g.group_id', 'g.group_name')
            ->get();

        // Overall failure count
        $totalFailures = DB::connection('site')->table('cm_reports')
            ->whereBetween('submitted_at', [$dateFrom, $dateTo])
            ->count();

        $overallHours = $totalFailures > 0
            ? round($periodHours / $totalFailures, 1)
            : 0;

        $byGroup = $rows->map(fn($r) => [
            'group'         => $r->group_name,
            'avg_hours'     => $r->failure_count > 0 ? round($periodHours / $r->failure_count, 1) : 0,
            'failure_count' => (int) $r->failure_count,
        ])->values()->toArray();

        usort($byGroup, fn($a, $b) => $a['avg_hours'] <=> $b['avg_hours']);

        return [
            'overall_hours' => $overallHours,
            'by_group'      => $byGroup,
        ];
    }
}
