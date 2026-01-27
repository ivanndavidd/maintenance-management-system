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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Compliance status: compliant, non_compliant, pending
            $table->enum('compliance_status', ['pending', 'compliant', 'non_compliant'])
                ->default('pending')
                ->after('status');

            // Non-compliance notes (why it's not compliant)
            $table->text('non_compliance_notes')->nullable()->after('compliance_status');

            // Checked by (user who verified the physical goods)
            $table->unsignedBigInteger('checked_by')->nullable()->after('non_compliance_notes');
            $table->foreign('checked_by')->references('id')->on('users')->onDelete('set null');

            // Check date
            $table->timestamp('checked_at')->nullable()->after('checked_by');

            // Stock added flag
            $table->boolean('stock_added')->default(false)->after('checked_at');

            // Stock added by
            $table->unsignedBigInteger('stock_added_by')->nullable()->after('stock_added');
            $table->foreign('stock_added_by')->references('id')->on('users')->onDelete('set null');

            // Stock added at
            $table->timestamp('stock_added_at')->nullable()->after('stock_added_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['checked_by']);
            $table->dropForeign(['stock_added_by']);
            $table->dropColumn([
                'compliance_status',
                'non_compliance_notes',
                'checked_by',
                'checked_at',
                'stock_added',
                'stock_added_by',
                'stock_added_at'
            ]);
        });
    }
};
