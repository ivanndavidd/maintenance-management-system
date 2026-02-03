<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table exists first
        if (!Schema::hasTable('shift_assignments')) {
            return; // Table doesn't exist yet, skip this migration
        }

        // First, try to drop the unique constraint if it exists
        try {
            DB::statement('ALTER TABLE shift_assignments DROP INDEX unique_shift_assignment');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }

        // Drop old columns that are no longer needed
        Schema::table('shift_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('shift_assignments', 'shift_type')) {
                $table->dropColumn('shift_type');
            }
            if (Schema::hasColumn('shift_assignments', 'start_time')) {
                $table->dropColumn('start_time');
            }
            if (Schema::hasColumn('shift_assignments', 'end_time')) {
                $table->dropColumn('end_time');
            }
            if (Schema::hasColumn('shift_assignments', 'working_hours')) {
                $table->dropColumn('working_hours');
            }
        });

        // Add new columns for hourly scheduling
        Schema::table('shift_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('shift_assignments', 'hour')) {
                $table->integer('hour')->after('day_of_week'); // 0-23
            }
            if (!Schema::hasColumn('shift_assignments', 'column_index')) {
                $table->integer('column_index')->after('hour')->default(0); // 0-3 for 4 columns
            }
            if (!Schema::hasColumn('shift_assignments', 'color')) {
                $table->string('color')->nullable()->after('user_id'); // Color for visual differentiation
            }
        });

        // Add new unique constraint for hour-based assignments
        try {
            DB::statement('ALTER TABLE shift_assignments ADD UNIQUE unique_hourly_assignment(shift_schedule_id, day_of_week, hour, column_index)');
        } catch (\Exception $e) {
            // Constraint already exists, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the unique constraint
        try {
            DB::statement('ALTER TABLE shift_assignments DROP INDEX unique_hourly_assignment');
        } catch (\Exception $e) {
            // Index doesn't exist
        }

        // Drop hourly columns
        Schema::table('shift_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('shift_assignments', 'hour')) {
                $table->dropColumn('hour');
            }
            if (Schema::hasColumn('shift_assignments', 'column_index')) {
                $table->dropColumn('column_index');
            }
            if (Schema::hasColumn('shift_assignments', 'color')) {
                $table->dropColumn('color');
            }
        });

        // Restore old columns
        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->enum('shift_type', ['shift_1', 'shift_2', 'shift_3'])->after('day_of_week');
            $table->time('start_time')->after('shift_type');
            $table->time('end_time')->after('start_time');
            $table->decimal('working_hours', 4, 2)->after('end_time');
        });

        // Restore old unique constraint
        try {
            DB::statement('ALTER TABLE shift_assignments ADD UNIQUE unique_shift_assignment(shift_schedule_id, user_id, day_of_week, shift_type)');
        } catch (\Exception $e) {
            // Constraint already exists
        }
    }
};
