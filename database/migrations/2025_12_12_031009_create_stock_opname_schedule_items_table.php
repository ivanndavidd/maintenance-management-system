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
        Schema::create('stock_opname_schedule_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->enum('item_type', ['sparepart', 'tool']);
            $table->unsignedBigInteger('item_id');
            $table->boolean('is_active')->default(true); // can be disabled without removing from schedule
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('stock_opname_schedules')->onDelete('cascade');

            // Composite unique to prevent duplicate items in same schedule
            $table->unique(['schedule_id', 'item_type', 'item_id']);

            // Index for faster queries
            $table->index(['item_type', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_schedule_items');
    }
};
