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
        Schema::create('task_request_operators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_request_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('task_request_id')
                ->references('id')
                ->on('task_requests')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->unique(['task_request_id', 'user_id']);
        });

        // Add completed_by column to track who actually completed the task
        Schema::table('task_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('completed_by')->nullable()->after('assigned_to');
            $table->timestamp('completed_at')->nullable()->after('completed_by');

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
        Schema::table('task_requests', function (Blueprint $table) {
            $table->dropForeign(['completed_by']);
            $table->dropColumn(['completed_by', 'completed_at']);
        });

        Schema::dropIfExists('task_request_operators');
    }
};
