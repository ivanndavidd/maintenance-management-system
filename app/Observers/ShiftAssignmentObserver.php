<?php

namespace App\Observers;

use App\Models\ShiftAssignment;
use App\Models\PmTask;
use App\Mail\PmTaskAssigned;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class ShiftAssignmentObserver
{
    /**
     * Handle the ShiftAssignment "created" event.
     * Auto-assign PM Tasks to users when shift assignment is created
     */
    public function created(ShiftAssignment $shiftAssignment): void
    {
        $this->assignPmTasks($shiftAssignment);
    }

    /**
     * Handle the ShiftAssignment "updated" event.
     * Re-assign PM Tasks when shift assignment is updated
     */
    public function updated(ShiftAssignment $shiftAssignment): void
    {
        // Only reassign if user_id changed
        if ($shiftAssignment->isDirty('user_id')) {
            $this->assignPmTasks($shiftAssignment);
        }
    }

    /**
     * Handle the ShiftAssignment "deleted" event.
     * Unassign PM Tasks when shift assignment is deleted
     */
    public function deleted(ShiftAssignment $shiftAssignment): void
    {
        $this->unassignPmTasks($shiftAssignment);
    }

    /**
     * Assign PM Tasks to user based on shift assignment
     *
     * @param ShiftAssignment $shiftAssignment
     */
    protected function assignPmTasks(ShiftAssignment $shiftAssignment): void
    {
        // Get the shift schedule
        $schedule = $shiftAssignment->shiftSchedule;
        if (!$schedule) {
            return;
        }

        // Calculate the actual date for this day_of_week within the schedule
        $taskDate = $this->getDateForDayOfWeek(
            $schedule->start_date,
            $schedule->end_date,
            $shiftAssignment->day_of_week
        );

        if (!$taskDate) {
            return;
        }

        // Find all PM tasks that match this criteria:
        // 1. Task date matches the calculated date
        // 2. Assigned shift matches (shift_id in assignment = assigned_shift_id in task)
        // 3. Task is not yet assigned to a user OR assigned to different user
        PmTask::where('task_date', $taskDate)
            ->where('assigned_shift_id', $shiftAssignment->shift_id)
            ->whereIn('status', [PmTask::STATUS_PENDING, PmTask::STATUS_IN_PROGRESS])
            ->each(function ($task) use ($shiftAssignment) {
                // Update the assigned user
                $task->update([
                    'assigned_user_id' => $shiftAssignment->user_id
                ]);

                // Log the assignment
                $task->logs()->create([
                    'user_id' => auth()->id() ?? $shiftAssignment->user_id,
                    'action' => 'auto_assigned',
                    'notes' => "Auto-assigned to {$shiftAssignment->user->name} via shift schedule"
                ]);

                // Send email notification
                try {
                    $user = $shiftAssignment->user;
                    if ($user && $user->email) {
                        Mail::to($user->email)->send(new PmTaskAssigned($task));
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send PM task assignment email: ' . $e->getMessage(), [
                        'task_id' => $task->id,
                        'assigned_user_id' => $shiftAssignment->user_id,
                    ]);
                }
            });
    }

    /**
     * Unassign PM Tasks when shift assignment is deleted
     *
     * @param ShiftAssignment $shiftAssignment
     */
    protected function unassignPmTasks(ShiftAssignment $shiftAssignment): void
    {
        // Get the shift schedule
        $schedule = $shiftAssignment->shiftSchedule;
        if (!$schedule) {
            return;
        }

        // Calculate the actual date
        $taskDate = $this->getDateForDayOfWeek(
            $schedule->start_date,
            $schedule->end_date,
            $shiftAssignment->day_of_week
        );

        if (!$taskDate) {
            return;
        }

        // Unassign PM tasks that were assigned to this user
        PmTask::where('task_date', $taskDate)
            ->where('assigned_shift_id', $shiftAssignment->shift_id)
            ->where('assigned_user_id', $shiftAssignment->user_id)
            ->whereIn('status', [PmTask::STATUS_PENDING, PmTask::STATUS_IN_PROGRESS])
            ->each(function ($task) {
                $task->update([
                    'assigned_user_id' => null
                ]);

                $task->logs()->create([
                    'user_id' => auth()->id(),
                    'action' => 'auto_unassigned',
                    'notes' => 'Unassigned due to shift schedule deletion'
                ]);
            });
    }

    /**
     * Get the actual date for a specific day of week within a date range
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $dayOfWeek (monday, tuesday, etc.)
     * @return Carbon|null
     */
    protected function getDateForDayOfWeek($startDate, $endDate, $dayOfWeek): ?Carbon
    {
        $dayMap = [
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY,
            'sunday' => Carbon::SUNDAY,
        ];

        $targetDayNumber = $dayMap[$dayOfWeek] ?? null;
        if (!$targetDayNumber) {
            return null;
        }

        // Start from the start_date and find the target day
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            if ($current->dayOfWeek === $targetDayNumber) {
                return $current;
            }
            $current->addDay();
        }

        return null;
    }
}
