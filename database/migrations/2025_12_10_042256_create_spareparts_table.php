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
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();

            // Equipment & Material Info
            $table->string('equipment_type')->nullable();
            $table->string('material_code')->unique();
            $table->string('sparepart_name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();

            // Quantity & Stock
            $table->integer('quantity')->default(0);
            $table->string('unit')->default('pcs'); // pcs, box, meter, etc
            $table->integer('minimum_stock')->default(0);
            $table->string('vulnerability')->nullable(); // tingkat kerentanan

            // Location & Pricing
            $table->string('location')->nullable();
            $table->decimal('parts_price', 15, 2)->default(0);

            // Item Classification
            $table->string('item_type')->nullable(); // jenis item
            $table->string('path')->nullable(); // path/kategori

            // Stock Opname
            $table->integer('physical_quantity')->nullable();
            $table->integer('discrepancy_qty')->default(0); // selisih qty
            $table->decimal('discrepancy_value', 15, 2)->default(0); // nilai selisih
            $table->enum('opname_status', ['pending', 'in_progress', 'completed', 'verified'])->nullable();
            $table->date('opname_date')->nullable();
            $table->unsignedBigInteger('opname_by')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();

            // Adjustment
            $table->integer('adjustment_qty')->default(0);
            $table->text('adjustment_reason')->nullable();
            $table->timestamp('last_opname_at')->nullable();

            // Audit
            $table->unsignedBigInteger('add_part_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('opname_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('add_part_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spareparts');
    }
};
