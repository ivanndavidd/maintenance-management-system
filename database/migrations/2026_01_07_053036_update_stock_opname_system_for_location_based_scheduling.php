<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new fields to stock_opname_schedules
        Schema::table('stock_opname_schedules', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn('item_type');
            $table->dropForeign(['assigned_to']);
            $table->dropColumn('assigned_to');
            $table->dropColumn('frequency');
            $table->dropColumn(['scheduled_date', 'last_executed_date', 'scheduled_time']);

            // Add new fields for location-based scheduling
            $table->date('start_date')->after('schedule_code');
            $table->date('end_date')->after('start_date');

            // Track which item types are included
            $table->boolean('include_spareparts')->default(false)->after('end_date');
            $table->boolean('include_tools')->default(false)->after('include_spareparts');
            $table->boolean('include_assets')->default(false)->after('include_tools');

            // Store selected locations as JSON
            $table->json('sparepart_locations')->nullable()->after('include_assets');
            $table->json('asset_locations')->nullable()->after('sparepart_locations');

            // Progress tracking
            $table->integer('total_items')->default(0)->after('asset_locations');
            $table->integer('completed_items')->default(0)->after('total_items');
            $table->integer('cancelled_items')->default(0)->after('completed_items');
        });

        // Update status enum
        DB::statement("ALTER TABLE stock_opname_schedules MODIFY COLUMN status ENUM('active', 'in_progress', 'completed', 'cancelled') DEFAULT 'active'");

        // Update stock_opname_schedule_items
        Schema::table('stock_opname_schedule_items', function (Blueprint $table) {
            // Add execution tracking fields
            $table->enum('execution_status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending')->after('item_id');
            $table->bigInteger('executed_by')->unsigned()->nullable()->after('execution_status');
            $table->integer('system_quantity')->nullable()->after('executed_by');
            $table->integer('physical_quantity')->nullable()->after('system_quantity');
            $table->integer('discrepancy_qty')->default(0)->after('physical_quantity');
            $table->decimal('discrepancy_value', 15, 2)->default(0)->after('discrepancy_qty');
            $table->text('notes')->nullable()->after('discrepancy_value');
            $table->timestamp('executed_at')->nullable()->after('notes');

            $table->foreign('executed_by')->references('id')->on('users')->onDelete('set null');
        });

        // Modify item_type enum to include 'asset'
        DB::statement("ALTER TABLE stock_opname_schedule_items MODIFY COLUMN item_type ENUM('sparepart', 'tool', 'asset')");

        // Create new table for tracking user assignments
        Schema::create('stock_opname_user_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('user_id');
            $table->date('assignment_date');
            $table->enum('shift_type', ['shift_1', 'shift_2', 'shift_3']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('stock_opname_schedules')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['schedule_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_user_assignments');

        Schema::table('stock_opname_schedule_items', function (Blueprint $table) {
            $table->dropForeign(['executed_by']);
            $table->dropColumn([
                'execution_status',
                'executed_by',
                'system_quantity',
                'physical_quantity',
                'discrepancy_qty',
                'discrepancy_value',
                'notes',
                'executed_at'
            ]);
        });

        DB::statement("ALTER TABLE stock_opname_schedule_items MODIFY COLUMN item_type ENUM('sparepart', 'tool')");

        Schema::table('stock_opname_schedules', function (Blueprint $table) {
            $table->dropColumn([
                'start_date',
                'end_date',
                'include_spareparts',
                'include_tools',
                'include_assets',
                'sparepart_locations',
                'asset_locations',
                'total_items',
                'completed_items',
                'cancelled_items'
            ]);

            $table->enum('item_type', ['sparepart', 'tool'])->after('schedule_code');
            $table->unsignedBigInteger('assigned_to')->after('item_type');
            $table->enum('frequency', ['monthly', 'semesterly', 'annually'])->after('item_type');
            $table->date('scheduled_date')->after('frequency');
            $table->date('last_executed_date')->nullable()->after('scheduled_date');
            $table->time('scheduled_time')->nullable()->after('last_executed_date');

            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('restrict');
        });

        DB::statement("ALTER TABLE stock_opname_schedules MODIFY COLUMN status ENUM('active', 'inactive', 'completed') DEFAULT 'active'");
    }
};
