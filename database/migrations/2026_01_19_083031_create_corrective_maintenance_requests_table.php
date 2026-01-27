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
        Schema::create('corrective_maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();

            // Requestor Information
            $table->string('requestor_name');
            $table->string('requestor_email');
            $table->string('requestor_phone')->nullable();
            $table->string('requestor_department')->nullable();

            // Request Details
            $table->string('location');
            $table->string('equipment_name')->nullable();
            $table->string('equipment_id')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('problem_description');
            $table->text('additional_notes')->nullable();
            $table->string('attachment_path')->nullable();

            // Ticket Status & Assignment
            $table->enum('status', ['pending', 'received', 'in_progress', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();

            // Work Details
            $table->text('work_notes')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('in_progress_at')->nullable();

            // Email Tracking
            $table->timestamp('received_email_sent_at')->nullable();
            $table->timestamp('progress_email_sent_at')->nullable();
            $table->timestamp('completed_email_sent_at')->nullable();

            // Admin tracking
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corrective_maintenance_requests');
    }
};
