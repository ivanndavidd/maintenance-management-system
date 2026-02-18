<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
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

        // === MTTR from CMR ===
        $avgResolutionTime = CorrectiveMaintenanceRequest::whereIn('status', ['completed', 'done'])
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, started_at, completed_at)) as avg_hours')
            ->first()->avg_hours ?? 0;

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
            'avgResolutionTime',
            'todayTasks',
            'calendarData'
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
        $openStatuses = ['pending', 'received', 'in_progress', 'further_repair'];
        $closedStatuses = ['completed', 'done'];

        $open = CorrectiveMaintenanceRequest::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', $openStatuses)
            ->count();

        $closed = CorrectiveMaintenanceRequest::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', $closedStatuses)
            ->count();

        $total = CorrectiveMaintenanceRequest::whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        return [
            'open' => $open,
            'closed' => $closed,
            'total' => $total,
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
}
