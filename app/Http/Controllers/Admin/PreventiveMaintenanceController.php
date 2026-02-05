<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PmSchedule;
use App\Models\PmScheduleDate;
use App\Models\PmCleaningGroup;
use App\Models\PmSprGroup;
use App\Models\PmTask;
use App\Models\ShiftSchedule;
use App\Models\Sparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PreventiveMaintenanceController extends Controller
{
    /**
     * Display a listing of PM schedules - redirect to calendar view
     */
    public function index(Request $request)
    {
        // Redirect to calendar view as default
        return redirect()->route('admin.preventive-maintenance.calendar');
    }

    /**
     * Show the form for creating a new PM schedule.
     */
    public function create()
    {
        $equipmentTypes = Sparepart::distinct()
            ->whereNotNull('equipment_type')
            ->where('equipment_type', '!=', '')
            ->pluck('equipment_type')
            ->sort()
            ->values();

        $shifts = ShiftSchedule::with('assignments.user')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $frequencies = PmTask::getFrequencies();

        return view('admin.preventive-maintenance.create', compact('equipmentTypes', 'shifts', 'frequencies'));
    }

    /**
     * Store a newly created PM schedule (month only, no dates yet).
     */
    public function store(Request $request)
    {
        $request->validate([
            'scheduled_month' => 'required|string',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Parse month input (YYYY-MM) to first day of month
        $monthDate = Carbon::createFromFormat('Y-m', $request->scheduled_month)->startOfMonth();

        $schedule = PmSchedule::create([
            'scheduled_month' => $monthDate,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.preventive-maintenance.show', $schedule)
            ->with('success', 'PM Schedule created. Now add dates and tasks.');
    }

    /**
     * Display the specified PM schedule.
     */
    public function show(PmSchedule $preventive_maintenance)
    {
        $schedule = $preventive_maintenance->load([
            'scheduleDates.cleaningGroups.sprGroups.tasks.assignedUser',
            'scheduleDates.cleaningGroups.sprGroups.tasks.completedByUser',
            'scheduleDates.standaloneTasks.assignedUser',
            'scheduleDates.standaloneTasks.completedByUser',
            'creator'
        ]);

        $equipmentTypes = Sparepart::distinct()
            ->whereNotNull('equipment_type')
            ->where('equipment_type', '!=', '')
            ->pluck('equipment_type')
            ->sort()
            ->values();

        $shifts = ShiftSchedule::with('assignments.user')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $frequencies = PmTask::getFrequencies();

        return view('admin.preventive-maintenance.show', compact('schedule', 'equipmentTypes', 'shifts', 'frequencies'));
    }

    /**
     * Show the form for editing the specified PM schedule.
     */
    public function edit(PmSchedule $preventive_maintenance)
    {
        $schedule = $preventive_maintenance->load([
            'scheduleDates.cleaningGroups.sprGroups.tasks'
        ]);

        $equipmentTypes = Sparepart::distinct()
            ->whereNotNull('equipment_type')
            ->where('equipment_type', '!=', '')
            ->pluck('equipment_type')
            ->sort()
            ->values();

        $shifts = ShiftSchedule::with('assignments.user')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $frequencies = PmTask::getFrequencies();

        return view('admin.preventive-maintenance.edit', compact('schedule', 'equipmentTypes', 'shifts', 'frequencies'));
    }

    /**
     * Update the specified PM schedule.
     */
    public function update(Request $request, PmSchedule $preventive_maintenance)
    {
        $request->validate([
            'scheduled_month' => 'required|string',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,active,completed,cancelled',
        ]);

        $monthDate = Carbon::createFromFormat('Y-m', $request->scheduled_month)->startOfMonth();

        $preventive_maintenance->update([
            'scheduled_month' => $monthDate,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('admin.preventive-maintenance.show', $preventive_maintenance)
            ->with('success', 'Schedule updated successfully.');
    }

    /**
     * Activate a draft PM schedule.
     */
    public function activate(PmSchedule $preventive_maintenance)
    {
        $preventive_maintenance->update(['status' => 'active']);

        return redirect()
            ->route('admin.preventive-maintenance.show', $preventive_maintenance)
            ->with('success', 'Schedule activated successfully.');
    }

    /**
     * Remove the specified PM schedule.
     */
    public function destroy(PmSchedule $preventive_maintenance)
    {
        $preventive_maintenance->delete();

        return redirect()
            ->route('admin.preventive-maintenance.index')
            ->with('success', 'Schedule deleted successfully.');
    }

    /**
     * Add a new date to schedule (AJAX).
     */
    public function addDate(Request $request, PmSchedule $schedule)
    {
        $request->validate([
            'schedule_date' => 'required|date',
        ]);

        // Check if date already exists for this schedule
        $exists = $schedule->scheduleDates()->whereDate('schedule_date', $request->schedule_date)->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This date already exists in this schedule.',
            ], 422);
        }

        $maxOrder = $schedule->scheduleDates()->max('sort_order') ?? 0;

        $scheduleDate = $schedule->scheduleDates()->create([
            'schedule_date' => $request->schedule_date,
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'schedule_date' => $scheduleDate,
        ]);
    }

    /**
     * Delete a schedule date.
     */
    public function deleteDate(PmScheduleDate $scheduleDate)
    {
        $scheduleDate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Date and all its groups/tasks deleted.',
        ]);
    }

    /**
     * Add a new cleaning group to a schedule date (AJAX).
     */
    public function addCleaningGroup(Request $request, PmScheduleDate $scheduleDate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $maxOrder = $scheduleDate->cleaningGroups()->max('sort_order') ?? 0;

        $cleaningGroup = $scheduleDate->cleaningGroups()->create([
            'name' => $request->name,
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'cleaning_group' => $cleaningGroup,
        ]);
    }

    /**
     * Add a new SPR group to existing cleaning group.
     */
    public function addSprGroup(Request $request, PmCleaningGroup $cleaningGroup)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $maxOrder = $cleaningGroup->sprGroups()->max('sort_order') ?? 0;

        $sprGroup = $cleaningGroup->sprGroups()->create([
            'name' => $request->name,
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'spr_group' => $sprGroup,
        ]);
    }

    /**
     * Add a new task to existing SPR group.
     */
    public function addTask(Request $request, PmSprGroup $sprGroup)
    {
        $request->validate([
            'task_name' => 'required|string|max:255',
            'frequency' => 'required|string',
            'equipment_type' => 'nullable|string',
            'assigned_shift_id' => 'nullable|integer|in:1,2,3',
        ]);

        $maxOrder = $sprGroup->tasks()->max('sort_order') ?? 0;

        $task = $sprGroup->tasks()->create([
            'task_name' => $request->task_name,
            'task_description' => $request->task_description,
            'frequency' => $request->frequency,
            'equipment_type' => $request->equipment_type,
            'assigned_shift_id' => $request->assigned_shift_id,
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'task' => $task->load(['assignedUser']),
        ]);
    }

    /**
     * Add a standalone task directly to a schedule date (without cleaning/SPR group).
     */
    public function addStandaloneTask(Request $request, PmScheduleDate $scheduleDate)
    {
        $request->validate([
            'task_name' => 'required|string|max:255',
            'frequency' => 'required|string',
            'equipment_type' => 'nullable|string',
            'assigned_shift_id' => 'nullable|integer|in:1,2,3',
        ]);

        $maxOrder = $scheduleDate->standaloneTasks()->max('sort_order') ?? 0;

        $task = $scheduleDate->standaloneTasks()->create([
            'task_name' => $request->task_name,
            'task_description' => $request->task_description,
            'frequency' => $request->frequency,
            'equipment_type' => $request->equipment_type,
            'assigned_shift_id' => $request->assigned_shift_id,
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'task' => $task->load(['assignedUser']),
        ]);
    }

    /**
     * Update task status.
     */
    public function updateTaskStatus(Request $request, PmTask $task)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,skipped',
            'notes' => 'nullable|string',
        ]);

        if ($request->status === 'completed') {
            $task->markCompleted(auth()->id(), $request->notes);
        } elseif ($request->status === 'in_progress') {
            $task->markInProgress(auth()->id());
        } else {
            $task->update([
                'status' => $request->status,
            ]);

            $task->logs()->create([
                'user_id' => auth()->id(),
                'action' => $request->status,
                'notes' => $request->notes,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task status updated.',
            'task' => $task->fresh(),
        ]);
    }

    /**
     * Update a task's details.
     */
    public function updateTask(Request $request, PmTask $task)
    {
        $request->validate([
            'task_name' => 'required|string|max:255',
            'frequency' => 'required|string',
            'equipment_type' => 'nullable|string',
            'assigned_shift_id' => 'nullable|integer|in:1,2,3',
        ]);

        $task->update([
            'task_name' => $request->task_name,
            'frequency' => $request->frequency,
            'equipment_type' => $request->equipment_type,
            'assigned_shift_id' => $request->assigned_shift_id,
        ]);

        return response()->json([
            'success' => true,
            'task' => $task->fresh(),
        ]);
    }

    /**
     * Delete a cleaning group.
     */
    public function deleteCleaningGroup(PmCleaningGroup $cleaningGroup)
    {
        $cleaningGroup->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cleaning group deleted.',
        ]);
    }

    /**
     * Delete an SPR group.
     */
    public function deleteSprGroup(PmSprGroup $sprGroup)
    {
        $sprGroup->delete();

        return response()->json([
            'success' => true,
            'message' => 'SPR group deleted.',
        ]);
    }

    /**
     * Get available shifts for a specific date (AJAX).
     */
    public function getShiftsForDate(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $date = Carbon::parse($request->date);
        $dayOfWeek = strtolower($date->format('l')); // monday, tuesday, etc.

        // Find active shift schedule that covers this date
        $shiftSchedule = ShiftSchedule::where('status', 'active')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if (!$shiftSchedule) {
            return response()->json([
                'success' => true,
                'shifts' => [],
                'warning' => 'No active shift schedule found for this date. Please create a shift schedule first.',
            ]);
        }

        // Get distinct shift_ids assigned for this day_of_week in this schedule
        $assignedShiftIds = $shiftSchedule->assignments()
            ->where('day_of_week', $dayOfWeek)
            ->whereNull('change_action')
            ->distinct()
            ->pluck('shift_id')
            ->sort()
            ->values();

        $warning = null;
        if ($assignedShiftIds->count() < 3) {
            $missing = 3 - $assignedShiftIds->count();
            $warning = "Shift schedule is incomplete for this date. Only {$assignedShiftIds->count()} of 3 shifts are assigned.";
        }

        $shiftNames = [
            1 => 'Shift 1 (22:00 - 05:00)',
            2 => 'Shift 2 (06:00 - 13:00)',
            3 => 'Shift 3 (14:00 - 21:00)',
        ];

        $shifts = $assignedShiftIds->map(function ($shiftId) use ($shiftNames) {
            return [
                'id' => $shiftId,
                'name' => $shiftNames[$shiftId] ?? "Shift {$shiftId}",
            ];
        });

        return response()->json([
            'success' => true,
            'shifts' => $shifts,
            'warning' => $warning,
        ]);
    }

    /**
     * Delete a task.
     */
    public function deleteTask(PmTask $task)
    {
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted.',
        ]);
    }

    /**
     * Show calendar view
     */
    public function calendar()
    {
        return view('admin.preventive-maintenance.calendar');
    }

    /**
     * Get calendar events (for FullCalendar)
     */
    public function getCalendarEvents(Request $request)
    {
        try {
            $start = $request->input('start');
            $end = $request->input('end');

            // Get all tasks within date range
            $tasks = PmTask::query()
                ->whereNotNull('task_date')
                ->whereBetween('task_date', [$start, $end])
                ->with(['assignedUser', 'completedByUser'])
                ->get();

            // Format for FullCalendar
            $events = $tasks->map(function ($task) {
                $startDateTime = $task->task_date instanceof \Carbon\Carbon
                    ? $task->task_date->format('Y-m-d')
                    : $task->task_date;
                $endDateTime = $task->task_date instanceof \Carbon\Carbon
                    ? $task->task_date->format('Y-m-d')
                    : $task->task_date;

            // Color based on shift
            $className = 'no-shift';
            if ($task->assigned_shift_id) {
                $className = 'shift-' . $task->assigned_shift_id;
            }

            return [
                'id' => $task->id,
                'title' => $task->task_name,
                'start' => $startDateTime,
                'end' => $endDateTime,
                'className' => $className,
                'extendedProps' => [
                    'description' => $task->task_description,
                    'assigned_shift_id' => $task->assigned_shift_id,
                    'assigned_user_id' => $task->assigned_user_id,
                    'equipment_type' => $task->equipment_type,
                    'status' => $task->status,
                    'is_recurring' => $task->is_recurring,
                    'parent_task_id' => $task->parent_task_id,
                    'recurrence_pattern' => $task->recurrence_pattern,
                    'recurrence_interval' => $task->recurrence_interval,
                    'recurrence_days' => $task->recurrence_days,
                    'recurrence_day_of_month' => $task->recurrence_day_of_month,
                    'recurrence_start_date' => $task->recurrence_start_date
                        ? ($task->recurrence_start_date instanceof \Carbon\Carbon
                            ? $task->recurrence_start_date->format('Y-m-d')
                            : $task->recurrence_start_date)
                        : null,
                    'recurrence_end_date' => $task->recurrence_end_date
                        ? ($task->recurrence_end_date instanceof \Carbon\Carbon
                            ? $task->recurrence_end_date->format('Y-m-d')
                            : $task->recurrence_end_date)
                        : null,
                ],
            ];
            });

            return response()->json($events);
        } catch (\Exception $e) {
            \Log::error('Error fetching calendar events: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'error' => 'Error fetching events',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new calendar task
     */
    public function storeCalendarTask(Request $request)
    {
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'task_description' => 'nullable|string',
            'task_date' => 'required|date',
            'assigned_shift_id' => 'required|integer|in:1,2,3',
            'assigned_user_id' => 'nullable|exists:users,id',
            'equipment_type' => 'nullable|string',
            'is_recurring' => 'nullable|boolean',
            'recurrence_pattern' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_days' => 'nullable|string',
            'recurrence_day_of_month' => 'nullable|integer',
            'recurrence_start_date' => 'nullable|date',
            'recurrence_end_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // Create main task
            $task = PmTask::create([
                'task_name' => $validated['task_name'],
                'task_description' => $validated['task_description'] ?? null,
                'task_date' => $validated['task_date'],
                'assigned_shift_id' => $validated['assigned_shift_id'] ?? null,
                'assigned_user_id' => $validated['assigned_user_id'] ?? null,
                'equipment_type' => $validated['equipment_type'] ?? null,
                'is_recurring' => $validated['is_recurring'] ?? false,
                'recurrence_pattern' => $validated['recurrence_pattern'] ?? null,
                'recurrence_interval' => $validated['recurrence_interval'] ?? 1,
                'recurrence_days' => $validated['recurrence_days'] ?? null,
                'recurrence_day_of_month' => $validated['recurrence_day_of_month'] ?? null,
                'recurrence_start_date' => $validated['recurrence_start_date'] ?? null,
                'recurrence_end_date' => $validated['recurrence_end_date'] ?? null,
                'status' => 'pending',
                'frequency' => $validated['recurrence_pattern'] ?? '1_month',
            ]);

            // Generate recurring instances if applicable
            if ($task->is_recurring) {
                $this->generateRecurringTasks($task);
            }

            // Send notification email to assigned user
            if ($task->assigned_user_id) {
                try {
                    $user = User::find($task->assigned_user_id);
                    if ($user && $user->email) {
                        \Mail::to($user->email)->send(new \App\Mail\PmTaskAssigned($task));
                        \Log::info('PM task assignment email sent', [
                            'task_id' => $task->id,
                            'task_name' => $task->task_name,
                            'assigned_user' => $user->name,
                            'user_email' => $user->email,
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send PM task assignment email: ' . $e->getMessage(), [
                        'task_id' => $task->id,
                        'assigned_user_id' => $task->assigned_user_id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully.',
                'task' => $task,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating task: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update calendar task
     */
    public function updateCalendarTask(Request $request, PmTask $task)
    {
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'task_description' => 'nullable|string',
            'task_date' => 'required|date',
            'assigned_shift_id' => 'required|integer|in:1,2,3',
            'assigned_user_id' => 'nullable|exists:users,id',
            'equipment_type' => 'nullable|string',
            'is_recurring' => 'nullable|boolean',
            'recurrence_pattern' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_days' => 'nullable|string',
            'recurrence_day_of_month' => 'nullable|integer',
            'recurrence_start_date' => 'nullable|date',
            'recurrence_end_date' => 'nullable|date',
        ]);

        // Check if task date is being changed
        $currentDate = $task->task_date instanceof \Carbon\Carbon
            ? $task->task_date->format('Y-m-d')
            : $task->task_date;
        $newDate = $validated['task_date'];

        if ($currentDate !== $newDate) {
            return response()->json([
                'success' => false,
                'message' => 'Task date cannot be changed. Please create a new task with the correct date and delete the old one.',
            ], 422);
        }

        $task->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully.',
            'task' => $task,
        ]);
    }

    /**
     * Move task to new date (drag & drop)
     */
    public function moveCalendarTask(Request $request, PmTask $task)
    {
        $validated = $request->validate([
            'task_date' => 'required|date',
        ]);

        $task->update([
            'task_date' => $validated['task_date'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task moved successfully.',
        ]);
    }

    /**
     * Resize task duration (not used anymore - shifts don't have durations)
     */
    public function resizeCalendarTask(Request $request, PmTask $task)
    {
        return response()->json([
            'success' => true,
            'message' => 'Task resize not applicable.',
        ]);
    }

    /**
     * Delete calendar task
     */
    public function deleteCalendarTask(Request $request, PmTask $task)
    {
        $deleteType = $request->input('delete_type', 'this'); // Default to 'this' for single events

        try {
            DB::beginTransaction();

            switch ($deleteType) {
                case 'this':
                    // Delete only this specific event
                    $task->delete();
                    $message = 'Event deleted successfully.';
                    break;

                case 'following':
                    // Delete this event and all following events
                    if ($task->parent_task_id) {
                        // This is a child instance - delete this and all following child instances
                        PmTask::where('parent_task_id', $task->parent_task_id)
                            ->where('task_date', '>=', $task->task_date)
                            ->delete();
                        $message = 'This event and all following events deleted successfully.';
                    } else if ($task->is_recurring) {
                        // This is a parent task - delete all child instances from this date onwards
                        PmTask::where('parent_task_id', $task->id)
                            ->where('task_date', '>=', $task->task_date)
                            ->delete();
                        $task->delete();
                        $message = 'This event and all following events deleted successfully.';
                    } else {
                        // Single event, just delete it
                        $task->delete();
                        $message = 'Event deleted successfully.';
                    }
                    break;

                case 'all':
                    // Delete all events in the series
                    if ($task->parent_task_id) {
                        // This is a child instance - delete parent and all siblings
                        $parentId = $task->parent_task_id;
                        PmTask::where('parent_task_id', $parentId)->delete();
                        PmTask::find($parentId)?->delete();
                        $message = 'All events in the series deleted successfully.';
                    } else if ($task->is_recurring) {
                        // This is a parent task - delete all child instances
                        PmTask::where('parent_task_id', $task->id)->delete();
                        $task->delete();
                        $message = 'All events in the series deleted successfully.';
                    } else {
                        // Single event, just delete it
                        $task->delete();
                        $message = 'Event deleted successfully.';
                    }
                    break;

                default:
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid delete type specified.',
                    ], 400);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting task: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting task: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate recurring task instances
     */
    private function generateRecurringTasks(PmTask $parentTask)
    {
        if (!$parentTask->is_recurring || !$parentTask->recurrence_pattern) {
            return;
        }

        $startDate = Carbon::parse($parentTask->recurrence_start_date ?? $parentTask->task_date);
        $endDate = $parentTask->recurrence_end_date
            ? Carbon::parse($parentTask->recurrence_end_date)
            : $startDate->copy()->addYear(); // Default 1 year if no end date

        // Ensure interval is a valid integer
        $interval = (int) ($parentTask->recurrence_interval ?? 1);
        if ($interval < 1) {
            $interval = 1;
        }

        // Store original day of month for monthly recurrence
        $originalDay = $parentTask->task_date instanceof \Carbon\Carbon
            ? $parentTask->task_date->day
            : Carbon::parse($parentTask->task_date)->day;

        $currentDate = $startDate->copy();
        $instances = [];

        while ($currentDate->lte($endDate) && count($instances) < 100) { // Limit to 100 instances
            // Skip the parent task date (already created)
            if (!$currentDate->isSameDay($parentTask->task_date)) {
                // Check if this date matches the recurrence pattern
                if ($this->shouldCreateInstance($currentDate, $parentTask)) {
                    $instances[] = [
                        'parent_task_id' => $parentTask->id,
                        'task_name' => $parentTask->task_name,
                        'task_description' => $parentTask->task_description,
                        'task_date' => $currentDate->format('Y-m-d'),
                        'assigned_shift_id' => $parentTask->assigned_shift_id,
                        'assigned_user_id' => $parentTask->assigned_user_id,
                        'equipment_type' => $parentTask->equipment_type,
                        'status' => 'pending',
                        'frequency' => $parentTask->frequency,
                        'is_recurring' => false, // Child instances are not recurring
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Move to next occurrence based on pattern
            switch ($parentTask->recurrence_pattern) {
                case 'daily':
                    $currentDate->addDays($interval);
                    break;
                case 'weekly':
                    $currentDate->addWeeks($interval);
                    break;
                case 'monthly':
                    // For monthly recurrence, we need to be careful with day overflow
                    // Save current day before adding months
                    $currentDay = $currentDate->day;

                    // Temporarily set to day 1 to avoid overflow
                    $currentDate->day = 1;

                    // Add months
                    $currentDate->addMonths($interval);

                    // Now set to the original day, or last day of month if original day doesn't exist
                    // For example: Jan 29,30,31 -> Feb 28 (or 29 for leap year), Mar 29,30,31, Apr 29,30
                    $daysInMonth = $currentDate->daysInMonth;
                    if ($originalDay > $daysInMonth) {
                        $currentDate->day = $daysInMonth;
                    } else {
                        $currentDate->day = $originalDay;
                    }
                    break;
                case 'yearly':
                    $currentDate->addYears($interval);
                    break;
            }
        }

        // Bulk insert child instances
        if (!empty($instances)) {
            PmTask::insert($instances);
        }
    }

    /**
     * Check if instance should be created for this date based on recurrence pattern
     */
    private function shouldCreateInstance(Carbon $date, PmTask $parentTask): bool
    {
        // For weekly recurrence, check if day of week matches
        if ($parentTask->recurrence_pattern === 'weekly' && $parentTask->recurrence_days) {
            $daysOfWeek = explode(',', $parentTask->recurrence_days);
            $dayName = $date->format('D'); // Mon, Tue, etc.
            return in_array($dayName, $daysOfWeek);
        }

        // For monthly recurrence, check day of month
        if ($parentTask->recurrence_pattern === 'monthly') {
            // If specific day of month is set, use it
            if ($parentTask->recurrence_day_of_month) {
                if ($parentTask->recurrence_day_of_month == -1) {
                    // Last day of month
                    return $date->day === $date->daysInMonth;
                }
                return $date->day === $parentTask->recurrence_day_of_month;
            }
            // Otherwise, it's handled by the monthly logic in generateRecurringTasks
            // which adjusts for last valid day
            return true;
        }

        return true; // For daily and yearly, always create
    }
}
