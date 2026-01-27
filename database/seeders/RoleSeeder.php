<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles (use firstOrCreate to avoid duplicates)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $staffMaintenanceRole = Role::firstOrCreate(['name' => 'staff_maintenance']);
        $supervisorMaintenanceRole = Role::firstOrCreate(['name' => 'supervisor_maintenance']);

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
            Permission::firstOrCreate(['name' => $permission]);
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

        // Staff Maintenance permissions (basic operator permissions)
        $staffMaintenanceRole->givePermissionTo([
            'dashboard-view',
            'job-list',
            'report-list',
            'report-create',
        ]);

        // Supervisor Maintenance permissions (can manage staff and approve reports)
        $supervisorMaintenanceRole->givePermissionTo([
            'dashboard-view',
            'job-list',
            'job-create',
            'job-edit',
            'report-list',
            'report-create',
            'report-validate',
            'analytics-view',
        ]);
    }
}