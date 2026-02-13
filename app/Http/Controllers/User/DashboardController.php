<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CorrectiveMaintenanceRequest;
use App\Models\PmTask;
use App\Models\StockOpnameUserAssignment;
use App\Models\ShiftAssignment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userId = $user->id;
        $today = Carbon::today();

        // Get today's tasks from all sources
        $todayTasks = $this->getTodayTasks($userId, $today);

        // Get calendar data for the entire year (current year)
        $calendarData = $this->getYearCalendarData($userId);

        $assignedScope = fn($query) => $query->whereHas('technicians', fn($q) => $q->where('user_id', $userId));

        // CMR Statistics
        $tasks = [
            'pending' => $assignedScope(CorrectiveMaintenanceRequest::query())
                ->whereIn('status', ['pending', 'received'])
                ->count(),
            'in_progress' => $assignedScope(CorrectiveMaintenanceRequest::query())
                ->where('status', 'in_progress')
                ->count(),
            'completed_this_month' => $assignedScope(CorrectiveMaintenanceRequest::query())
                ->whereIn('status', ['completed', 'done'])
                ->whereMonth('completed_at', now()->month)
                ->count(),
            'further_repair' => $assignedScope(CorrectiveMaintenanceRequest::query())
                ->where('status', 'further_repair')
                ->count(),
        ];

        // Recent Tasks (Last 5) - Exclude completed/done
        $recentTasks = $assignedScope(CorrectiveMaintenanceRequest::query())
            ->whereNotIn('status', ['completed', 'done'])
            ->latest()
            ->limit(5)
            ->get();

        // Performance Metrics
        $totalCompleted = $assignedScope(CorrectiveMaintenanceRequest::query())
            ->whereIn('status', ['completed', 'done'])
            ->count();

        $totalAssigned = $assignedScope(CorrectiveMaintenanceRequest::query())->count();

        $completionRate = $totalAssigned > 0 ? round(($totalCompleted / $totalAssigned) * 100, 1) : 0;

        return view('user.dashboard', compact(
            'tasks',
            'recentTasks',
            'completionRate',
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
        $cmTasks = CorrectiveMaintenanceRequest::whereHas('technicians', function($q) use ($userId) {
            $q->where('user_id', $userId);
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
                'url' => route('user.corrective-maintenance.show', $task->id)
            ];
        });

        // Preventive Maintenance Tasks
        $pmTasks = PmTask::where('assigned_user_id', $userId)
            ->where('task_date', $today)
            ->whereIn('status', ['pending', 'in_progress'])
            ->get()
            ->map(function($task) {
                return [
                    'type' => 'preventive',
                    'id' => $task->id,
                    'title' => $task->task_name,
                    'status' => $task->status,
                    'shift' => $task->assigned_shift_id,
                    'url' => route('user.preventive-maintenance.show', $task->id)
                ];
            });

        // Stock Opname Tasks
        $stockTasks = StockOpnameUserAssignment::where('user_id', $userId)
            ->whereHas('schedule', function($q) use ($today) {
                $q->where('execution_date', $today)
                  ->whereIn('status', ['scheduled', 'in_progress']);
            })
            ->with('schedule')
            ->get()
            ->map(function($assignment) {
                return [
                    'type' => 'stock_opname',
                    'id' => $assignment->schedule->id,
                    'title' => 'Stock Opname - ' . $assignment->schedule->location,
                    'status' => $assignment->schedule->status,
                    'shift' => $assignment->shift_id ?? null,
                    'url' => route('user.stock-opname.show', $assignment->schedule->id)
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

            // Get CM Tasks count (approximate by created_at date)
            $cmTasksCount = CorrectiveMaintenanceRequest::whereHas('technicians', function($q) use ($userId) {
                $q->where('user_id', $userId);
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
                      ->whereIn('status', ['scheduled', 'in_progress']);
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
}
