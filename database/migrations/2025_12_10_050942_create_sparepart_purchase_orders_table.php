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
        Schema::create('sparepart_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique(); // PO-20251210-001
            $table->unsignedBigInteger('sparepart_id');
            $table->integer('quantity_ordered');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->string('supplier');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->integer('quantity_received')->default(0);
            $table->enum('status', ['pending', 'ordered', 'partial_received', 'received', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('ordered_by');
            $table->unsignedBigInteger('received_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('sparepart_id')->references('id')->on('spareparts')->onDelete('cascade');
            $table->foreign('ordered_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sparepart_purchase_orders');
    }
};
