<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Receiving Area',
                'code' => 'RCV',
                'location' => 'Warehouse Block A - North',
                'description' => 'Area for incoming goods and inspection',
                'is_active' => true,
            ],
            [
                'name' => 'Storage Area',
                'code' => 'STG',
                'location' => 'Warehouse Block B - Central',
                'description' => 'Main storage area with racking systems',
                'is_active' => true,
            ],
            [
                'name' => 'Shipping Area',
                'code' => 'SHP',
                'location' => 'Warehouse Block C - South',
                'description' => 'Outbound goods and loading dock area',
                'is_active' => true,
            ],
            [
                'name' => 'Cold Storage',
                'code' => 'CLD',
                'location' => 'Warehouse Block D - East',
                'description' => 'Temperature controlled storage area',
                'is_active' => true,
            ],
            [
                'name' => 'Packaging Area',
                'code' => 'PKG',
                'location' => 'Warehouse Block E - West',
                'description' => 'Repackaging and labeling area',
                'is_active' => true,
            ],
            [
                'name' => 'Maintenance Workshop',
                'code' => 'MNT',
                'location' => 'Warehouse - Service Building',
                'description' => 'Equipment maintenance and repair workshop',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }
    }
}
