<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobCompletionLog;
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
        $query = User::role(['user', 'admin'])
            ->with(['jobCompletionLogs' => function ($q) use ($request) {
                if ($request->has('date_from') && $request->date_from != '') {
                    $q->whereDate('completed_at', '>=', $request->date_from);
                }
                if ($request->has('date_to') && $request->date_to != '') {
                    $q->whereDate('completed_at', '<=', $request->date_to);
                }
            }]);

        // Calculate KPI metrics for each user
        $users = $query->get()->map(function ($user) use ($request) {
            // Get all logs for this user with date filters applied
            $logsQuery = $user->jobCompletionLogs();

            // Apply date filters
            if ($request->has('date_from') && $request->date_from != '') {
                $logsQuery->whereDate('completed_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to != '') {
                $logsQuery->whereDate('completed_at', '<=', $request->date_to);
            }

            // Get all logs once
            $logs = $logsQuery->get();

            $totalJobs = $logs->count();
            $lateJobs = $logs->where('completion_status', 'late')->count();
            $onTimeJobs = $logs->where('completion_status', 'on_time')->count();
            $earlyJobs = $logs->where('completion_status', 'early')->count();

            $onTimeRate = $totalJobs > 0 ? round(($onTimeJobs + $earlyJobs) / $totalJobs * 100, 1) : 0;
            $lateRate = $totalJobs > 0 ? round($lateJobs / $totalJobs * 100, 1) : 0;

            $avgDaysLate = $logs->where('completion_status', 'late')->avg('days_late') ?? 0;

            return [
                'user' => $user,
                'total_jobs' => $totalJobs,
                'late_jobs' => $lateJobs,
                'on_time_jobs' => $onTimeJobs,
                'early_jobs' => $earlyJobs,
                'on_time_rate' => $onTimeRate,
                'late_rate' => $lateRate,
                'avg_days_late' => round($avgDaysLate, 1),
            ];
        })->filter(function ($item) {
            return $item['total_jobs'] > 0; // Only show users with completed jobs
        })->sortByDesc('total_jobs');

        return view('admin.kpi.index', compact('users'));
    }

    /**
     * Display detailed KPI for specific user
     */
    public function show(Request $request, User $user)
    {
        $query = JobCompletionLog::where('user_id', $user->id);

        // Filters
        if ($request->has('status') && $request->status != '') {
            $query->where('completion_status', $request->status);
        }

        if ($request->has('job_type') && $request->job_type != '') {
            $query->where('job_type', $request->job_type);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('completed_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('completed_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('completed_at', 'desc')->paginate(15);

        // Summary statistics
        $totalJobs = JobCompletionLog::where('user_id', $user->id)->count();
        $lateJobs = JobCompletionLog::where('user_id', $user->id)
            ->where('completion_status', 'late')
            ->count();
        $onTimeJobs = JobCompletionLog::where('user_id', $user->id)
            ->where('completion_status', 'on_time')
            ->count();
        $earlyJobs = JobCompletionLog::where('user_id', $user->id)
            ->where('completion_status', 'early')
            ->count();

        $onTimeRate = $totalJobs > 0 ? round(($onTimeJobs + $earlyJobs) / $totalJobs * 100, 1) : 0;
        $avgDaysLate = JobCompletionLog::where('user_id', $user->id)
            ->where('completion_status', 'late')
            ->avg('days_late') ?? 0;

        // Monthly trend (last 6 months)
        $monthlyTrend = JobCompletionLog::where('user_id', $user->id)
            ->where('completed_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(completed_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN completion_status = "late" THEN 1 ELSE 0 END) as late_count')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        $statuses = ['on_time', 'late', 'early'];
        $types = ['preventive', 'corrective', 'predictive', 'breakdown'];

        return view('admin.kpi.show', compact(
            'user',
            'logs',
            'totalJobs',
            'lateJobs',
            'onTimeJobs',
            'earlyJobs',
            'onTimeRate',
            'avgDaysLate',
            'monthlyTrend',
            'statuses',
            'types'
        ));
    }
}
