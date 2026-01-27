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
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_code')->unique();
            $table->unsignedBigInteger('reported_by'); // PIC who reported
            $table->unsignedBigInteger('machine_id');
            $table->string('incident_type'); // breakdown, abnormal_sound, overheating, etc.
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('title');
            $table->text('description');
            $table->json('attachments')->nullable(); // Photos/videos of incident
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'resolved', 'closed'])->default('pending');
            $table->unsignedBigInteger('assigned_to')->nullable(); // Operator/technician assigned
            $table->dateTime('assigned_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->foreign('reported_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('machine_id')->references('id')->on('machines')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['reported_by', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['machine_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
