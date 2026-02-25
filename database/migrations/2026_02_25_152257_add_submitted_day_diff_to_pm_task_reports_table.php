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
        Schema::table('pm_task_reports', function (Blueprint $table) {
            // Difference in days: submitted_at date minus task_date
            // Negative = early, 0 = on time, positive = late
            $table->integer('submitted_day_diff')->nullable()->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('pm_task_reports', function (Blueprint $table) {
            $table->dropColumn('submitted_day_diff');
        });
    }
};
