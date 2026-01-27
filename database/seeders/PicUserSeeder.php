<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PicUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create PIC user for testing
        $picUser = User::create([
            'name' => 'PIC User',
            'email' => 'pic@warehouse.com',
            'password' => Hash::make('password'),
            'employee_id' => 'PIC001',
            'email_verified_at' => now(),
        ]);

        // Assign PIC role
        $picUser->assignRole('pic');

        $this->command->info('PIC user created successfully!');
        $this->command->info('Email: pic@warehouse.com');
        $this->command->info('Password: password');
    }
}
