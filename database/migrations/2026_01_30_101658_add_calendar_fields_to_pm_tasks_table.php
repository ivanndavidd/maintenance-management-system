<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pm_tasks', function (Blueprint $table) {
            // Calendar/Time fields
            $table->time('start_time')->nullable()->after('task_description');
            $table->time('end_time')->nullable()->after('start_time');
            $table->date('task_date')->nullable()->after('end_time'); // Specific date for the task instance

            // Recurring pattern fields
            $table->string('recurrence_pattern')->nullable()->after('frequency'); // daily, weekly, monthly, yearly
            $table->integer('recurrence_interval')->default(1)->after('recurrence_pattern'); // Every X days/weeks/months
            $table->string('recurrence_days')->nullable()->after('recurrence_interval'); // For weekly: Mon,Tue,Wed etc
            $table->integer('recurrence_day_of_month')->nullable()->after('recurrence_days'); // For monthly: day 1-31 or last
            $table->date('recurrence_start_date')->nullable()->after('recurrence_day_of_month');
            $table->date('recurrence_end_date')->nullable()->after('recurrence_start_date');
            $table->boolean('is_recurring')->default(false)->after('recurrence_end_date');

            // Parent task reference for recurring instances
            $table->foreignId('parent_task_id')->nullable()->after('is_recurring')->constrained('pm_tasks')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pm_tasks', function (Blueprint $table) {
            $table->dropForeign(['parent_task_id']);
            $table->dropColumn([
                'start_time',
                'end_time',
                'task_date',
                'recurrence_pattern',
                'recurrence_interval',
                'recurrence_days',
                'recurrence_day_of_month',
                'recurrence_start_date',
                'recurrence_end_date',
                'is_recurring',
                'parent_task_id',
            ]);
        });
    }
};
