<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_code', 20)->unique();
            $table->string('title');
            $table->text('description');
            $table->foreignId('machine_id')->constrained('machines');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->enum('type', ['preventive', 'corrective', 'predictive', 'breakdown']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent']);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'overdue'])
                  ->default('pending');
            $table->datetime('scheduled_date');
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->integer('estimated_duration')->nullable(); // minutes
            $table->integer('actual_duration')->nullable(); // minutes
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['status', 'scheduled_date']);
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_jobs');
    }
};