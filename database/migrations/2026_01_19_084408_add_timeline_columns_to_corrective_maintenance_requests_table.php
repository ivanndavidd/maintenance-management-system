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
            $table->timestamp('received_at')->nullable()->after('completed_at');
            $table->timestamp('in_progress_at')->nullable()->after('received_at');
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
