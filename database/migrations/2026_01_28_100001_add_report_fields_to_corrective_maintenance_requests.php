<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add new status values to enum
        DB::statement("ALTER TABLE corrective_maintenance_requests MODIFY COLUMN status ENUM('pending','received','in_progress','completed','failed','cancelled','done','further_repair') DEFAULT 'pending'");

        Schema::table('corrective_maintenance_requests', function (Blueprint $table) {
            $table->foreignId('parent_ticket_id')->nullable()->after('handled_by')
                ->constrained('corrective_maintenance_requests')->nullOnDelete();
            $table->timestamp('report_submitted_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('corrective_maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['parent_ticket_id']);
            $table->dropColumn(['parent_ticket_id', 'report_submitted_at']);
        });

        DB::statement("ALTER TABLE corrective_maintenance_requests MODIFY COLUMN status ENUM('pending','received','in_progress','completed','failed','cancelled') DEFAULT 'pending'");
    }
};
