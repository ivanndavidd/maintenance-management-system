<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PmTask;
use App\Models\PmTaskReport;
use App\Models\ShiftAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PreventiveMaintenanceController extends Controller
{
    /**
     * Build a query that returns PM tasks visible to the current user
     * based on dynamic shift membership (Opsi 3).
     *
     * A task is visible if:
     *   assigned_shift_id = shift_id of any ShiftAssignment where
     *   user_id = $userId AND day_of_week = day_of_week(task_date) AND
     *   the related ShiftSchedule is active and covers task_date.
     */
    private function userTasksQuery(int $userId)
    {
        return PmTask::whereNotNull('task_date')
            ->whereNotNull('assigned_shift_id')
            ->whereExists(function ($sub) use ($userId) {
                $sub->select(DB::raw(1))
                    ->from('shift_assignments as sa')
                    ->join('shift_schedules as ss', 'ss.id', '=', 'sa.shift_schedule_id')
                    ->whereColumn('sa.shift_id', 'pm_tasks.assigned_shift_id')
                    ->where('sa.user_id', $userId)
                    ->whereNull('sa.change_action')
                    // day_of_week matches the task's day
                    ->whereRaw("sa.day_of_week = LOWER(DAYNAME(pm_tasks.task_date))")
                    // shift schedule covers the task date
                    ->whereColumn('ss.start_date', '<=', 'pm_tasks.task_date')
                    ->whereColumn('ss.end_date', '>=', 'pm_tasks.task_date')
                    ->where('ss.status', 'active');
            });
    }

    public function index(Request $request)
    {
        $query = $this->userTasksQuery(auth()->id())->with('latestReport');

        // Filter by month
        if ($request->filled('month')) {
            $query->whereYear('task_date', date('Y', strtotime($request->month . '-01')))
                  ->whereMonth('task_date', date('m', strtotime($request->month . '-01')));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->orderBy('task_date', 'asc')->get();

        // Group tasks by month (Y-m format) — current month first, then descending
        $currentMonth = \Carbon\Carbon::now()->format('Y-m');
        $tasksByMonth = $tasks->groupBy(function ($task) {
            return \Carbon\Carbon::parse($task->task_date)->format('Y-m');
        })->sortBy(function ($tasks, $month) use ($currentMonth) {
            if ($month === $currentMonth) {
                return '0';
            }
            return '1-' . (9999 - intval(str_replace('-', '', $month)));
        });

        // Calculate stats per month
        $monthlyStats = [];
        foreach ($tasksByMonth as $month => $monthTasks) {
            $total = $monthTasks->count();
            $completed = $monthTasks->where('status', 'completed')->count();
            $inProgress = $monthTasks->where('status', 'in_progress')->count();
            $pending = $monthTasks->where('status', 'pending')->count();
            $monthlyStats[$month] = [
                'total' => $total,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'pending' => $pending,
                'progress' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        }

        // Overall stats
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $inProgressTasks = $tasks->where('status', 'in_progress')->count();
        $pendingTasks = $tasks->where('status', 'pending')->count();

        return view('user.preventive-maintenance.index', compact(
            'tasksByMonth', 'monthlyStats', 'totalTasks', 'completedTasks', 'inProgressTasks', 'pendingTasks'
        ));
    }

    public function show(PmTask $task)
    {
        // Verify user belongs to the task's shift on that date
        abort_unless(
            $this->userTasksQuery(auth()->id())->where('pm_tasks.id', $task->id)->exists(),
            403
        );

        $task->load('latestReport.furtherRepairAssets', 'logs.user');

        return view('user.preventive-maintenance.show', compact('task'));
    }

    public function updateTaskStatus(Request $request, PmTask $task)
    {
        // Verify user belongs to the task's shift on that date
        abort_unless(
            $this->userTasksQuery(auth()->id())->where('pm_tasks.id', $task->id)->exists(),
            403
        );

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->update([
            'status' => $request->status,
            'completed_at' => $request->status === 'completed' ? now() : null,
        ]);

        // Send notification to supervisors and admins if task is completed
        if ($request->status === 'completed') {
            try {
                $supervisors = \App\Models\User::role('supervisor_maintenance')->get();
                $admins = \App\Models\User::role('admin')->get();

                foreach ($supervisors as $supervisor) {
                    \Mail::to($supervisor->email)->send(new \App\Mail\PmTaskCompleted($task));
                }
                foreach ($admins as $admin) {
                    \Mail::to($admin->email)->send(new \App\Mail\PmTaskCompleted($task));
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send PM task completion notification: ' . $e->getMessage(), [
                    'task_id' => $task->id,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Task status updated successfully!']);
    }

    /**
     * Store PM task report
     */
    public function storeReport(Request $request, PmTask $task)
    {
        // Verify user belongs to the task's shift on that date
        if (!$this->userTasksQuery(auth()->id())->where('pm_tasks.id', $task->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'description' => 'required|string',
            'photos.*' => 'nullable|image|max:5120',
            'assets' => 'nullable|array',
            'assets.*.id' => 'required_with:assets|exists:assets_master,id',
            'assets.*.notes' => 'nullable|string',
        ]);

        // Store photos
        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('pm-reports', 'public');
                $photos[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
        }

        // Create report
        $report = PmTaskReport::create([
            'pm_task_id' => $task->id,
            'description' => $request->description,
            'photos' => $photos ?: null,
            'status' => 'submitted',
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        // Attach further repair assets
        if ($request->filled('assets')) {
            foreach ($request->assets as $assetData) {
                $report->furtherRepairAssets()->attach($assetData['id'], [
                    'notes' => $assetData['notes'] ?? null,
                ]);
            }
        }

        // Update task status to completed
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => auth()->id(),
        ]);

        // Log the report submission
        $task->logs()->create([
            'user_id' => auth()->id(),
            'action' => 'report_submitted',
            'notes' => 'Report submitted with ' . count($photos) . ' photo(s)',
        ]);

        return response()->json(['success' => true, 'message' => 'Report submitted successfully!']);
    }

    /**
     * Show PM task report detail
     */
    public function showReport(PmTask $task, PmTaskReport $report)
    {
        $report->load(['submitter', 'reviewer', 'furtherRepairAssets']);

        return response()->json([
            'success' => true,
            'report' => [
                'id' => $report->id,
                'description' => $report->description,
                'photos' => collect($report->photos)->map(function ($photo) {
                    return [
                        'path' => $photo['path'],
                        'url' => Storage::url($photo['path']),
                        'original_name' => $photo['original_name'],
                    ];
                }),
                'status' => $report->status,
                'status_label' => $report->getStatusLabel(),
                'status_badge' => $report->getStatusBadgeClass(),
                'admin_comments' => $report->admin_comments,
                'submitted_by' => $report->submitter->name ?? '-',
                'submitted_at' => $report->submitted_at?->format('d M Y, H:i'),
                'reviewed_by' => $report->reviewer->name ?? null,
                'reviewed_at' => $report->reviewed_at?->format('d M Y, H:i'),
                'further_repair_assets' => $report->furtherRepairAssets->map(function ($asset) {
                    return [
                        'id' => $asset->id,
                        'equipment_id' => $asset->equipment_id ?? '-',
                        'asset_name' => $asset->asset_name,
                        'location' => $asset->location ?? '-',
                        'notes' => $asset->pivot->notes,
                    ];
                }),
                'task' => [
                    'id' => $task->id,
                    'task_name' => $task->task_name,
                    'task_date' => $task->task_date?->format('d M Y'),
                    'shift' => $task->assigned_shift_id,
                ],
            ],
        ]);
    }
}
