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
        // Update Purchase Orders: Remove dashes from po_number
        // Example: PO-20251219-001 becomes PO20251219001
        DB::statement("UPDATE purchase_orders SET po_number = REPLACE(po_number, '-', '')");

        // Update Stock Adjustments: Remove dashes from adjustment_code
        // Example: ADJ-20251216-005 becomes ADJ20251216005
        DB::statement("UPDATE stock_adjustments SET adjustment_code = REPLACE(adjustment_code, '-', '')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: Add dashes back to po_number
        // Example: PO20251219001 becomes PO-20251219-001
        DB::statement("
            UPDATE purchase_orders
            SET po_number = CONCAT(
                SUBSTRING(po_number, 1, 2),
                '-',
                SUBSTRING(po_number, 3, 8),
                '-',
                SUBSTRING(po_number, 11, 3)
            )
            WHERE po_number NOT LIKE '%-%'
        ");

        // Reverse: Add dashes back to adjustment_code
        // Example: ADJ20251216005 becomes ADJ-20251216-005
        DB::statement("
            UPDATE stock_adjustments
            SET adjustment_code = CONCAT(
                SUBSTRING(adjustment_code, 1, 3),
                '-',
                SUBSTRING(adjustment_code, 4, 8),
                '-',
                SUBSTRING(adjustment_code, 12, 3)
            )
            WHERE adjustment_code NOT LIKE '%-%'
        ");
    }
};
