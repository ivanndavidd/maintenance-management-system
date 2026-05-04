<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Concerns\IteratesOverSites;
use App\Models\PmTask;
use App\Models\ShiftAssignment;
use App\Models\ShiftSchedule;
use App\Mail\PmTaskDueReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendPmTaskDueReminders extends Command
{
    use IteratesOverSites;

    protected $signature = 'pm:send-due-reminders {--shift= : Only send for specific shift (1, 2, or 3)} {--date= : Override today\'s date for testing (Y-m-d)}';

    protected $description = 'Send email reminders for PM tasks due today based on shift schedule';

    public function handle()
    {
        $shiftId = $this->option('shift');

        if ($shiftId) {
            $this->info("Sending PM task due reminders for Shift {$shiftId}...");
        } else {
            $this->info('Sending PM task due reminders for all shifts...');
        }

        $this->forEachSite(function ($site) use ($shiftId) {
            $this->info("Processing site: {$site->name}...");
            $this->sendRemindersForSite($shiftId ? (int) $shiftId : null);
        });

        $this->info('Done.');
        return 0;
    }

    protected function sendRemindersForSite(?int $shiftId): void
    {
        // Shift 1 (22:00-05:00): task_date is the next day (e.g. Monday),
        // but shift starts at 22:00 the night before (Sunday).
        // So when this runs at 22:00, we look for tomorrow's Shift 1 tasks.
        // Shift 2 & 3 run on the same day as task_date.
        $baseDate = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();
        $targetDate = ($shiftId === 1) ? $baseDate->copy()->addDay() : $baseDate->copy();

        $query = PmTask::where('task_date', $targetDate)
            ->whereIn('status', [PmTask::STATUS_PENDING, PmTask::STATUS_IN_PROGRESS])
            ->whereNull('reminder_sent_at');

        if ($shiftId) {
            $query->where('assigned_shift_id', $shiftId);
        }

        $tasks = $query->get();

        if ($tasks->isEmpty()) {
            $this->info('  No tasks due today that need reminders.');
            return;
        }

        // Find the active shift schedule covering targetDate
        $shiftSchedule = ShiftSchedule::where('start_date', '<=', $targetDate)
            ->where('end_date', '>=', $targetDate)
            ->where('status', 'active')
            ->first();

        $sentCount = 0;

        foreach ($tasks as $task) {
            // Get all users scheduled for this shift on this day
            $dayOfWeek = strtolower($targetDate->englishDayOfWeek);

            $users = collect();
            if ($shiftSchedule) {
                $users = ShiftAssignment::where('shift_schedule_id', $shiftSchedule->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('shift_id', $task->assigned_shift_id)
                    ->whereNull('change_action')
                    ->with('user')
                    ->get()
                    ->pluck('user')
                    ->filter(fn($u) => $u && $u->email);
            }

            // Fallback to assigned_user_id if no shift users found
            if ($users->isEmpty() && $task->assigned_user_id) {
                $users = collect([$task->assignedUser])->filter(fn($u) => $u && $u->email);
            }

            if ($users->isEmpty()) {
                continue;
            }

            try {
                foreach ($users as $user) {
                    Mail::to($user->email)->send(new PmTaskDueReminder($task));
                    $sentCount++;
                }

                $task->update(['reminder_sent_at' => now()]);

                $task->logs()->create([
                    'user_id' => $task->assigned_user_id ?? $users->first()->id,
                    'action' => 'reminder_sent',
                    'notes' => "Due date reminder email sent to {$users->count()} user(s) (Shift {$task->assigned_shift_id})",
                ]);
            } catch (\Exception $e) {
                $this->error("  Failed to send reminder for task #{$task->id}: {$e->getMessage()}");
            }
        }

        $this->info("  Sent {$sentCount} reminder(s).");
    }
}
