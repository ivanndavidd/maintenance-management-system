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
        Schema::create('corrective_maintenance_technicians', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cm_request_id');
            $table->unsignedBigInteger('user_id');
            $table->string('shift_info')->nullable(); // e.g., "Shift 2 (06:00-13:00)"
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            // Foreign keys with shorter names
            $table->foreign('cm_request_id', 'cm_tech_request_fk')
                ->references('id')
                ->on('corrective_maintenance_requests')
                ->cascadeOnDelete();

            $table->foreign('user_id', 'cm_tech_user_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            // Ensure unique assignment per ticket per user
            $table->unique(['cm_request_id', 'user_id'], 'cm_tech_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corrective_maintenance_technicians');
    }
};
