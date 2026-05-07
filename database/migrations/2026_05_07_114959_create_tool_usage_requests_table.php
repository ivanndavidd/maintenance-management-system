<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('site')->create('tool_usage_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // TUR-YYYYMMDD-0001
            $table->unsignedBigInteger('tool_id');
            $table->unsignedBigInteger('requested_by');
            $table->integer('quantity_requested');
            $table->date('usage_date');           // tanggal rencana pemakaian
            $table->date('return_date')->nullable(); // tanggal rencana pengembalian
            $table->string('purpose');            // tujuan pemakaian
            $table->string('location')->nullable(); // lokasi pemakaian
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_use', 'returned', 'cancelled'])
                  ->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();  // supervisor/admin
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->text('return_notes')->nullable();
            $table->timestamps();

            $table->foreign('tool_id')->references('id')->on('tools')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::connection('site')->dropIfExists('tool_usage_requests');
    }
};
