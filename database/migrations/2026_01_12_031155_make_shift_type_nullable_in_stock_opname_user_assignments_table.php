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
        // Make shift_type nullable for manual assignment system
        DB::statement("ALTER TABLE stock_opname_user_assignments MODIFY COLUMN shift_type ENUM('shift_1', 'shift_2', 'shift_3') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to NOT NULL (set default value for existing nulls first)
        DB::statement("UPDATE stock_opname_user_assignments SET shift_type = 'shift_1' WHERE shift_type IS NULL");
        DB::statement("ALTER TABLE stock_opname_user_assignments MODIFY COLUMN shift_type ENUM('shift_1', 'shift_2', 'shift_3') NOT NULL");
    }
};
