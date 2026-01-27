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
        // Create pivot table for multiple operators
        Schema::create('incident_report_operators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('incident_report_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('incident_report_id')
                ->references('id')
                ->on('incident_reports')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->unique(['incident_report_id', 'user_id']);
        });

        // Add completed_by column to track who actually completed the task
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('completed_by')->nullable()->after('resolved_by');
            $table->timestamp('completed_at')->nullable()->after('resolved_at');

            $table->foreign('completed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->dropForeign(['completed_by']);
            $table->dropColumn(['completed_by', 'completed_at']);
        });

        Schema::dropIfExists('incident_report_operators');
    }
};
