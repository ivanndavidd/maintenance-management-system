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
     * Drop old unique_hourly_assignment constraint if it still exists
     */
    public function up(): void
    {
        // Try to drop the old constraint
        try {
            DB::statement('ALTER TABLE shift_assignments DROP INDEX unique_hourly_assignment');
            echo "✅ Dropped unique_hourly_assignment constraint\n";
        } catch (\Exception $e) {
            echo "ℹ️ Constraint unique_hourly_assignment does not exist or already dropped\n";
        }

        // Also try alternative name
        try {
            DB::statement('ALTER TABLE shift_assignments DROP INDEX shift_assignments_shift_schedule_id_day_of_week_hour_column_index_unique');
            echo "✅ Dropped long-name unique constraint\n";
        } catch (\Exception $e) {
            echo "ℹ️ Long-name constraint does not exist\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse this migration
    }
};
