<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create pm_schedule_dates table (new level between schedule and cleaning groups)
        Schema::create('pm_schedule_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_schedule_id')->constrained('pm_schedules')->cascadeOnDelete();
            $table->date('schedule_date');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('schedule_date');
            $table->unique(['pm_schedule_id', 'schedule_date']);
        });

        // 2. Add pm_schedule_date_id to pm_cleaning_groups
        Schema::table('pm_cleaning_groups', function (Blueprint $table) {
            $table->foreignId('pm_schedule_date_id')->nullable()->after('pm_schedule_id')
                  ->constrained('pm_schedule_dates')->cascadeOnDelete();
        });

        // 3. Migrate existing data: create schedule_dates from existing schedules
        $schedules = DB::table('pm_schedules')->get();
        foreach ($schedules as $schedule) {
            $dateId = DB::table('pm_schedule_dates')->insertGetId([
                'pm_schedule_id' => $schedule->id,
                'schedule_date' => $schedule->scheduled_date,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update cleaning groups to point to the new date record
            DB::table('pm_cleaning_groups')
                ->where('pm_schedule_id', $schedule->id)
                ->update(['pm_schedule_date_id' => $dateId]);
        }

        // 4. Make pm_schedule_date_id required now that data is migrated
        Schema::table('pm_cleaning_groups', function (Blueprint $table) {
            $table->foreignId('pm_schedule_date_id')->nullable(false)->change();
        });

        // 5. Remove pm_schedule_id from pm_cleaning_groups (now goes through dates)
        Schema::table('pm_cleaning_groups', function (Blueprint $table) {
            $table->dropForeign(['pm_schedule_id']);
            $table->dropColumn('pm_schedule_id');
        });

        // 6. Change scheduled_date to scheduled_month (store as first day of month)
        Schema::table('pm_schedules', function (Blueprint $table) {
            $table->renameColumn('scheduled_date', 'scheduled_month');
        });

        // Update existing data to first day of month
        DB::table('pm_schedules')->get()->each(function ($schedule) {
            $date = \Carbon\Carbon::parse($schedule->scheduled_month);
            DB::table('pm_schedules')
                ->where('id', $schedule->id)
                ->update(['scheduled_month' => $date->startOfMonth()->format('Y-m-d')]);
        });
    }

    public function down(): void
    {
        // Rename back
        Schema::table('pm_schedules', function (Blueprint $table) {
            $table->renameColumn('scheduled_month', 'scheduled_date');
        });

        // Re-add pm_schedule_id to cleaning groups
        Schema::table('pm_cleaning_groups', function (Blueprint $table) {
            $table->foreignId('pm_schedule_id')->nullable()->after('id');
        });

        // Migrate data back
        $dates = DB::table('pm_schedule_dates')->get();
        foreach ($dates as $date) {
            DB::table('pm_cleaning_groups')
                ->where('pm_schedule_date_id', $date->id)
                ->update(['pm_schedule_id' => $date->pm_schedule_id]);
        }

        Schema::table('pm_cleaning_groups', function (Blueprint $table) {
            $table->dropForeign(['pm_schedule_date_id']);
            $table->dropColumn('pm_schedule_date_id');
            $table->foreign('pm_schedule_id')->references('id')->on('pm_schedules')->cascadeOnDelete();
        });

        Schema::dropIfExists('pm_schedule_dates');
    }
};
