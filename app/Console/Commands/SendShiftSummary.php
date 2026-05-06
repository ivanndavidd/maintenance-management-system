<?php

namespace App\Console\Commands;

use App\Concerns\IteratesOverSites;
use App\Mail\ShiftSummary;
use App\Models\PmTask;
use App\Models\ShiftAssignment;
use App\Models\ShiftSchedule;
use App\Models\StockOpnameSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendShiftSummary extends Command
{
    use IteratesOverSites;

    protected $signature = 'shift:send-summary {--shift= : Shift number (1, 2, or 3)} {--date= : Override date for testing (Y-m-d)}';

    protected $description = 'Send shift summary email to admins and supervisors before each shift starts';

    // Shift 1 task_date is the NEXT day (overnight shift)
    const SHIFT_INFO = [
        1 => ['label' => 'Shift 1', 'time' => '22:00 - 05:00', 'next_day' => true],
        2 => ['label' => 'Shift 2', 'time' => '06:00 - 13:00', 'next_day' => false],
        3 => ['label' => 'Shift 3', 'time' => '14:00 - 21:00', 'next_day' => false],
    ];

    public function handle()
    {
        $shiftId = (int) $this->option('shift');
        $baseDate = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();

        if (!$shiftId || !isset(self::SHIFT_INFO[$shiftId])) {
            $this->error('Please specify a valid --shift (1, 2, or 3)');
            return 1;
        }

        $this->info("Sending shift {$shiftId} summary emails...");

        $this->forEachSite(function ($site) use ($shiftId, $baseDate) {
            $this->info("  Processing site: {$site->name}");
            $this->sendForSite($shiftId, $baseDate, $site->name);
        });

        $this->info('Done.');
        return 0;
    }

    protected function sendForSite(int $shiftId, Carbon $baseDate, string $siteName): void
    {
        $info      = self::SHIFT_INFO[$shiftId];
        $taskDate  = $info['next_day'] ? $baseDate->copy()->addDay() : $baseDate->copy();
        $dayOfWeek = strtolower($taskDate->englishDayOfWeek);

        // Active shift schedule covering taskDate
        $shiftSchedule = ShiftSchedule::where('start_date', '<=', $taskDate)
            ->where('end_date', '>=', $taskDate)
            ->where('status', 'active')
            ->first();

        // Users on duty for this shift
        $dutyUsers = collect();
        if ($shiftSchedule) {
            $dutyUsers = ShiftAssignment::where('shift_schedule_id', $shiftSchedule->id)
                ->where('day_of_week', $dayOfWeek)
                ->where('shift_id', $shiftId)
                ->whereNull('change_action')
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter(fn($u) => $u !== null)
                ->unique('id')
                ->values();
        }

        // PM tasks for this shift on taskDate
        $pmTasks = PmTask::where('task_date', $taskDate)
            ->where('assigned_shift_id', $shiftId)
            ->whereNotNull('task_date')
            ->with('latestReport')
            ->orderBy('task_name')
            ->get();

        // Stock opname: active (not completed/cancelled) and overdue
        $stockOpnameActive = StockOpnameSchedule::whereNotIn('status', ['completed', 'cancelled'])
            ->with('assignedUsers')
            ->orderBy('execution_date')
            ->get();

        $stockOpnameLate = $stockOpnameActive->filter(fn($s) => $s->isOverdue());
        $stockOpnameOngoing = $stockOpnameActive->filter(fn($s) => !$s->isOverdue());

        // Recipients: all admins + supervisors
        $recipients = User::role(['admin', 'supervisor_maintenance'])
            ->whereNotNull('email')
            ->get();

        if ($recipients->isEmpty()) {
            $this->info('  No recipients found.');
            return;
        }

        $payload = [
            'shiftId'            => $shiftId,
            'shiftLabel'         => $info['label'],
            'shiftTime'          => $info['time'],
            'taskDate'           => $taskDate,
            'siteName'           => $siteName,
            'dutyUsers'          => $dutyUsers,
            'pmTasks'            => $pmTasks,
            'stockOpnameOngoing' => $stockOpnameOngoing,
            'stockOpnameLate'    => $stockOpnameLate,
        ];

        $sent = 0;
        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new ShiftSummary($payload, $recipient));
                $sent++;
            } catch (\Exception $e) {
                $this->error("  Failed to send to {$recipient->email}: {$e->getMessage()}");
            }
        }

        $this->info("  Sent to {$sent} recipient(s).");
    }
}
