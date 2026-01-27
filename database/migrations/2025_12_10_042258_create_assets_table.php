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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable()->unique();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->date('purchase_date')->nullable();
            $table->string('supplier')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('location')->nullable();
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'broken'])->default('good');
            $table->enum('status', ['active', 'inactive', 'maintenance', 'disposed'])->default('active');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->integer('warranty_months')->default(0);
            $table->date('warranty_expiry')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->decimal('depreciation_rate', 5, 2)->default(0); // percentage
            $table->decimal('current_value', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
