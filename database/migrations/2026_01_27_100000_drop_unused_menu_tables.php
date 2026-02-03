<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drops tables related to removed menus:
     * - Maintenance Jobs
     * - Work Reports
     * - Incident Reports
     * - Task Requests
     */
    public function up(): void
    {
        // Drop tables with foreign key dependencies first

        // Task Request related tables
        Schema::dropIfExists('task_request_operators');
        Schema::dropIfExists('task_requests');

        // Incident Report related tables
        Schema::dropIfExists('incident_report_operators');
        Schema::dropIfExists('incident_reports');

        // Work Reports
        Schema::dropIfExists('work_reports');

        // Maintenance Jobs related tables
        Schema::dropIfExists('job_completion_logs');
        Schema::dropIfExists('maintenance_jobs');

        // Inventory Requests (if exists and not used)
        Schema::dropIfExists('inventory_requests');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate maintenance_jobs table
        Schema::create('maintenance_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('machine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Recreate job_completion_logs table
        Schema::create('job_completion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('completed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Recreate work_reports table
        Schema::create('work_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_job_id')->nullable()->constrained()->nullOnDelete();
            $table->date('report_date');
            $table->text('work_performed');
            $table->text('issues_found')->nullable();
            $table->text('recommendations')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        // Recreate incident_reports table
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->string('incident_number')->unique();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('location')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'investigating', 'resolved', 'closed'])->default('pending');
            $table->json('attachments')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });

        // Recreate incident_report_operators pivot table
        Schema::create('incident_report_operators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Recreate task_requests table
        Schema::create('task_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_progress', 'completed'])->default('pending');
            $table->date('requested_date')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // Recreate task_request_operators pivot table
        Schema::create('task_request_operators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Recreate inventory_requests table
        Schema::create('inventory_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('item_name');
            $table->integer('quantity');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'fulfilled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }
};
