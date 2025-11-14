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
        Schema::create('job_completion_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->unsignedBigInteger('user_id');
            $table->date('scheduled_date');
            $table->dateTime('completed_at');
            $table->integer('days_late')->default(0)->comment('Positive = late, 0 = on time, negative = early');
            $table->enum('completion_status', ['on_time', 'late', 'early']);
            $table->string('job_code');
            $table->string('job_title');
            $table->enum('job_type', ['preventive', 'corrective', 'predictive', 'breakdown']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent']);
            $table->timestamps();

            $table->foreign('job_id')->references('id')->on('maintenance_jobs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['user_id', 'completion_status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_completion_logs');
    }
};
