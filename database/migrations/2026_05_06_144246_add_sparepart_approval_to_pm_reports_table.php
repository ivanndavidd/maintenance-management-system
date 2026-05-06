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
        // Extend status enum with new values
        \DB::connection('site')->statement(
            "ALTER TABLE pm_task_reports MODIFY COLUMN status ENUM('submitted','approved','revision_needed','pending_sparepart_approval','sparepart_rejected') NOT NULL DEFAULT 'submitted'"
        );

        Schema::connection('site')->table('pm_task_reports', function (Blueprint $table) {
            $table->string('sparepart_approval_status')->nullable()->after('status'); // pending, approved, rejected
            $table->text('sparepart_approval_notes')->nullable()->after('sparepart_approval_status');
            $table->unsignedBigInteger('sparepart_approved_by')->nullable()->after('sparepart_approval_notes');
            $table->timestamp('sparepart_approved_at')->nullable()->after('sparepart_approved_by');
            $table->foreign('sparepart_approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('site')->table('pm_task_reports', function (Blueprint $table) {
            $table->dropForeign(['sparepart_approved_by']);
            $table->dropColumn([
                'sparepart_approval_status',
                'sparepart_approval_notes',
                'sparepart_approved_by',
                'sparepart_approved_at',
            ]);
        });

        \DB::connection('site')->statement(
            "ALTER TABLE pm_task_reports MODIFY COLUMN status ENUM('submitted','approved','revision_needed') NOT NULL DEFAULT 'submitted'"
        );
    }
};
