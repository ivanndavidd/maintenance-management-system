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
        Schema::create('shift_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Week 51 - December 2025"
            $table->date('start_date'); // Senin minggu tersebut
            $table->date('end_date'); // Minggu minggu tersebut
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->enum('shift_type', ['shift_1', 'shift_2', 'shift_3']); // shift_1: 22:00-05:00, shift_2: 06:00-13:00, shift_3: 14:00-21:00
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('working_hours', 4, 2); // e.g., 8.00, 7.50
            $table->text('notes')->nullable();
            $table->timestamps();

            // Ensure unique assignment per user per day per shift
            $table->unique(['shift_schedule_id', 'user_id', 'day_of_week', 'shift_type'], 'unique_shift_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_assignments');
        Schema::dropIfExists('shift_schedules');
    }
};
