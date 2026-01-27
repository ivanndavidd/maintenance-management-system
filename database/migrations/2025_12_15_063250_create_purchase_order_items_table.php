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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');

            // Item can be from master data (polymorphic) or temporary unlisted item
            $table->string('item_type')->nullable(); // Sparepart, Tool, or null for unlisted
            $table->unsignedBigInteger('item_id')->nullable(); // ID from spareparts/tools table

            // For unlisted items (not in master data yet)
            $table->boolean('is_unlisted')->default(false);
            $table->string('unlisted_item_name')->nullable();
            $table->text('unlisted_item_description')->nullable();
            $table->string('unlisted_item_specs')->nullable();

            // Order details
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->string('unit')->default('pcs');

            // Item status
            $table->enum('status', ['pending', 'partial_received', 'received'])->default('pending');
            $table->enum('compliance_status', ['pending', 'compliant', 'non_compliant'])->nullable();
            $table->text('compliance_notes')->nullable();

            // Stock management
            $table->boolean('added_to_stock')->default(false);
            $table->timestamp('stock_added_at')->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
