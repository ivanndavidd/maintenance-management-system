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
        Schema::create('pm_task_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_task_id')->constrained('pm_tasks')->cascadeOnDelete();
            $table->text('description');
            $table->json('photos')->nullable();
            $table->enum('status', ['submitted', 'approved', 'revision_needed'])->default('submitted');
            $table->text('admin_comments')->nullable();
            $table->foreignId('submitted_by')->constrained('users');
            $table->timestamp('submitted_at');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('pm_task_id');
            $table->index('status');
        });

        Schema::create('pm_task_report_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_task_report_id')->constrained('pm_task_reports')->cascadeOnDelete();
            $table->unsignedBigInteger('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets_master')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pm_task_report_assets');
        Schema::dropIfExists('pm_task_reports');
    }
};
