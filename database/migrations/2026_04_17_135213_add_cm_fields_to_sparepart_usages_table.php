<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('site')->table('sparepart_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('cm_report_id')->nullable()->after('id');
            $table->string('ticket_number', 50)->nullable()->after('cm_report_id');
        });
    }

    public function down(): void
    {
        Schema::connection('site')->table('sparepart_usages', function (Blueprint $table) {
            $table->dropColumn(['cm_report_id', 'ticket_number']);
        });
    }
};
