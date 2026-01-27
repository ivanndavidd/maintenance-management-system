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
        Schema::create('stock_opname_executions', function (Blueprint $table) {
            $table->id();
            $table->string('execution_code')->unique(); // SOE-20251210-001
            $table->unsignedBigInteger('schedule_id')->nullable(); // reference to schedule
            $table->enum('item_type', ['sparepart', 'tool']); // type of item
            $table->unsignedBigInteger('item_id'); // sparepart_id or tool_id
            $table->date('execution_date'); // actual date of opname
            $table->date('scheduled_date')->nullable(); // scheduled date (for compliance check)
            $table->integer('system_quantity'); // quantity in system before opname
            $table->integer('physical_quantity'); // actual counted quantity
            $table->integer('discrepancy_qty')->default(0); // difference
            $table->decimal('discrepancy_value', 15, 2)->default(0); // value of difference
            $table->enum('status', ['on_time', 'late', 'early'])->default('on_time'); // compliance status
            $table->boolean('is_missed')->default(false); // flag if schedule was missed
            $table->integer('days_difference')->default(0); // days difference from scheduled date
            $table->unsignedBigInteger('executed_by'); // staff who performed opname
            $table->unsignedBigInteger('verified_by')->nullable(); // admin who verified
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('discrepancy_notes')->nullable(); // notes about discrepancy
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('stock_opname_schedules')->onDelete('set null');
            $table->foreign('executed_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');

            // Indexes for faster querying
            $table->index(['item_type', 'item_id']);
            $table->index('execution_date');
            $table->index('is_missed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_executions');
    }
};
