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
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->unsignedBigInteger('related_opname_item_id')->nullable()->after('related_opname_execution_id');
            $table->foreign('related_opname_item_id')->references('id')->on('stock_opname_schedule_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropForeign(['related_opname_item_id']);
            $table->dropColumn('related_opname_item_id');
        });
    }
};
