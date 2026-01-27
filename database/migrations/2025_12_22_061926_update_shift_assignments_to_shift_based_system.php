<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Change from hourly (hour: 0-23) to shift-based (shift_id: 1,2,3)
     * - Shift 1: 22:00-05:00
     * - Shift 2: 06:00-13:00
     * - Shift 3: 14:00-21:00
     */
    public function up(): void
    {
        // Drop old unique constraint first
        try {
            DB::statement('ALTER TABLE shift_assignments DROP INDEX unique_hourly_assignment');
        } catch (\Exception $e) {
            // Already dropped
        }

        // Clear existing data to avoid constraint issues
        DB::table('shift_assignments')->truncate();

        // Modify shift_assignments table
        Schema::table('shift_assignments', function (Blueprint $table) {
            // Remove hour column (no longer needed)
            if (Schema::hasColumn('shift_assignments', 'hour')) {
                $table->dropColumn('hour');
            }

            // Add shift_id (1, 2, or 3)
            if (!Schema::hasColumn('shift_assignments', 'shift_id')) {
                $table->integer('shift_id')->after('day_of_week')->comment('1=22:00-05:00, 2=06:00-13:00, 3=14:00-21:00');
            }
        });

        // Add new unique constraint
        // User can appear multiple times (different shifts), but only once per shift per column
        try {
            DB::statement('ALTER TABLE shift_assignments ADD UNIQUE unique_shift_column(shift_schedule_id, day_of_week, shift_id, column_index)');
        } catch (\Exception $e) {
            // Constraint already exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop shift-based constraint
        try {
            DB::statement('ALTER TABLE shift_assignments DROP INDEX unique_shift_column');
        } catch (\Exception $e) {
            // Doesn't exist
        }

        Schema::table('shift_assignments', function (Blueprint $table) {
            // Remove shift_id
            if (Schema::hasColumn('shift_assignments', 'shift_id')) {
                $table->dropColumn('shift_id');
            }

            // Restore hour column
            if (!Schema::hasColumn('shift_assignments', 'hour')) {
                $table->integer('hour')->after('day_of_week');
            }
        });

        // Restore hourly constraint
        try {
            DB::statement('ALTER TABLE shift_assignments ADD UNIQUE unique_hourly_assignment(shift_schedule_id, day_of_week, hour, column_index)');
        } catch (\Exception $e) {
            // Constraint already exists
        }
    }
};
