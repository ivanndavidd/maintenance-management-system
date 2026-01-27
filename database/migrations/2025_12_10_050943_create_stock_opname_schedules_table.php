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
        Schema::create('stock_opname_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_code')->unique(); // SOS-20251210-001
            $table->enum('item_type', ['sparepart', 'tool']); // type of item to opname
            $table->enum('frequency', ['monthly', 'semesterly', 'annually']); // schedule frequency
            $table->date('scheduled_date'); // next scheduled opname date
            $table->date('last_executed_date')->nullable(); // last time opname was executed
            $table->time('scheduled_time')->nullable(); // scheduled time for opname
            $table->unsignedBigInteger('assigned_to'); // staff maintenance assigned
            $table->unsignedBigInteger('created_by'); // admin who created schedule
            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');
            $table->text('notes')->nullable();
            $table->integer('execution_count')->default(0); // number of times executed
            $table->integer('missed_count')->default(0); // number of times missed
            $table->timestamps();

            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_schedules');
    }
};
