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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Flag to mark if this is a returned/reordered PO due to non-compliance
            $table->boolean('is_reorder')->default(false)->after('stock_added_at');
            $table->unsignedBigInteger('original_po_id')->nullable()->after('is_reorder');
            $table->text('return_reason')->nullable()->after('original_po_id');
            $table->timestamp('returned_at')->nullable()->after('return_reason');
            $table->unsignedBigInteger('returned_by')->nullable()->after('returned_at');

            // Foreign keys
            $table->foreign('original_po_id')->references('id')->on('purchase_orders')->onDelete('set null');
            $table->foreign('returned_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['original_po_id']);
            $table->dropForeign(['returned_by']);
            $table->dropColumn(['is_reorder', 'original_po_id', 'return_reason', 'returned_at', 'returned_by']);
        });
    }
};
