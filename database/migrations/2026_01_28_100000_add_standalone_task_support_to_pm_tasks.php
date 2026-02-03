<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pm_tasks', function (Blueprint $table) {
            // Add schedule_date_id for standalone tasks
            $table->foreignId('pm_schedule_date_id')->nullable()->after('pm_spr_group_id')
                ->constrained('pm_schedule_dates')->cascadeOnDelete();

            // Make spr_group_id nullable (was required before)
            $table->foreignId('pm_spr_group_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pm_tasks', function (Blueprint $table) {
            $table->dropForeign(['pm_schedule_date_id']);
            $table->dropColumn('pm_schedule_date_id');
        });
    }
};
