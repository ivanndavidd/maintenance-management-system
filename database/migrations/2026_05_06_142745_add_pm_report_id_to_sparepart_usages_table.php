<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('site')->table('sparepart_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('pm_report_id')->nullable()->after('cm_report_id');
            $table->foreign('pm_report_id')->references('id')->on('pm_task_reports')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::connection('site')->table('sparepart_usages', function (Blueprint $table) {
            $table->dropForeign(['pm_report_id']);
            $table->dropColumn('pm_report_id');
        });
    }
};
