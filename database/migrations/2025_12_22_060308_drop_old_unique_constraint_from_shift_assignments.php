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
        // Drop the old unique constraint that conflicts with hourly system
        try {
            DB::statement('ALTER TABLE shift_assignments DROP INDEX unique_shift_assignment');
        } catch (\Exception $e) {
            // Constraint doesn't exist or already dropped
            echo "Note: unique_shift_assignment constraint not found or already dropped\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to restore this old constraint as it conflicts with the hourly system
        // Leaving empty intentionally
    }
};
