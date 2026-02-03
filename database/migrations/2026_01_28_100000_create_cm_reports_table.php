<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cm_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cm_request_id')->constrained('corrective_maintenance_requests')->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets_master')->nullOnDelete();
            $table->enum('status', ['done', 'further_repair']);
            $table->text('problem_detail');
            $table->text('work_done');
            $table->text('notes')->nullable();
            $table->foreignId('submitted_by')->constrained('users');
            $table->timestamp('submitted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cm_reports');
    }
};
