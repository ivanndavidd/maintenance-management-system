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
     * Fix unique constraint to support hourly assignments.
     * The old constraint (shift_schedule_id, day_of_week, shift_id, column_index)
     * doesn't work with selected_hours because multiple hour assignments
     * within the same shift violate the constraint.
     *
     * We drop the old constraint since selected_hours validation
     * is handled at application level.
     */
    public function up(): void
    {
        // Drop the old unique constraint that conflicts with hourly assignments
        try {
            DB::statement('ALTER TABLE shift_assignments DROP INDEX unique_shift_column');
        } catch (\Exception $e) {
            // Constraint doesn't exist or already dropped
        }

        // Note: We don't add a replacement constraint because:
        // 1. selected_hours allows flexible hour combinations within a shift
        // 2. Validation is handled at application level in ShiftController
        // 3. A user can have multiple entries in same shift/day with different hours
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the old constraint (may fail if data violates it)
        try {
            DB::statement('ALTER TABLE shift_assignments ADD UNIQUE unique_shift_column(shift_schedule_id, day_of_week, shift_id, column_index)');
        } catch (\Exception $e) {
            // Can't restore if data violates constraint
        }
    }
};
