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
        Schema::create('assets_master', function (Blueprint $table) {
            $table->id();

            // Auto-generated Asset ID (AST + YYYYMMDD + XXX)
            $table->string('asset_id', 20)->unique()->comment('Format: AST20240611001');

            // Equipment ID from Excel (Equipment column B, row 2)
            $table->string('equipment_id', 100)->nullable()->comment('From Excel: Equipment column');

            // Asset Name from Excel (Description column C, row 2)
            $table->string('asset_name')->comment('From Excel: Description column');

            // Location from Excel cell A1
            $table->string('location')->comment('From Excel: Cell A1');

            // Equipment Type from sheet name
            $table->string('equipment_type')->comment('From Excel: Sheet name');

            // Additional fields
            $table->string('status')->default('active')->comment('active, inactive, maintenance, disposed');
            $table->text('notes')->nullable();

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('equipment_id');
            $table->index('location');
            $table->index('equipment_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets_master');
    }
};
