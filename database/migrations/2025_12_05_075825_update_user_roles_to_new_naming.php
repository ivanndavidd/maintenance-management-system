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
        // Update role name from 'user' to 'staff_maintenance' in roles table
        DB::table('roles')
            ->where('name', 'user')
            ->update(['name' => 'staff_maintenance']);

        // Create supervisor_maintenance role if it doesn't exist
        $supervisorRoleExists = DB::table('roles')
            ->where('name', 'supervisor_maintenance')
            ->exists();

        if (!$supervisorRoleExists) {
            DB::table('roles')->insert([
                'name' => 'supervisor_maintenance',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert role name from 'staff_maintenance' back to 'user'
        DB::table('roles')
            ->where('name', 'staff_maintenance')
            ->update(['name' => 'user']);

        // Remove supervisor_maintenance role
        DB::table('roles')
            ->where('name', 'supervisor_maintenance')
            ->delete();
    }
};
