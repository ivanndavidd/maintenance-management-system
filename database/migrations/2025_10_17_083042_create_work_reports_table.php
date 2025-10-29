<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_code', 20)->unique();
            $table->foreignId('job_id')->constrained('maintenance_jobs');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->datetime('work_start');
            $table->datetime('work_end');
            $table->integer('downtime_minutes')->default(0);
            $table->text('work_performed');
            $table->text('issues_found')->nullable();
            $table->text('recommendations')->nullable();
            $table->enum('machine_condition', ['good', 'fair', 'needs_attention', 'critical']);
            $table->enum('status', ['draft', 'submitted', 'approved', 'revision_needed'])
                  ->default('draft');
            $table->text('admin_comments')->nullable();
            $table->datetime('validated_at')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            
            // Index for performance
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_reports');
    }
};