<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_report_id')->constrained('work_reports')->onDelete('cascade');
            $table->foreignId('part_id')->constrained('parts');
            $table->integer('quantity');
            $table->decimal('cost', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Composite index
            $table->unique(['work_report_id', 'part_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_parts');
    }
};