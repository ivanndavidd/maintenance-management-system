<?php

namespace App\Console\Commands;

use App\Concerns\IteratesOverSites;
use App\Mail\WeeklyShiftReminder;
use App\Models\ShiftSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyShiftReminder extends Command
{
    use IteratesOverSites;

    protected $signature = 'shift:send-weekly-reminder {--reminder : Mark as reminder (second send of the day)}';

    protected $description = 'Send email to supervisors if next week shift schedule has not been created';

    public function handle(): int
    {
        $this->info('Checking next week shift schedule...');

        $this->forEachSite(function ($site) {
            $this->info("Processing site: {$site->name}...");
            $this->checkSite($site);
        });

        $this->info('Done.');
        return 0;
    }

    protected function checkSite($site): void
    {
        $isReminder  = (bool) $this->option('reminder');

        // Next week: Monday to Sunday
        $nextWeekStart = Carbon::now()->startOfWeek()->addWeek(); // next Monday
        $nextWeekEnd   = $nextWeekStart->copy()->endOfWeek();     // next Sunday

        // Check if any active shift schedule covers next week
        $hasSchedule = ShiftSchedule::where('status', 'active')
            ->where('start_date', '<=', $nextWeekEnd)
            ->where('end_date', '>=', $nextWeekStart)
            ->exists();

        if ($hasSchedule) {
            $this->info("  Shift schedule for next week already exists. Skipping.");
            return;
        }

        $this->warn("  No shift schedule found for {$nextWeekStart->format('d M')} - {$nextWeekEnd->format('d M Y')}!");

        $supervisors = User::where('is_active', true)
            ->whereHas('roles', fn($q) => $q->where('name', 'supervisor_maintenance'))
            ->whereNotNull('email')
            ->get();

        if ($supervisors->isEmpty()) {
            $this->warn('  No supervisors found to notify.');
            return;
        }

        $sentCount = 0;
        foreach ($supervisors as $supervisor) {
            try {
                Mail::to($supervisor->email)->send(
                    new WeeklyShiftReminder($nextWeekStart, $nextWeekEnd, $site->name, $isReminder)
                );
                $sentCount++;
            } catch (\Exception $e) {
                $this->error("  Failed to send to {$supervisor->email}: {$e->getMessage()}");
            }
        }

        $this->info("  Sent {$sentCount} reminder(s) to supervisors.");
    }
}
