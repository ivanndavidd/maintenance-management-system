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
