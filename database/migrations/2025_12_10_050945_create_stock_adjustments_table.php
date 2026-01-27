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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_code')->unique(); // ADJ-20251210-001
            $table->enum('item_type', ['sparepart', 'tool']); // type of item
            $table->unsignedBigInteger('item_id'); // sparepart_id or tool_id
            $table->integer('quantity_before'); // quantity before adjustment
            $table->integer('quantity_after'); // quantity after adjustment
            $table->integer('adjustment_qty'); // quantity adjusted (can be negative)
            $table->enum('adjustment_type', ['add', 'subtract', 'correction']); // type of adjustment
            $table->enum('reason_category', ['damage', 'loss', 'found', 'correction', 'opname_result', 'other']); // category
            $table->text('reason'); // detailed reason for adjustment
            $table->decimal('value_impact', 15, 2)->default(0); // financial impact of adjustment
            $table->unsignedBigInteger('adjusted_by'); // admin who made adjustment
            $table->unsignedBigInteger('approved_by')->nullable(); // supervisor/admin who approved
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved'); // approval status
            $table->text('approval_notes')->nullable();
            $table->unsignedBigInteger('related_opname_execution_id')->nullable(); // if adjustment from opname
            $table->timestamps();

            $table->foreign('adjusted_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('related_opname_execution_id')->references('id')->on('stock_opname_executions')->onDelete('set null');

            // Indexes
            $table->index(['item_type', 'item_id']);
            $table->index('adjustment_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
