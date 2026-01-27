<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Track all shift assignment changes including:
     * - User replacements (David -> Rangga)
     * - Shift cancellations (David absent, no replacement)
     * - Reason for changes
     */
    public function up(): void
    {
        Schema::create('shift_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_assignment_id')->constrained()->onDelete('cascade');
            $table->enum('change_type', ['replacement', 'cancellation', 'restoration'])
                ->comment('replacement: user changed, cancellation: shift cancelled, restoration: cancelled shift restored');

            // Original assignment details
            $table->foreignId('original_user_id')->nullable()->constrained('users')->onDelete('set null');

            // New assignment details (null for cancellations)
            $table->foreignId('new_user_id')->nullable()->constrained('users')->onDelete('set null');

            // Reason and metadata
            $table->text('reason')->comment('Why the change was made (e.g., sick, emergency, etc.)');
            $table->date('effective_date')->comment('Date when this change is effective');

            // Who made the change
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();

            // Index for querying
            $table->index(['shift_assignment_id', 'effective_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_change_logs');
    }
};
