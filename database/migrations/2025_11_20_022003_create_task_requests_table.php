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
        Schema::create('task_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->unsignedBigInteger('requested_by'); // PIC who requested
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->string('task_type'); // preventive, corrective, inspection, etc.
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->string('title');
            $table->text('description');
            $table->date('requested_date')->nullable(); // When PIC wants task to be done
            $table->json('attachments')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'assigned', 'completed'])->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable(); // Admin who approved/rejected
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable(); // User assigned to task
            $table->unsignedBigInteger('job_id')->nullable(); // Link to maintenance job if created
            $table->timestamps();

            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('machine_id')->references('id')->on('machines')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('job_id')->references('id')->on('maintenance_jobs')->onDelete('set null');

            $table->index(['requested_by', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_requests');
    }
};
