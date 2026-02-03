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
        Schema::table('corrective_maintenance_requests', function (Blueprint $table) {
            // Only add columns if they don't already exist
            if (!Schema::hasColumn('corrective_maintenance_requests', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('corrective_maintenance_requests', 'in_progress_at')) {
                $table->timestamp('in_progress_at')->nullable()->after('received_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corrective_maintenance_requests', function (Blueprint $table) {
            $table->dropColumn(['received_at', 'in_progress_at']);
        });
    }
};
