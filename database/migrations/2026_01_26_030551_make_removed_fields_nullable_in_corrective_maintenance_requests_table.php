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
        Schema::table('corrective_maintenance_requests', function (Blueprint $table) {
            // Make removed fields nullable since they are no longer required in the form
            $table->string('location')->nullable()->change();
            $table->string('equipment_name')->nullable()->change();
            $table->string('equipment_id')->nullable()->change();
            $table->string('requestor_phone')->nullable()->change();
            $table->string('requestor_department')->nullable()->change();
            $table->text('additional_notes')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corrective_maintenance_requests', function (Blueprint $table) {
            $table->string('location')->nullable(false)->change();
            $table->string('equipment_name')->nullable(false)->change();
            $table->string('equipment_id')->nullable(false)->change();
            $table->string('requestor_phone')->nullable(false)->change();
            $table->string('requestor_department')->nullable(false)->change();
            $table->text('additional_notes')->nullable(false)->change();
        });
    }
};
