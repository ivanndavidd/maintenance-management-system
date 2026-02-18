<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Concerns\IteratesOverSites;
use App\Models\PmTask;
use App\Mail\PmTaskDueReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendPmTaskDueReminders extends Command
{
    use IteratesOverSites;

    protected $signature = 'pm:send-due-reminders {--shift= : Only send for specific shift (1, 2, or 3)}';

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
        $targetDate = ($shiftId === 1) ? Carbon::tomorrow() : Carbon::today();

        $query = PmTask::with('assignedUser')
            ->where('task_date', $targetDate)
            ->whereIn('status', [PmTask::STATUS_PENDING, PmTask::STATUS_IN_PROGRESS])
            ->whereNotNull('assigned_user_id')
            ->whereNull('reminder_sent_at');

        if ($shiftId) {
            $query->where('assigned_shift_id', $shiftId);
        }

        $tasks = $query->get();

        if ($tasks->isEmpty()) {
            $this->info('  No tasks due today that need reminders.');
            return;
        }

        $sentCount = 0;

        foreach ($tasks as $task) {
            if (!$task->assignedUser || !$task->assignedUser->email) {
                continue;
            }

            try {
                Mail::to($task->assignedUser->email)
                    ->send(new PmTaskDueReminder($task));

                $task->update(['reminder_sent_at' => now()]);

                $task->logs()->create([
                    'user_id' => $task->assigned_user_id,
                    'action' => 'reminder_sent',
                    'notes' => "Due date reminder email sent (Shift {$task->assigned_shift_id})",
                ]);

                $sentCount++;
            } catch (\Exception $e) {
                $this->error("  Failed to send reminder for task #{$task->id}: {$e->getMessage()}");
            }
        }

        $this->info("  Sent {$sentCount} reminder(s).");
    }
}
