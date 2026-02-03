<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PmSchedule;
use App\Models\PmTask;
use Illuminate\Http\Request;

class PreventiveMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = PmSchedule::with(['scheduleDates.cleaningGroups.sprGroups.tasks', 'creator'])
            ->whereIn('status', ['active', 'completed'])
            ->orderByDesc('scheduled_month');

        if ($request->filled('month')) {
            $query->whereMonth('scheduled_month', date('m', strtotime($request->month)))
                  ->whereYear('scheduled_month', date('Y', strtotime($request->month)));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->paginate(15);

        return view('user.preventive-maintenance.index', compact('schedules'));
    }

    public function show(PmSchedule $schedule)
    {
        $schedule->load([
            'scheduleDates.cleaningGroups.sprGroups.tasks',
            'scheduleDates.standaloneTasks',
            'creator',
        ]);

        return view('user.preventive-maintenance.show', compact('schedule'));
    }

    public function updateTaskStatus(Request $request, PmTask $task)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->update([
            'status' => $request->status,
            'completed_at' => $request->status === 'completed' ? now() : null,
        ]);

        return response()->json(['success' => true]);
    }
}
