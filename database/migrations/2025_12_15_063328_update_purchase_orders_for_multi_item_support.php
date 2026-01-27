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
        // Drop all foreign keys related to columns we're about to drop
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                                   WHERE TABLE_SCHEMA = DATABASE()
                                   AND TABLE_NAME = 'purchase_orders'
                                   AND CONSTRAINT_TYPE = 'FOREIGN KEY'");

        foreach ($foreignKeys as $fk) {
            $fkName = $fk->CONSTRAINT_NAME;
            // Drop FK if it relates to columns we're removing
            if (strpos($fkName, 'checked_by') !== false || strpos($fkName, 'stock_added_by') !== false) {
                Schema::table('purchase_orders', function (Blueprint $table) use ($fkName) {
                    $table->dropForeign($fkName);
                });
            }
        }

        Schema::table('purchase_orders', function (Blueprint $table) {
            // Drop indexes
            if (Schema::hasColumn('purchase_orders', 'item_id')) {
                try {
                    $table->dropIndex(['item_id', 'item_type']);
                } catch (\Exception $e) {
                    // Index might not exist, that's okay
                }
            }

            // Remove single-item fields (now in purchase_order_items)
            $columns = ['item_id', 'item_type', 'quantity_ordered', 'quantity_received', 'unit_price',
                       'compliance_status', 'non_compliance_notes', 'checked_by', 'checked_at',
                       'stock_added', 'stock_added_by', 'stock_added_at'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('purchase_orders', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Add fields for multi-item cart
            if (!Schema::hasColumn('purchase_orders', 'total_items')) {
                $table->integer('total_items')->default(0)->after('po_number');
            }
            if (!Schema::hasColumn('purchase_orders', 'total_quantity')) {
                $table->integer('total_quantity')->default(0)->after('total_items');
            }
            if (!Schema::hasColumn('purchase_orders', 'has_unlisted_items')) {
                $table->boolean('has_unlisted_items')->default(false)->after('total_quantity');
            }

            // total_price stays as grand total for all items
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Restore single-item fields
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_type')->nullable();
            $table->integer('quantity_ordered')->nullable();
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->string('compliance_status')->nullable();
            $table->text('non_compliance_notes')->nullable();
            $table->unsignedBigInteger('checked_by')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->boolean('stock_added')->default(false);
            $table->unsignedBigInteger('stock_added_by')->nullable();
            $table->timestamp('stock_added_at')->nullable();

            // Remove multi-item fields
            $table->dropColumn(['total_items', 'total_quantity', 'has_unlisted_items']);
        });
    }
};
