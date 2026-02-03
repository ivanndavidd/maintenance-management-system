<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Structure:
     * - pm_schedules: Main schedule (date-based)
     * - pm_cleaning_groups: Cleaning groups under a schedule
     * - pm_spr_groups: SPR groups under a cleaning group
     * - pm_tasks: Individual tasks under an SPR group
     */
    public function up(): void
    {
        // Check if shift_schedules table exists (for foreign key constraint)
        $hasShiftSchedules = Schema::hasTable('shift_schedules');

        // Main Preventive Maintenance Schedule
        Schema::create('pm_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('scheduled_date');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('scheduled_date');
        });

        // Cleaning Groups (under a schedule)
        Schema::create('pm_cleaning_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_schedule_id')->constrained('pm_schedules')->cascadeOnDelete();
            $table->string('name'); // e.g., "Cleaning Area A", "Cleaning Conveyor"
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // SPR Groups (under a cleaning group)
        Schema::create('pm_spr_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_cleaning_group_id')->constrained('pm_cleaning_groups')->cascadeOnDelete();
            $table->string('name'); // e.g., "SPR-001", "SPR-002"
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Tasks (under an SPR group)
        Schema::create('pm_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_spr_group_id')->constrained('pm_spr_groups')->cascadeOnDelete();
            $table->string('task_name');
            $table->text('task_description')->nullable();
            $table->string('frequency'); // 1_week, 2_weeks, 3_weeks, 1_month, 2_months, etc.
            $table->string('equipment_type')->nullable(); // from spareparts.equipment_type

            // Add shift constraint only if shift_schedules table exists
            if ($hasShiftSchedules) {
                $table->foreignId('assigned_shift_id')->nullable()->constrained('shift_schedules')->nullOnDelete();
            } else {
                $table->unsignedBigInteger('assigned_shift_id')->nullable();
            }

            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('completion_notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('frequency');
            $table->index('status');
        });

        // Task completion history/log
        Schema::create('pm_task_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_task_id')->constrained('pm_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action'); // created, started, completed, skipped, reassigned
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pm_task_logs');
        Schema::dropIfExists('pm_tasks');
        Schema::dropIfExists('pm_spr_groups');
        Schema::dropIfExists('pm_cleaning_groups');
        Schema::dropIfExists('pm_schedules');
    }
};
