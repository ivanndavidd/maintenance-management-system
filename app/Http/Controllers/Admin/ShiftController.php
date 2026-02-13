<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftSchedule;
use App\Models\ShiftAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShiftController extends Controller
{
    /**
     * Get route prefix based on user role
     */
    private function getRoutePrefix(): string
    {
        return auth()->user()->hasRole('supervisor_maintenance') ? 'supervisor' : 'admin';
    }

    /**
     * Display a listing of shift schedules
     */
    public function index(Request $request)
    {
        $schedules = ShiftSchedule::with('creator')->orderBy('start_date', 'desc')->paginate(10)->appends($request->except('page'));

        return view('admin.shifts.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new shift schedule
     */
    public function create()
    {
        // Get all active users with role 'staff_maintenance'
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['staff_maintenance', 'supervisor_maintenance']);
        })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get all existing shift schedule start dates (Monday dates that are already taken)
        $existingStartDates = ShiftSchedule::pluck('start_date')
            ->map(function ($date) {
                return $date->format('Y-m-d');
            })
            ->toArray();

        return view('admin.shifts.create', compact('users', 'existingStartDates'));
    }

    /**
     * Store a newly created shift schedule
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);

        // Validate that start_date is a Monday
        if ($startDate->dayOfWeek !== \Carbon\Carbon::MONDAY) {
            return back()
                ->withErrors(['start_date' => 'Start date must be a Monday.'])
                ->withInput();
        }

        // Validate that end_date is a Sunday (6 days after start_date)
        if ($endDate->dayOfWeek !== \Carbon\Carbon::SUNDAY) {
            return back()
                ->withErrors([
                    'end_date' =>
                        'End date must be the Sunday of the same week (6 days after start date).',
                ])
                ->withInput();
        }

        // Check if this Monday already has a shift schedule
        $existingSchedule = ShiftSchedule::where('start_date', $request->start_date)->first();
        if ($existingSchedule) {
            return back()
                ->withErrors([
                    'start_date' =>
                        'A shift schedule already exists for this Monday. Please select a different week.',
                ])
                ->withInput();
        }

        // Auto-generate schedule name in format: "Shift (DD - DD, MM, YYYY)"

        $scheduleName = sprintf(
            'Shift (%s - %s, %s, %s)',
            $startDate->format('d'),
            $endDate->format('d'),
            $startDate->format('M'),
            $startDate->format('Y'),
        );

        $schedule = ShiftSchedule::create([
            'name' => $scheduleName,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'draft',
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route($this->getRoutePrefix() . '.shifts.edit', $schedule)
            ->with(
                'success',
                'Shift schedule created successfully. Please assign users to shifts.',
            );
    }

    /**
     * Show the form for editing the shift schedule (Hourly View)
     */
    public function edit(ShiftSchedule $shift)
    {
        // Get all active users with role 'staff_maintenance' or 'supervisor_maintenance'
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['staff_maintenance', 'supervisor_maintenance']);
        })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get hourly assignments grid
        $grid = ShiftAssignment::getScheduleGrid($shift->id);

        // Calculate total hours for each user
        $userHours = [];
        foreach ($users as $user) {
            $userHours[$user->id] = ShiftAssignment::calculateUserHours($shift->id, $user->id);
        }

        return view('admin.shifts.edit-hourly', compact('shift', 'users', 'grid', 'userHours'));
    }

    /**
     * Update the shift schedule
     */
    public function update(Request $request, ShiftSchedule $shift)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $shift->update([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'notes' => $request->notes,
        ]);

        return redirect()
            ->route($this->getRoutePrefix() . '.shifts.edit', $shift)
            ->with('success', 'Shift schedule updated successfully.');
    }

    /**
     * Assign user to a shift
     */
    public function assignUser(Request $request, ShiftSchedule $shift)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'shift_type' => 'required|in:shift_1,shift_2,shift_3',
        ]);

        // Get shift times
        $shiftTimes = ShiftSchedule::getShiftTimes($request->shift_type);

        // Check if assignment already exists
        $existing = ShiftAssignment::where('shift_schedule_id', $shift->id)
            ->where('user_id', $request->user_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('shift_type', $request->shift_type)
            ->first();

        if ($existing) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'User already assigned to this shift.',
                ],
                422,
            );
        }

        // Check if shift already has 4 users
        $count = ShiftAssignment::where('shift_schedule_id', $shift->id)
            ->where('day_of_week', $request->day_of_week)
            ->where('shift_type', $request->shift_type)
            ->count();

        if ($count >= 4) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'This shift already has maximum 4 users assigned.',
                ],
                422,
            );
        }

        $assignment = ShiftAssignment::create([
            'shift_schedule_id' => $shift->id,
            'user_id' => $request->user_id,
            'day_of_week' => $request->day_of_week,
            'shift_type' => $request->shift_type,
            'start_time' => $shiftTimes['start'],
            'end_time' => $shiftTimes['end'],
            'working_hours' => $shiftTimes['hours'],
        ]);

        $assignment->load('user');

        return response()->json([
            'success' => true,
            'message' => 'User assigned to shift successfully.',
            'assignment' => $assignment,
            'total_hours' => $shift->getUserTotalHours($request->user_id),
        ]);
    }

    /**
     * Remove user from a shift
     */
    public function removeUser(Request $request, ShiftSchedule $shift)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'shift_type' => 'required|in:shift_1,shift_2,shift_3',
        ]);

        $assignment = ShiftAssignment::where('shift_schedule_id', $shift->id)
            ->where('user_id', $request->user_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('shift_type', $request->shift_type)
            ->first();

        if (!$assignment) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Assignment not found.',
                ],
                404,
            );
        }

        $userId = $assignment->user_id;
        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'User removed from shift successfully.',
            'total_hours' => $shift->getUserTotalHours($userId),
        ]);
    }

    /**
     * Activate shift schedule
     */
    public function activate(ShiftSchedule $shift)
    {
        // Check if there are assignments
        if ($shift->assignments()->count() === 0) {
            return redirect()
                ->back()
                ->with('error', 'Cannot activate schedule without any assignments.');
        }

        $shift->activate();

        return redirect()
            ->route($this->getRoutePrefix() . '.shifts.index')
            ->with('success', 'Shift schedule activated successfully.');
    }

    /**
     * Delete shift schedule
     */
    public function destroy(ShiftSchedule $shift)
    {
        if ($shift->status === 'active') {
            return redirect()->back()->with('error', 'Cannot delete active shift schedule.');
        }

        $shift->delete();

        return redirect()
            ->route($this->getRoutePrefix() . '.shifts.index')
            ->with('success', 'Shift schedule deleted successfully.');
    }

    /**
     * Get shift details for a specific date (for task assignment)
     */
    public function getShiftForDate(Request $request)
    {
        $date = Carbon::parse($request->date);
        $dayOfWeek = strtolower($date->format('l'));

        // Find active schedule for this date
        $schedule = ShiftSchedule::where('status', 'active')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if (!$schedule) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'No active shift schedule found for this date.',
                ],
                404,
            );
        }

        // Get all shifts for this day
        $shifts = [];
        foreach (['shift_1', 'shift_2', 'shift_3'] as $shiftType) {
            $users = $schedule->getUsersInShift($dayOfWeek, $shiftType);
            $shifts[] = [
                'shift_type' => $shiftType,
                'shift_name' => ShiftSchedule::getShiftTypeName($shiftType),
                'users' => $users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ];
                }),
            ];
        }

        return response()->json([
            'success' => true,
            'schedule' => $schedule,
            'day_of_week' => $dayOfWeek,
            'shifts' => $shifts,
        ]);
    }

    /**
     * Assign user to hourly shift cells (Batch assignment)
     */
    public function assignUserHourly(Request $request, ShiftSchedule $shift)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'assignments' => 'required|array|min:1',
            'assignments.*.day' =>
                'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'assignments.*.hour' => 'required|integer|min:0|max:23',
            'assignments.*.column' => 'required|integer|min:0|max:3',
        ]);

        $user = User::findOrFail($request->user_id);
        $color = ShiftAssignment::generateColorForUser($user->id);

        // First, validate that user doesn't already have assignment in the same shift
        // Check the first assignment to determine which shift and day
        $firstAssignment = $request->assignments[0];
        $firstHour = $firstAssignment['hour'];
        $firstDay = $firstAssignment['day'];
        $checkShiftId = ShiftAssignment::getShiftIdForHour($firstHour);

        // Determine target schedule ID for validation
        $checkScheduleId = $shift->id;
        $checkDutyDay = $firstDay;

        if ($firstDay === 'sunday' && ($firstHour == 22 || $firstHour == 23)) {
            $nextWeekSchedule = ShiftSchedule::where('start_date', '>', $shift->start_date)
                ->orderBy('start_date', 'asc')
                ->first();
            if ($nextWeekSchedule) {
                $checkScheduleId = $nextWeekSchedule->id;
                $checkDutyDay = 'monday';
            }
        }

        // Check if user already has assignment in this shift (any column)
        // Skip this check for Sunday 22-23 since those hours will be merged
        // into the existing Shift 1 assignment (which may already have 00-05 hours)
        $isSundayOvernightHours = $firstDay === 'sunday' && ($firstHour == 22 || $firstHour == 23);

        if (!$isSundayOvernightHours) {
            $existingInShift = ShiftAssignment::where('shift_schedule_id', $checkScheduleId)
                ->where('day_of_week', $checkDutyDay)
                ->where('shift_id', $checkShiftId)
                ->where('user_id', $user->id)
                ->first();

            if ($existingInShift) {
                $shiftNames = [
                    1 => 'Shift 1 (22:00-05:00)',
                    2 => 'Shift 2 (06:00-13:00)',
                    3 => 'Shift 3 (14:00-21:00)',
                ];

                return response()->json(
                    [
                        'success' => false,
                        'error' =>
                            "User {$user->name} is already assigned to {$shiftNames[$checkShiftId]} on " .
                            ucfirst($checkDutyDay) .
                            '. Each user can only be assigned once per shift, regardless of column.',
                    ],
                    422,
                );
            }
        }

        // Group hours by day, column, and shift, collecting selected hours
        $shiftAssignments = [];

        foreach ($request->assignments as $assignment) {
            $shiftId = ShiftAssignment::getShiftIdForHour($assignment['hour']);

            if (!$shiftId) {
                continue; // Skip invalid hours
            }

            // NEW CONCEPT: Use the day AS-IS (no conversion to duty day)
            // All hours in a shift (including 22-23) belong to the SAME day
            // Monday 22:00 -> Monday Shift 1 (not Tuesday)
            // Monday 00:00 -> Monday Shift 1
            $dutyDay = $assignment['day'];

            // Determine which schedule to use
            // Sunday 22-23 belongs to Monday Shift 1 of NEXT week's schedule
            $targetScheduleId = $shift->id;
            if (
                $assignment['day'] === 'sunday' &&
                ($assignment['hour'] == 22 || $assignment['hour'] == 23)
            ) {
                // Find next week's schedule (schedule with start_date after current)
                $nextWeekSchedule = ShiftSchedule::where('start_date', '>', $shift->start_date)
                    ->orderBy('start_date', 'asc')
                    ->first();

                if ($nextWeekSchedule) {
                    $targetScheduleId = $nextWeekSchedule->id;
                    // For next week, Sunday 22-23 becomes Monday (next day)
                    $dutyDay = 'monday';
                    \Log::info('Sunday 22-23 assignment redirected to next week schedule', [
                        'current_schedule' => $shift->id,
                        'next_schedule' => $nextWeekSchedule->id,
                        'hour' => $assignment['hour'],
                        'duty_day' => $dutyDay,
                    ]);
                }
            }

            $key =
                $targetScheduleId . '_' . $dutyDay . '_' . $assignment['column'] . '_' . $shiftId;

            if (!isset($shiftAssignments[$key])) {
                $shiftAssignments[$key] = [
                    'schedule_id' => $targetScheduleId,
                    'day' => $dutyDay,
                    'column' => $assignment['column'],
                    'shift_id' => $shiftId,
                    'selected_hours' => [],
                    'original_day' => $assignment['day'], // Store original calendar day
                ];
            }

            // Add this hour to selected_hours array
            $shiftAssignments[$key]['selected_hours'][] = (int) $assignment['hour'];
        }

        // Sort selected_hours for each assignment
        foreach ($shiftAssignments as &$shiftAssignment) {
            sort($shiftAssignment['selected_hours']);
        }

        $createdAssignments = [];

        // Create shift assignments (one per shift, not per hour)
        foreach ($shiftAssignments as $shiftAssignment) {
            \Log::info('Processing shift assignment:', $shiftAssignment);

            // Use the target schedule_id (always current schedule now)
            $targetScheduleId = $shiftAssignment['schedule_id'];

            // Check if shift+column is already occupied
            // Only check the fields in unique constraint: (shift_schedule_id, day_of_week, shift_id, column_index)
            $existing = ShiftAssignment::where('shift_schedule_id', $targetScheduleId)
                ->where('day_of_week', $shiftAssignment['day'])
                ->where('shift_id', $shiftAssignment['shift_id'])
                ->where('column_index', $shiftAssignment['column'])
                ->first();

            if ($existing) {
                // Check if existing assignment is for the SAME user and SAME original day
                $isSameUser = $existing->user_id == $user->id;
                $isSameOriginalDay =
                    $existing->original_calendar_day == $shiftAssignment['original_day'];

                if ($isSameUser && $isSameOriginalDay) {
                    // Same user, same original day - safe to merge hours
                    $existingHours = $existing->selected_hours ?? [];
                    $newHours = $shiftAssignment['selected_hours'];
                    $mergedHours = array_unique(array_merge($existingHours, $newHours));
                    sort($mergedHours);

                    $existing->update([
                        'selected_hours' => $mergedHours,
                        'color' => $color,
                    ]);

                    \Log::info('Assignment updated (merged hours):', [
                        'id' => $existing->id,
                        'merged_hours' => $mergedHours,
                    ]);

                    $createdAssignments[] = $existing->fresh();
                } else {
                    // Different user or different original day - conflict, skip
                    \Log::warning('Skipped - slot occupied by different user/day:', [
                        'existing_user_id' => $existing->user_id,
                        'new_user_id' => $user->id,
                        'existing_original_day' => $existing->original_calendar_day,
                        'new_original_day' => $shiftAssignment['original_day'],
                    ]);
                }
            } else {
                // Create new assignment
                $created = ShiftAssignment::create([
                    'shift_schedule_id' => $targetScheduleId,
                    'user_id' => $user->id,
                    'day_of_week' => $shiftAssignment['day'],
                    'original_calendar_day' => $shiftAssignment['original_day'],
                    'shift_id' => $shiftAssignment['shift_id'],
                    'selected_hours' => $shiftAssignment['selected_hours'],
                    'column_index' => $shiftAssignment['column'],
                    'color' => $color,
                ]);

                \Log::info('Assignment created:', [
                    'id' => $created->id,
                    'hours' => $created->selected_hours,
                    'duty_day' => $created->day_of_week,
                    'original_calendar_day' => $created->original_calendar_day,
                ]);

                $createdAssignments[] = $created;
            }
        }

        \Log::info('Total assignments created:', ['count' => count($createdAssignments)]);

        return response()->json([
            'success' => true,
            'message' => count($createdAssignments) . ' shift(s) assigned successfully.',
            'assignments' => $createdAssignments,
            'total_hours' => ShiftAssignment::calculateUserHours($shift->id, $user->id),
        ]);
    }

    /**
     * Remove assignment by ID
     */
    public function removeAssignment(Request $request, ShiftSchedule $shift)
    {
        $request->validate([
            'assignment_id' => 'required|exists:shift_assignments,id',
        ]);

        $assignment = ShiftAssignment::findOrFail($request->assignment_id);

        if ($assignment->shift_schedule_id != $shift->id) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Assignment does not belong to this schedule.',
                ],
                403,
            );
        }

        $userId = $assignment->user_id;
        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assignment removed successfully.',
            'total_hours' => ShiftAssignment::calculateUserHours($shift->id, $userId),
        ]);
    }

    /**
     * Remove multiple assignments (bulk remove)
     */
    public function removeAssignments(Request $request, ShiftSchedule $shift)
    {
        $request->validate([
            'removals' => 'required|array',
            'removals.*.assignment_id' => 'required|exists:shift_assignments,id',
            'removals.*.hours' => 'required|array',
            'removals.*.hours.*' => 'integer|min:0|max:23',
        ]);

        $removals = $request->removals;
        $affectedUserIds = [];

        foreach ($removals as $removal) {
            $assignmentId = $removal['assignment_id'];
            $hoursToRemove = $removal['hours'];

            $assignment = ShiftAssignment::where('id', $assignmentId)
                ->where('shift_schedule_id', $shift->id)
                ->first();

            if (!$assignment) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Assignment does not belong to this schedule.',
                    ],
                    403,
                );
            }

            $affectedUserIds[] = $assignment->user_id;

            // Get current selected hours
            $selectedHours = $assignment->selected_hours ?? [];

            // Remove specified hours
            $updatedHours = array_values(array_diff($selectedHours, $hoursToRemove));

            if (empty($updatedHours)) {
                // If no hours left, delete the assignment
                $assignment->delete();
            } else {
                // Update the assignment with remaining hours
                $assignment->selected_hours = $updatedHours;
                $assignment->save();
            }
        }

        // Calculate updated hours for all affected users
        $affectedUserIds = array_unique($affectedUserIds);
        $userHours = [];
        foreach ($affectedUserIds as $userId) {
            $userHours[$userId] = ShiftAssignment::calculateUserHours($shift->id, $userId);
        }

        return response()->json([
            'success' => true,
            'message' => 'Hours removed successfully.',
            'user_hours' => $userHours,
        ]);
    }

    /**
     * Clear all assignments for a schedule
     */
    public function clearAllAssignments(ShiftSchedule $shift)
    {
        ShiftAssignment::where('shift_schedule_id', $shift->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'All assignments cleared successfully.',
        ]);
    }

    /**
     * Get shift details for a specific day
     */
    public function getDayDetails(ShiftSchedule $shift, Request $request)
    {
        $day = $request->day; // 'monday', 'tuesday', etc.
        $date = $request->date; // '22 - Dec - 2025'

        // Parse date
        $dateObj = $this->parseDisplayDate($date);

        $assignments = ShiftAssignment::where('shift_schedule_id', $shift->id)
            ->where('day_of_week', $day)
            ->with([
                'user',
                'changeLogs' => function ($query) use ($dateObj) {
                    $query
                        ->whereDate('effective_date', $dateObj)
                        ->with(['originalUser', 'newUser', 'changedBy'])
                        ->orderBy('created_at', 'desc');
                },
            ])
            ->get();

        $result = [
            'shift1' => null,
            'shift2' => null,
            'shift3' => null,
            'changes' => [],
        ];

        // Get shift assignments
        foreach ([1, 2, 3] as $shiftId) {
            $assignment = $assignments->firstWhere('shift_id', $shiftId);
            if ($assignment) {
                $result['shift' . $shiftId] = [
                    'id' => $assignment->id,
                    'user_name' => $assignment->user->name,
                    'user_id' => $assignment->user_id,
                ];
            }
        }

        // Collect all changes for this day
        foreach ($assignments as $assignment) {
            foreach ($assignment->changeLogs as $log) {
                $result['changes'][] = [
                    'time' => $log->created_at->format('H:i A'),
                    'description' => $log->change_description,
                    'reason' => $log->reason,
                ];
            }
        }

        return response()->json($result);
    }

    /**
     * Change shift assignment (replacement or cancellation)
     */
    public function changeAssignment(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:shift_assignments,id',
            'change_type' => 'required|in:replacement,cancellation',
            'new_user_id' => 'required_if:change_type,replacement|nullable|exists:users,id',
            'reason' => 'required|string',
            'effective_date' => 'required|date',
        ]);

        $assignment = ShiftAssignment::findOrFail($request->assignment_id);
        $originalUserId = $assignment->user_id;

        // Log the change
        \App\Models\ShiftChangeLog::create([
            'shift_assignment_id' => $assignment->id,
            'change_type' => $request->change_type,
            'original_user_id' => $originalUserId,
            'new_user_id' => $request->new_user_id,
            'reason' => $request->reason,
            'effective_date' => $request->effective_date,
            'changed_by' => auth()->id(),
        ]);

        // Update assignment with change tracking
        if ($request->change_type === 'replacement') {
            $assignment->update([
                'change_action' => 'replaced',
                'new_user_id' => $request->new_user_id,
                'change_effective_date' => $request->effective_date,
                'change_reason' => $request->reason,
                'changed_by' => auth()->id(),
            ]);

            \Log::info('Shift assignment replaced:', [
                'assignment_id' => $assignment->id,
                'from_user' => $originalUserId,
                'to_user' => $request->new_user_id,
                'reason' => $request->reason,
            ]);
        } else {
            // Cancellation
            $assignment->update([
                'change_action' => 'cancelled',
                'change_effective_date' => $request->effective_date,
                'change_reason' => $request->reason,
                'changed_by' => auth()->id(),
            ]);

            \Log::info('Shift assignment cancelled:', [
                'assignment_id' => $assignment->id,
                'user_id' => $originalUserId,
                'reason' => $request->reason,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Shift assignment changed successfully.',
            'change_type' => $request->change_type,
            'assignment' => $assignment->fresh()->load('user'),
        ]);
    }

    /**
     * Helper: Parse display date format (e.g., "22 - Dec - 2025")
     */
    private function parseDisplayDate($dateStr)
    {
        $months = [
            'Jan' => 1,
            'Feb' => 2,
            'Mar' => 3,
            'Apr' => 4,
            'May' => 5,
            'Jun' => 6,
            'Jul' => 7,
            'Aug' => 8,
            'Sep' => 9,
            'Oct' => 10,
            'Nov' => 11,
            'Dec' => 12,
        ];

        $parts = explode(' - ', $dateStr);
        $day = (int) $parts[0];
        $month = $months[$parts[1]];
        $year = (int) $parts[2];

        return \Carbon\Carbon::create($year, $month, $day);
    }
}
