<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('site')->table('cm_reports', function (Blueprint $table) {
            $table->string('severity', 20)->nullable()->after('asset_id');
        });
    }

    public function down(): void
    {
        Schema::connection('site')->table('cm_reports', function (Blueprint $table) {
            $table->dropColumn('severity');
        });
    }
};
