<?php

namespace Database\Seeders;

use App\Models\MachineCategory;
use Illuminate\Database\Seeder;

class MachineCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Forklift',
                'code' => 'FLT',
                'description' => 'Electric and diesel forklifts for material handling',
            ],
            [
                'name' => 'Pallet Jack',
                'code' => 'PLJ',
                'description' => 'Manual and electric pallet jacks',
            ],
            [
                'name' => 'Conveyor System',
                'code' => 'CNV',
                'description' => 'Belt and roller conveyor systems',
            ],
            [
                'name' => 'Racking System',
                'code' => 'RCK',
                'description' => 'Storage racks and shelving systems',
            ],
            [
                'name' => 'Loading Dock Equipment',
                'code' => 'LDE',
                'description' => 'Dock levelers, dock seals, and dock doors',
            ],
            [
                'name' => 'Scissor Lift',
                'code' => 'SCL',
                'description' => 'Hydraulic and electric scissor lifts',
            ],
            [
                'name' => 'HVAC System',
                'code' => 'HVC',
                'description' => 'Heating, ventilation, and air conditioning systems',
            ],
            [
                'name' => 'Lighting System',
                'code' => 'LGT',
                'description' => 'LED and industrial lighting systems',
            ],
            [
                'name' => 'Security System',
                'code' => 'SEC',
                'description' => 'CCTV, access control, and alarm systems',
            ],
            [
                'name' => 'Fire Safety System',
                'code' => 'FIR',
                'description' => 'Fire alarms, sprinklers, and extinguishers',
            ],
            [
                'name' => 'Weighing Scale',
                'code' => 'WGH',
                'description' => 'Floor scales and pallet scales',
            ],
            [
                'name' => 'Packaging Machine',
                'code' => 'PKM',
                'description' => 'Wrapping and strapping machines',
            ],
        ];

        foreach ($categories as $category) {
            MachineCategory::create($category);
        }
    }
}