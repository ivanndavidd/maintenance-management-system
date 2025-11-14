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
        Schema::table('maintenance_jobs', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('notes');
            $table->enum('recurrence_type', ['daily', 'weekly', 'monthly', 'yearly'])->nullable()->after('is_recurring');
            $table->integer('recurrence_interval')->nullable()->after('recurrence_type')->comment('e.g., every 2 days, every 3 months');
            $table->date('recurrence_end_date')->nullable()->after('recurrence_interval');
            $table->unsignedBigInteger('parent_job_id')->nullable()->after('recurrence_end_date')->comment('For tracking recurring job lineage');

            $table->foreign('parent_job_id')->references('id')->on('maintenance_jobs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_jobs', function (Blueprint $table) {
            $table->dropForeign(['parent_job_id']);
            $table->dropColumn([
                'is_recurring',
                'recurrence_type',
                'recurrence_interval',
                'recurrence_end_date',
                'parent_job_id'
            ]);
        });
    }
};
