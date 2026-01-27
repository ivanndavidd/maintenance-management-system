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
            // Approval status
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->after('status');

            // User who needs to approve (tagged user)
            $table->unsignedBigInteger('approver_id')->nullable()->after('approval_status');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');

            // User who approved/rejected
            $table->unsignedBigInteger('approved_by')->nullable()->after('approver_id');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            // Approval date
            $table->timestamp('approved_at')->nullable()->after('approved_by');

            // Rejection reason
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['approver_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'approval_status',
                'approver_id',
                'approved_by',
                'approved_at',
                'rejection_reason'
            ]);
        });
    }
};
