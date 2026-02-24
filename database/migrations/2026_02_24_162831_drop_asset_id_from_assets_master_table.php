<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets_master', function (Blueprint $table) {
            $table->dropUnique(['asset_id']);
            $table->dropColumn('asset_id');
        });
    }

    public function down(): void
    {
        Schema::table('assets_master', function (Blueprint $table) {
            $table->string('asset_id', 20)->unique()->nullable()->after('id');
        });
    }
};
