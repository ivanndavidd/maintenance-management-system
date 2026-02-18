<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('target_user_id')->constrained('users')->onDelete('cascade');
            $table->json('requested_site_ids');
            $table->json('current_site_ids')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_access_requests');
    }
};
