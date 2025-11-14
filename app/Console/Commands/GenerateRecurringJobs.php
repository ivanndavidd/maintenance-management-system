<?php

namespace App\Console\Commands;

use App\Models\MaintenanceJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateRecurringJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:generate-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new recurring maintenance jobs based on completed jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting recurring job generation...');

        // Get all completed jobs that are recurring
        $completedRecurringJobs = MaintenanceJob::where('is_recurring', true)
            ->where('status', 'completed')
            ->whereNotNull('scheduled_date')
            ->get();

        $generatedCount = 0;

        foreach ($completedRecurringJobs as $job) {
            // Check if we should generate a new occurrence
            if ($this->shouldGenerateNewOccurrence($job)) {
                $newJob = $this->createNextOccurrence($job);

                if ($newJob) {
                    $generatedCount++;
                    $this->info("Created: {$newJob->job_code} - {$newJob->title}");
                }
            }
        }

        if ($generatedCount > 0) {
            $this->info("Successfully generated {$generatedCount} recurring job(s).");
        } else {
            $this->info('No recurring jobs needed to be generated.');
        }

        return 0;
    }

    /**
     * Determine if a new occurrence should be generated
     */
    private function shouldGenerateNewOccurrence(MaintenanceJob $job): bool
    {
        // Check if there's an end date and we've passed it
        if ($job->recurrence_end_date && Carbon::now()->gt($job->recurrence_end_date)) {
            return false;
        }

        // Calculate next scheduled date
        $nextDate = $this->calculateNextDate($job);

        // Check if next date is today or in the past
        if (!$nextDate || $nextDate->isFuture()) {
            return false;
        }

        // Check if a child job already exists for this occurrence
        $existingJob = MaintenanceJob::where('parent_job_id', $job->id)
            ->where('scheduled_date', $nextDate->format('Y-m-d'))
            ->exists();

        return !$existingJob;
    }

    /**
     * Calculate the next scheduled date based on recurrence settings
     */
    private function calculateNextDate(MaintenanceJob $job): ?Carbon
    {
        if (!$job->scheduled_date || !$job->recurrence_type || !$job->recurrence_interval) {
            return null;
        }

        $lastDate = Carbon::parse($job->scheduled_date);
        $interval = $job->recurrence_interval;

        return match($job->recurrence_type) {
            'daily' => $lastDate->copy()->addDays($interval),
            'weekly' => $lastDate->copy()->addWeeks($interval),
            'monthly' => $lastDate->copy()->addMonths($interval),
            'yearly' => $lastDate->copy()->addYears($interval),
            default => null,
        };
    }

    /**
     * Create the next occurrence of a recurring job
     */
    private function createNextOccurrence(MaintenanceJob $parentJob): ?MaintenanceJob
    {
        $nextDate = $this->calculateNextDate($parentJob);

        if (!$nextDate) {
            return null;
        }

        // Generate new job code
        $jobCode = 'JOB-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        // Create new job based on parent
        $newJob = MaintenanceJob::create([
            'job_code' => $jobCode,
            'title' => $parentJob->title,
            'description' => $parentJob->description,
            'machine_id' => $parentJob->machine_id,
            'assigned_to' => $parentJob->assigned_to,
            'created_by' => $parentJob->created_by,
            'type' => $parentJob->type,
            'priority' => $parentJob->priority,
            'status' => 'pending',
            'scheduled_date' => $nextDate,
            'estimated_duration' => $parentJob->estimated_duration,
            'notes' => $parentJob->notes ? "Auto-generated from recurring job: {$parentJob->job_code}\n\n{$parentJob->notes}" : "Auto-generated from recurring job: {$parentJob->job_code}",
            'is_recurring' => true,
            'recurrence_type' => $parentJob->recurrence_type,
            'recurrence_interval' => $parentJob->recurrence_interval,
            'recurrence_end_date' => $parentJob->recurrence_end_date,
            'parent_job_id' => $parentJob->id,
        ]);

        return $newJob;
    }
}
