<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pm_tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_shift_id']);
            $table->dropColumn('assigned_shift_id');
        });

        Schema::table('pm_tasks', function (Blueprint $table) {
            $table->unsignedTinyInteger('assigned_shift_id')->nullable()->after('equipment_type');
        });
    }

    public function down(): void
    {
        Schema::table('pm_tasks', function (Blueprint $table) {
            $table->dropColumn('assigned_shift_id');
        });

        Schema::table('pm_tasks', function (Blueprint $table) {
            $table->foreignId('assigned_shift_id')->nullable()->constrained('shift_schedules')->nullOnDelete();
        });
    }
};
