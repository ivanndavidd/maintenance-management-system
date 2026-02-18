<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule recurring job generation
Schedule::command('jobs:generate-recurring')
    ->daily()
    ->at('00:00')
    ->description('Generate new recurring maintenance jobs');

// PM Task automation
Schedule::command('pm:assign-tasks-to-shifts')
    ->daily()
    ->at('05:00')
    ->description('Auto-assign PM tasks to shift users');

Schedule::command('pm:send-due-reminders --shift=1')
    ->daily()
    ->at('22:00')
    ->description('Send email reminders for Shift 1 PM tasks (22:00-05:00)');

Schedule::command('pm:send-due-reminders --shift=2')
    ->daily()
    ->at('06:00')
    ->description('Send email reminders for Shift 2 PM tasks (06:00-13:00)');

Schedule::command('pm:send-due-reminders --shift=3')
    ->daily()
    ->at('14:00')
    ->description('Send email reminders for Shift 3 PM tasks (14:00-21:00)');

Schedule::command('pm:alert-missing-shift-schedule')
    ->daily()
    ->at('18:00')
    ->description('Alert supervisors about missing shift schedules for tomorrow');
