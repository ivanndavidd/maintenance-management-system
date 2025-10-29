<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@warehouse.com',
            'password' => Hash::make('password123'),
        ]);
        $superAdmin->assignRole('super-admin');

        // Create Admin
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@warehouse.com',
            'password' => Hash::make('password123'),
        ]);
        $admin->assignRole('admin');

        // Create Sample User
        $user = User::create([
            'name' => 'John Operator',
            'email' => 'operator@warehouse.com',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('user');
    }
}