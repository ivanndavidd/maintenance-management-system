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
        Schema::table('spareparts', function (Blueprint $table) {
            // Rename material_code to sparepart_id (auto-generated SPR...)
            $table->renameColumn('material_code', 'sparepart_id');

            // Rename material_code_external to material_code (from CSV)
            $table->renameColumn('material_code_external', 'material_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spareparts', function (Blueprint $table) {
            // Reverse the changes
            $table->renameColumn('sparepart_id', 'material_code');
            $table->renameColumn('material_code', 'material_code_external');
        });
    }
};
