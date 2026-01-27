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
        Schema::table('stock_opname_schedules', function (Blueprint $table) {
            $table->string('ticket_status')->default('open')->after('status'); // open, closed
            $table->timestamp('closed_at')->nullable()->after('ticket_status');
            $table->unsignedBigInteger('closed_by')->nullable()->after('closed_at');
            $table->string('execution_type')->nullable()->after('closed_by'); // late, early, ontime
            $table->integer('days_difference')->nullable()->after('execution_type'); // berapa hari terlambat/cepat

            // Foreign key
            $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_opname_schedules', function (Blueprint $table) {
            $table->dropForeign(['closed_by']);
            $table->dropColumn(['ticket_status', 'closed_at', 'closed_by', 'execution_type', 'days_difference']);
        });
    }
};
