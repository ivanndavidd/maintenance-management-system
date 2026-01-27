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
        // Alter enum to add 'cancelled' status
        DB::statement("ALTER TABLE purchase_order_items MODIFY COLUMN status ENUM('pending', 'partial_received', 'received', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'cancelled' from enum
        DB::statement("ALTER TABLE purchase_order_items MODIFY COLUMN status ENUM('pending', 'partial_received', 'received') DEFAULT 'pending'");
    }
};
