<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        $superAdminRole = Role::create(['name' => 'super-admin']);

        // Create Permissions
        $permissions = [
            'dashboard-view',
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'job-list',
            'job-create',
            'job-edit',
            'job-delete',
            'report-list',
            'report-create',
            'report-validate',
            'machine-manage',
            'parts-manage',
            'analytics-view',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole->givePermissionTo([
            'dashboard-view',
            'user-list',
            'user-create',
            'user-edit',
            'job-list',
            'job-create',
            'job-edit',
            'report-list',
            'report-validate',
            'machine-manage',
            'parts-manage',
            'analytics-view',
        ]);

        $userRole->givePermissionTo([
            'dashboard-view',
            'job-list',
            'report-list',
            'report-create',
        ]);

        // Super Admin gets all permissions
        $superAdminRole->givePermissionTo(Permission::all());
    }
}