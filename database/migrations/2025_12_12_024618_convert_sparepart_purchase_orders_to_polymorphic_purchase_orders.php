<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table exists before modifying
        if (!Schema::hasTable('sparepart_purchase_orders')) {
            return;
        }

        // First, drop foreign key from sparepart_purchase_orders
        Schema::table('sparepart_purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['sparepart_id']);
        });

        // Rename table
        Schema::rename('sparepart_purchase_orders', 'purchase_orders');

        // Modify columns to make it polymorphic
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Rename sparepart_id to item_id (will be used polymorphically)
            $table->renameColumn('sparepart_id', 'item_id');

            // Add item_type column for polymorphic relationship
            $table->string('item_type')->after('po_number');
        });

        // Update existing records to set item_type as Sparepart
        DB::table('purchase_orders')->update(['item_type' => 'App\\Models\\Sparepart']);

        // Re-add index on item_id and item_type
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index(['item_id', 'item_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove polymorphic columns
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['item_id', 'item_type']);
            $table->dropColumn('item_type');
            $table->renameColumn('item_id', 'sparepart_id');
        });

        // Rename table back
        Schema::rename('purchase_orders', 'sparepart_purchase_orders');

        // Re-add foreign key
        Schema::table('sparepart_purchase_orders', function (Blueprint $table) {
            $table->foreign('sparepart_id')->references('id')->on('spareparts')->onDelete('cascade');
        });
    }
};
