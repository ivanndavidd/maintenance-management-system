<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@warehouse.com',
            'password' => Hash::make('password123'),
            'employee_id' => 'ADM001',
        ]);
        $admin->assignRole('admin');

        // Create Sample Staff Maintenance
        $user = User::create([
            'name' => 'John Operator',
            'email' => 'operator@warehouse.com',
            'password' => Hash::make('password123'),
            'employee_id' => 'OPR001',
        ]);
        $user->assignRole('staff_maintenance');
    }
}