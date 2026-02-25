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
        Schema::table('assets_master', function (Blueprint $table) {
            $table->string('bom_id', 20)->nullable()->after('equipment_id');
            $table->string('group_id', 10)->nullable()->after('bom_id')->index();

            $table->foreign('group_id')->references('group_id')->on('group_assets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assets_master', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropIndex(['group_id']);
            $table->dropColumn(['bom_id', 'group_id']);
        });
    }
};
