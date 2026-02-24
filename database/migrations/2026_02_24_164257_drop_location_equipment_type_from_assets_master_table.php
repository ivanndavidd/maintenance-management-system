<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets_master', function (Blueprint $table) {
            $table->dropIndex(['location']);
            $table->dropIndex(['equipment_type']);
            $table->dropColumn(['location', 'equipment_type']);
        });
    }

    public function down(): void
    {
        Schema::table('assets_master', function (Blueprint $table) {
            $table->string('location')->nullable()->after('asset_name');
            $table->string('equipment_type')->nullable()->after('location');
            $table->index('location');
            $table->index('equipment_type');
        });
    }
};
