<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SiteUserSeeder extends Seeder
{
    /**
     * Seed users for a site database.
     */
    public function run(): void
    {
        // Create roles first
        $this->createRoles();

        // Create admin user
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin',
            'email' => 'admin@warehouse.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign admin role
        DB::table('model_has_roles')->insert([
            'role_id' => Role::where('name', 'admin')->first()->id,
            'model_type' => 'App\\Models\\User',
            'model_id' => $adminId,
        ]);

        // Create supervisor user
        $supervisorId = DB::table('users')->insertGetId([
            'name' => 'Supervisor',
            'email' => 'supervisor@warehouse.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign supervisor role
        DB::table('model_has_roles')->insert([
            'role_id' => Role::where('name', 'supervisor_maintenance')->first()->id,
            'model_type' => 'App\\Models\\User',
            'model_id' => $supervisorId,
        ]);

        // Create staff user
        $staffId = DB::table('users')->insertGetId([
            'name' => 'Staff',
            'email' => 'staff@warehouse.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign staff role
        DB::table('model_has_roles')->insert([
            'role_id' => Role::where('name', 'staff_maintenance')->first()->id,
            'model_type' => 'App\\Models\\User',
            'model_id' => $staffId,
        ]);

        $this->command->info('Site users seeded successfully!');
    }

    /**
     * Create roles if they don't exist
     */
    protected function createRoles(): void
    {
        $roles = ['admin', 'supervisor_maintenance', 'staff_maintenance'];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role, 'guard_name' => 'web']
            );
        }
    }
}
