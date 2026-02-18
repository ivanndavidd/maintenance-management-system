<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Concerns\IteratesOverSites;
use App\Mail\PmTaskAssigned;
use App\Models\PmTask;
use App\Models\ShiftAssignment;
use App\Models\ShiftSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class AssignPmTasksToShifts extends Command
{
    use IteratesOverSites;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pm:assign-tasks-to-shifts {--force : Force reassign all tasks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-assign PM tasks to users based on existing shift schedules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting PM Task assignment to shift schedules...');

        $force = $this->option('force');

        $this->forEachSite(function ($site) use ($force) {
            $this->info("Processing site: {$site->name}...");
            $this->assignTasksForSite($force);
        });

        return 0;
    }

    protected function assignTasksForSite(bool $force): void
    {
        // Get all PM tasks that need assignment
        $query = PmTask::whereNotNull('task_date')
            ->whereNotNull('assigned_shift_id')
            ->whereIn('status', [PmTask::STATUS_PENDING, PmTask::STATUS_IN_PROGRESS]);

        if (!$force) {
            $query->whereNull('assigned_user_id');
        }

        $tasks = $query->get();

        if ($tasks->isEmpty()) {
            $this->info('  No tasks found that need assignment.');
            return;
        }

        $this->info("  Found {$tasks->count()} tasks to process.");

        $assignedCount = 0;
        $skippedCount = 0;

        foreach ($tasks as $task) {
            // Find the shift schedule that covers this task date
            $shiftSchedule = ShiftSchedule::where('start_date', '<=', $task->task_date)
                ->where('end_date', '>=', $task->task_date)
                ->where('status', 'active')
                ->first();

            if (!$shiftSchedule) {
                $skippedCount++;
                continue;
            }

            // Get the day of week for the task date
            $dayOfWeek = $this->getDayOfWeekString($task->task_date);

            // Find shift assignment for this day and shift
            $shiftAssignment = ShiftAssignment::where('shift_schedule_id', $shiftSchedule->id)
                ->where('day_of_week', $dayOfWeek)
                ->where('shift_id', $task->assigned_shift_id)
                ->first();

            if (!$shiftAssignment) {
                $skippedCount++;
                continue;
            }

            // Assign the task
            $task->update([
                'assigned_user_id' => $shiftAssignment->user_id
            ]);

            // Log the assignment
            $task->logs()->create([
                'user_id' => $shiftAssignment->user_id,
                'action' => 'auto_assigned',
                'notes' => "Auto-assigned to {$shiftAssignment->user->name} via command"
            ]);

            // Send email notification
            try {
                $user = $shiftAssignment->user;
                if ($user && $user->email) {
                    Mail::to($user->email)->send(new PmTaskAssigned($task));
                }
            } catch (\Exception $e) {
                $this->error("  Failed to send email for task #{$task->id}: {$e->getMessage()}");
            }

            $assignedCount++;
        }

        $this->info("  Assigned: {$assignedCount}");
        $this->info("  Skipped: {$skippedCount}");
    }

    /**
     * Get day of week string from Carbon date
     */
    protected function getDayOfWeekString($date): string
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
