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
        // Check if referenced table exists first
        if (!Schema::hasTable('shift_schedules')) {
            return; // Referenced table doesn't exist yet, skip this migration
        }

        // Add shift support to Stock Opname Schedules
        Schema::table('stock_opname_schedules', function (Blueprint $table) {
            $table->foreignId('shift_schedule_id')->nullable()->after('assigned_to')->constrained('shift_schedules')->onDelete('set null');
            $table->enum('shift_type', ['shift_1', 'shift_2', 'shift_3'])->nullable()->after('shift_schedule_id');
            $table->date('shift_date')->nullable()->after('shift_type');
        });

        // Add shift support to Incident Reports
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->foreignId('shift_schedule_id')->nullable()->after('resolved_at')->constrained('shift_schedules')->onDelete('set null');
            $table->enum('shift_type', ['shift_1', 'shift_2', 'shift_3'])->nullable()->after('shift_schedule_id');
            $table->date('shift_date')->nullable()->after('shift_type');
        });

        // Add shift support to Task Requests
        Schema::table('task_requests', function (Blueprint $table) {
            $table->foreignId('shift_schedule_id')->nullable()->after('assigned_to')->constrained('shift_schedules')->onDelete('set null');
            $table->enum('shift_type', ['shift_1', 'shift_2', 'shift_3'])->nullable()->after('shift_schedule_id');
            $table->date('shift_date')->nullable()->after('shift_type');
        });

        // Add shift support to Maintenance Jobs
        Schema::table('maintenance_jobs', function (Blueprint $table) {
            $table->foreignId('shift_schedule_id')->nullable()->after('assigned_to')->constrained('shift_schedules')->onDelete('set null');
            $table->enum('shift_type', ['shift_1', 'shift_2', 'shift_3'])->nullable()->after('shift_schedule_id');
            $table->date('shift_date')->nullable()->after('shift_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_opname_schedules', function (Blueprint $table) {
            $table->dropForeign(['shift_schedule_id']);
            $table->dropColumn(['shift_schedule_id', 'shift_type', 'shift_date']);
        });

        Schema::table('incident_reports', function (Blueprint $table) {
            $table->dropForeign(['shift_schedule_id']);
            $table->dropColumn(['shift_schedule_id', 'shift_type', 'shift_date']);
        });

        Schema::table('task_requests', function (Blueprint $table) {
            $table->dropForeign(['shift_schedule_id']);
            $table->dropColumn(['shift_schedule_id', 'shift_type', 'shift_date']);
        });

        Schema::table('maintenance_jobs', function (Blueprint $table) {
            $table->dropForeign(['shift_schedule_id']);
            $table->dropColumn(['shift_schedule_id', 'shift_type', 'shift_date']);
        });
    }
};
