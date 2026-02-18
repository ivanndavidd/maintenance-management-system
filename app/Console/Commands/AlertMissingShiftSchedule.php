<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Concerns\IteratesOverSites;
use App\Models\PmTask;
use App\Models\ShiftSchedule;
use App\Models\User;
use App\Mail\MissingShiftScheduleAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class AlertMissingShiftSchedule extends Command
{
    use IteratesOverSites;

    protected $signature = 'pm:alert-missing-shift-schedule';

    protected $description = 'Alert supervisors when no shift schedule exists for tomorrow but PM tasks are scheduled';

    public function handle()
    {
        $this->info('Checking for missing shift schedules...');

        $this->forEachSite(function ($site) {
            $this->info("Processing site: {$site->name}...");
            $this->checkSite($site);
        });

        $this->info('Done.');
        return 0;
    }

    protected function checkSite($site): void
    {
        $tomorrow = Carbon::tomorrow();

        // Check if an active shift schedule covers tomorrow
        $hasShiftSchedule = ShiftSchedule::where('start_date', '<=', $tomorrow)
            ->where('end_date', '>=', $tomorrow)
            ->where('status', 'active')
            ->exists();

        if ($hasShiftSchedule) {
            $this->info('  Active shift schedule found for tomorrow. OK.');
            return;
        }

        // Check if there are PM tasks scheduled for tomorrow
        $unassignedTasks = PmTask::where('task_date', $tomorrow)
            ->whereIn('status', [PmTask::STATUS_PENDING, PmTask::STATUS_IN_PROGRESS])
            ->whereNull('assigned_user_id')
            ->get();

        if ($unassignedTasks->isEmpty()) {
            $this->info('  No PM tasks for tomorrow. OK.');
            return;
        }

        $this->warn("  No shift schedule for {$tomorrow->format('d M Y')} but {$unassignedTasks->count()} PM task(s) exist!");

        // Get supervisors and admins to notify
        $recipients = User::where('is_active', true)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['supervisor_maintenance', 'admin']);
            })
            ->whereNotNull('email')
            ->get();

        if ($recipients->isEmpty()) {
            $this->warn('  No supervisors/admins found to notify.');
            return;
        }

        $sentCount = 0;

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)
                    ->send(new MissingShiftScheduleAlert($tomorrow, $unassignedTasks, $site->name));
                $sentCount++;
            } catch (\Exception $e) {
                $this->error("  Failed to send alert to {$recipient->email}: {$e->getMessage()}");
            }
        }

        $this->info("  Sent {$sentCount} alert(s) to supervisors/admins.");
    }
}
