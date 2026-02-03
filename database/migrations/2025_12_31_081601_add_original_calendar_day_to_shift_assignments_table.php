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
        // Check if table exists first
        if (!Schema::hasTable('shift_assignments')) {
            return; // Table doesn't exist yet, skip this migration
        }

        Schema::table('shift_assignments', function (Blueprint $table) {
            // Store the original calendar day where assignment was created
            // For Sunday 22-23 assigned in Week 1, this will be 'sunday' even though duty_day is 'monday'
            $table->string('original_calendar_day')->nullable()->after('day_of_week')
                ->comment('The calendar day where assignment was originally created (for Sunday 22-23 overnight shifts)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->dropColumn('original_calendar_day');
        });
    }
};
