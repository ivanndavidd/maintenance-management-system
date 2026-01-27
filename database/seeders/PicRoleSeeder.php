<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PicRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create PIC role
        $picRole = Role::firstOrCreate(['name' => 'pic']);

        // Define PIC permissions
        $permissions = [
            'view dashboard',
            'create incident reports',
            'view own incident reports',
            'edit own incident reports',
            'delete own incident reports',
            'request maintenance task',
            'view own task requests',
            'view notifications',
            'view profile',
            'edit profile',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all permissions to PIC role
        $picRole->syncPermissions($permissions);

        $this->command->info('PIC role and permissions created successfully!');
    }
}
