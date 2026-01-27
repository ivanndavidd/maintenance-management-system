<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class SupervisorMaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create maintenance department
        $maintenanceDept = Department::firstOrCreate(
            ['name' => 'Maintenance'],
            [
                'code' => 'MAINT',
                'description' => 'Maintenance Department',
            ]
        );

        // Create Supervisor Maintenance user
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@warehouse.com'],
            [
                'name' => 'Supervisor Maintenance',
                'employee_id' => 'SUP001',
                'password' => Hash::make('password'),
                'phone' => '+62812345678',
                'department_id' => $maintenanceDept->id,
                'is_active' => true,
            ]
        );

        // Assign supervisor_maintenance role
        if (!$supervisor->hasRole('supervisor_maintenance')) {
            $supervisor->assignRole('supervisor_maintenance');
        }

        $this->command->info('Supervisor Maintenance user created successfully!');
        $this->command->info('Email: supervisor@warehouse.com');
        $this->command->info('Password: password');
    }
}
