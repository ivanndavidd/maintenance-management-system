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
        // Drop foreign key constraints from tables referencing 'parts'
        Schema::disableForeignKeyConstraints();

        // Drop all tables that might reference parts
        Schema::dropIfExists('report_parts');
        // NOTE: inventory_requests still used - don't drop
        // Schema::dropIfExists('inventory_requests');

        // Drop the main unused tables
        Schema::dropIfExists('parts');
        // NOTE: machines and machine_category still used - don't drop
        // Schema::dropIfExists('machines');
        // Schema::dropIfExists('machine_category');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore tables if needed (structure only, data will be lost)
        Schema::create('machine_category', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('machine_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->foreign('category_id')
                ->references('id')
                ->on('machine_category')
                ->onDelete('set null');
        });

        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->timestamps();
        });
    }
};
