<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PmTask;
use App\Models\CorrectiveMaintenanceRequest;
use App\Models\StockOpnameScheduleItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiController extends Controller
{
    /**
     * Display KPI dashboard
     */
    public function index(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        // Get all maintenance staff users
        $staffUsers = User::role(['staff_maintenance', 'admin', 'supervisor_maintenance'])->get();

        $users = $staffUsers->map(function ($user) use ($dateFrom, $dateTo) {
            // PM Tasks completed by this user
            $pmQuery = PmTask::where('completed_by', $user->id)
                ->where('status', 'completed');
            if ($dateFrom) $pmQuery->whereDate('completed_at', '>=', $dateFrom);
            if ($dateTo) $pmQuery->whereDate('completed_at', '<=', $dateTo);
            $pmCount = $pmQuery->count();

            // PM Tasks assigned to this user (for completion rate)
            $pmAssignedQuery = PmTask::where('assigned_user_id', $user->id);
            if ($dateFrom) $pmAssignedQuery->whereDate('task_date', '>=', $dateFrom);
            if ($dateTo) $pmAssignedQuery->whereDate('task_date', '<=', $dateTo);
            $pmAssigned = $pmAssignedQuery->count();

            // CM Tickets completed by this user (via technicians pivot)
            $cmQuery = CorrectiveMaintenanceRequest::whereHas('technicians', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->where('status', 'completed');
            if ($dateFrom) $cmQuery->whereDate('completed_at', '>=', $dateFrom);
            if ($dateTo) $cmQuery->whereDate('completed_at', '<=', $dateTo);
            $cmCount = $cmQuery->count();

            // CM Tickets assigned to this user (all statuses)
            $cmAssignedQuery = CorrectiveMaintenanceRequest::whereHas('technicians', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
            if ($dateFrom) $cmAssignedQuery->whereDate('created_at', '>=', $dateFrom);
            if ($dateTo) $cmAssignedQuery->whereDate('created_at', '<=', $dateTo);
            $cmAssigned = $cmAssignedQuery->count();

            // Stock Opname items executed by this user
            $soQuery = StockOpnameScheduleItem::where('executed_by', $user->id)
                ->where('execution_status', 'completed');
            if ($dateFrom) $soQuery->whereDate('executed_at', '>=', $dateFrom);
            if ($dateTo) $soQuery->whereDate('executed_at', '<=', $dateTo);
            $soCount = $soQuery->count();

            // SO assigned (all statuses for this user)
            $soAssignedQuery = StockOpnameScheduleItem::where('executed_by', $user->id);
            if ($dateFrom) $soAssignedQuery->whereDate('executed_at', '>=', $dateFrom);
            if ($dateTo) $soAssignedQuery->whereDate('executed_at', '<=', $dateTo);
            $soAssigned = $soAssignedQuery->count();

            $totalCompleted = $pmCount + $cmCount + $soCount;
            $totalAssigned = $pmAssigned + $cmAssigned + $soAssigned;
            $completionRate = $totalAssigned > 0 ? round(($totalCompleted / $totalAssigned) * 100, 1) : 0;

            return [
                'user' => $user,
                'pm_count' => $pmCount,
                'cm_count' => $cmCount,
                'so_count' => $soCount,
                'total_completed' => $totalCompleted,
                'total_assigned' => $totalAssigned,
                'completion_rate' => $completionRate,
            ];
        })->filter(function ($item) {
            return $item['total_completed'] > 0 || $item['total_assigned'] > 0;
        })->sortByDesc('total_completed');

        // Global summary
        $totalPm = $users->sum('pm_count');
        $totalCm = $users->sum('cm_count');
        $totalSo = $users->sum('so_count');
        $totalAll = $users->sum('total_completed');

        return view('admin.kpi.index', compact('users', 'totalPm', 'totalCm', 'totalSo', 'totalAll'));
    }

    /**
     * Display detailed KPI for specific user
     */
    public function show(Request $request, User $user)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $filterType = $request->type; // pm, cm, so

        // PM Tasks
        $pmQuery = PmTask::where('completed_by', $user->id)->where('status', 'completed');
        if ($dateFrom) $pmQuery->whereDate('completed_at', '>=', $dateFrom);
        if ($dateTo) $pmQuery->whereDate('completed_at', '<=', $dateTo);
        $pmTasks = $pmQuery->orderBy('completed_at', 'desc')->get();

        // CM Tickets
        $cmQuery = CorrectiveMaintenanceRequest::whereHas('technicians', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', 'completed');
        if ($dateFrom) $cmQuery->whereDate('completed_at', '>=', $dateFrom);
        if ($dateTo) $cmQuery->whereDate('completed_at', '<=', $dateTo);
        $cmTickets = $cmQuery->orderBy('completed_at', 'desc')->get();

        // Stock Opname Items
        $soQuery = StockOpnameScheduleItem::where('executed_by', $user->id)
            ->where('execution_status', 'completed');
        if ($dateFrom) $soQuery->whereDate('executed_at', '>=', $dateFrom);
        if ($dateTo) $soQuery->whereDate('executed_at', '<=', $dateTo);
        $soItems = $soQuery->orderBy('executed_at', 'desc')->get();

        // Summary counts
        $pmCount = $pmTasks->count();
        $cmCount = $cmTickets->count();
        $soCount = $soItems->count();
        $totalCompleted = $pmCount + $cmCount + $soCount;

        // Monthly trend (last 6 months) - PM
        $pmTrend = PmTask::where('completed_by', $user->id)
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(completed_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->pluck('count', 'month');

        // Monthly trend - CM
        $cmTrend = CorrectiveMaintenanceRequest::whereHas('technicians', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(completed_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->pluck('count', 'month');

        // Monthly trend - SO
        $soTrend = StockOpnameScheduleItem::where('executed_by', $user->id)
            ->where('execution_status', 'completed')
            ->where('executed_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(executed_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->pluck('count', 'month');

        // Merge trends into unified monthly data
        $allMonths = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $allMonths->put($month, [
                'month' => $month,
                'pm' => $pmTrend->get($month, 0),
                'cm' => $cmTrend->get($month, 0),
                'so' => $soTrend->get($month, 0),
                'total' => ($pmTrend->get($month, 0) + $cmTrend->get($month, 0) + $soTrend->get($month, 0)),
            ]);
        }
        $monthlyTrend = $allMonths->values();

        return view('admin.kpi.show', compact(
            'user',
            'pmTasks',
            'cmTickets',
            'soItems',
            'pmCount',
            'cmCount',
            'soCount',
            'totalCompleted',
            'monthlyTrend'
        ));
    }
}
