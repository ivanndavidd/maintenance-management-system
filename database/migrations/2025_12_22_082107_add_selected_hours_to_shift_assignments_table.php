<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add selected_hours JSON column to store specific hours assigned
     * while keeping shift_id for categorization
     */
    public function up(): void
    {
        // Check if table exists first
        if (!Schema::hasTable('shift_assignments')) {
            return; // Table doesn't exist yet, skip this migration
        }

        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->json('selected_hours')->nullable()->after('shift_id')
                ->comment('Array of specific hours assigned (e.g., [0,1,2] for partial shift)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->dropColumn('selected_hours');
        });
    }
};
