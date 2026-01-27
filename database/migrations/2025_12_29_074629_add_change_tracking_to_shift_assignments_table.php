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
        Schema::table('shift_assignments', function (Blueprint $table) {
            // Track if assignment was changed (cancelled or replaced)
            $table->string('change_action')->nullable()->after('color')
                ->comment('null = active, cancelled = cancelled, replaced = replaced with another user');

            // For replacement: store new user ID
            $table->unsignedBigInteger('new_user_id')->nullable()->after('change_action');

            // Date when the change is effective
            $table->date('change_effective_date')->nullable()->after('new_user_id');

            // Reason for the change
            $table->text('change_reason')->nullable()->after('change_effective_date');

            // Who made the change
            $table->unsignedBigInteger('changed_by')->nullable()->after('change_reason');

            // Foreign keys
            $table->foreign('new_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_assignments', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['new_user_id']);
            $table->dropForeign(['changed_by']);

            // Drop columns
            $table->dropColumn([
                'change_action',
                'new_user_id',
                'change_effective_date',
                'change_reason',
                'changed_by'
            ]);
        });
    }
};
