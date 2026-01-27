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
        Schema::table('stock_opname_schedules', function (Blueprint $table) {
            // Add execution_date column
            $table->date('execution_date')->nullable()->after('schedule_code');

            // Copy data from start_date to execution_date
            // We'll do this in a separate query after adding the column
        });

        // Copy existing start_date to execution_date
        DB::statement('UPDATE stock_opname_schedules SET execution_date = start_date WHERE execution_date IS NULL');

        Schema::table('stock_opname_schedules', function (Blueprint $table) {
            // Make execution_date required after data migration
            $table->date('execution_date')->nullable(false)->change();

            // Drop old columns
            $table->dropColumn(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_opname_schedules', function (Blueprint $table) {
            // Add back start_date and end_date
            $table->date('start_date')->nullable()->after('schedule_code');
            $table->date('end_date')->nullable()->after('start_date');
        });

        // Copy execution_date to start_date and end_date
        DB::statement('UPDATE stock_opname_schedules SET start_date = execution_date, end_date = execution_date WHERE start_date IS NULL');

        Schema::table('stock_opname_schedules', function (Blueprint $table) {
            // Drop execution_date
            $table->dropColumn('execution_date');
        });
    }
};
