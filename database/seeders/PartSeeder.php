<?php

namespace Database\Seeders;

use App\Models\Part;
use Illuminate\Database\Seeder;

class PartSeeder extends Seeder
{
    public function run(): void
    {
        $parts = [
            // Forklift Parts
            [
                'code' => 'PRT-FLT-001',
                'name' => 'Forklift Battery 48V',
                'description' => 'Lead-acid traction battery for electric forklifts',
                'unit' => 'unit',
                'stock_quantity' => 5,
                'minimum_stock' => 2,
                'unit_cost' => 3500.00,
                'supplier' => 'Industrial Battery Co.',
                'location' => 'Warehouse Rack A-1',
            ],
            [
                'code' => 'PRT-FLT-002',
                'name' => 'Forklift Tire (Solid)',
                'description' => 'Solid rubber tire 16x6-8',
                'unit' => 'pcs',
                'stock_quantity' => 12,
                'minimum_stock' => 4,
                'unit_cost' => 185.00,
                'supplier' => 'Tire Supplies Ltd.',
                'location' => 'Warehouse Rack A-2',
            ],
            [
                'code' => 'PRT-FLT-003',
                'name' => 'Hydraulic Oil Filter',
                'description' => 'Hydraulic filter for Toyota/Komatsu forklifts',
                'unit' => 'pcs',
                'stock_quantity' => 20,
                'minimum_stock' => 5,
                'unit_cost' => 35.00,
                'supplier' => 'Filter Tech',
                'location' => 'Warehouse Rack A-3',
            ],
            [
                'code' => 'PRT-FLT-004',
                'name' => 'Fork Extension 72"',
                'description' => 'Heavy duty fork extensions',
                'unit' => 'pair',
                'stock_quantity' => 3,
                'minimum_stock' => 1,
                'unit_cost' => 450.00,
                'supplier' => 'Material Handling Solutions',
                'location' => 'Warehouse Rack A-4',
            ],

            // Conveyor Parts
            [
                'code' => 'PRT-CNV-001',
                'name' => 'Conveyor Belt (per meter)',
                'description' => 'PVC conveyor belt 800mm width',
                'unit' => 'meter',
                'stock_quantity' => 50,
                'minimum_stock' => 20,
                'unit_cost' => 125.00,
                'supplier' => 'Belt Systems Inc.',
                'location' => 'Warehouse Rack B-1',
            ],
            [
                'code' => 'PRT-CNV-002',
                'name' => 'Conveyor Roller',
                'description' => 'Steel roller 600mm width',
                'unit' => 'pcs',
                'stock_quantity' => 25,
                'minimum_stock' => 10,
                'unit_cost' => 45.00,
                'supplier' => 'Roller Tech Co.',
                'location' => 'Warehouse Rack B-2',
            ],
            [
                'code' => 'PRT-CNV-003',
                'name' => 'Drive Motor 5HP',
                'description' => 'Electric motor for conveyor drive',
                'unit' => 'unit',
                'stock_quantity' => 2,
                'minimum_stock' => 1,
                'unit_cost' => 850.00,
                'supplier' => 'Motor Supplies Ltd.',
                'location' => 'Warehouse Rack B-3',
            ],

            // General Maintenance
            [
                'code' => 'PRT-GEN-001',
                'name' => 'Hydraulic Oil (20L)',
                'description' => 'ISO VG 46 Hydraulic Oil',
                'unit' => 'bucket',
                'stock_quantity' => 15,
                'minimum_stock' => 5,
                'unit_cost' => 85.00,
                'supplier' => 'Lubricant Supplies',
                'location' => 'Warehouse Rack C-1',
            ],
            [
                'code' => 'PRT-GEN-002',
                'name' => 'Grease Cartridge',
                'description' => 'Multi-purpose lithium grease 400g',
                'unit' => 'pcs',
                'stock_quantity' => 50,
                'minimum_stock' => 20,
                'unit_cost' => 8.50,
                'supplier' => 'Lubricant Supplies',
                'location' => 'Warehouse Rack C-2',
            ],
            [
                'code' => 'PRT-GEN-003',
                'name' => 'Safety Light (LED)',
                'description' => 'Blue spot safety light for forklifts',
                'unit' => 'pcs',
                'stock_quantity' => 8,
                'minimum_stock' => 3,
                'unit_cost' => 125.00,
                'supplier' => 'Safety Equipment Co.',
                'location' => 'Warehouse Rack C-3',
            ],

            // HVAC Parts
            [
                'code' => 'PRT-HVC-001',
                'name' => 'Air Filter 24x24x2',
                'description' => 'MERV 8 Pleated air filter',
                'unit' => 'pcs',
                'stock_quantity' => 30,
                'minimum_stock' => 10,
                'unit_cost' => 15.00,
                'supplier' => 'HVAC Supplies Inc.',
                'location' => 'Warehouse Rack D-1',
            ],
            [
                'code' => 'PRT-HVC-002',
                'name' => 'Refrigerant R410A (25lb)',
                'description' => 'R410A refrigerant cylinder',
                'unit' => 'cylinder',
                'stock_quantity' => 4,
                'minimum_stock' => 2,
                'unit_cost' => 350.00,
                'supplier' => 'Cooling Solutions',
                'location' => 'Warehouse Rack D-2',
            ],
            [
                'code' => 'PRT-HVC-003',
                'name' => 'Compressor Oil (1 Gal)',
                'description' => 'POE oil for HVAC compressors',
                'unit' => 'gallon',
                'stock_quantity' => 6,
                'minimum_stock' => 2,
                'unit_cost' => 65.00,
                'supplier' => 'HVAC Supplies Inc.',
                'location' => 'Warehouse Rack D-3',
            ],

            // Electrical Parts
            [
                'code' => 'PRT-ELC-001',
                'name' => 'LED High Bay Light 200W',
                'description' => 'Industrial LED high bay fixture',
                'unit' => 'pcs',
                'stock_quantity' => 10,
                'minimum_stock' => 4,
                'unit_cost' => 185.00,
                'supplier' => 'Lighting Solutions Ltd.',
                'location' => 'Warehouse Rack E-1',
            ],
            [
                'code' => 'PRT-ELC-002',
                'name' => 'Emergency Exit Light',
                'description' => 'LED emergency exit sign with battery',
                'unit' => 'pcs',
                'stock_quantity' => 12,
                'minimum_stock' => 5,
                'unit_cost' => 45.00,
                'supplier' => 'Safety Equipment Co.',
                'location' => 'Warehouse Rack E-2',
            ],
            [
                'code' => 'PRT-ELC-003',
                'name' => 'Circuit Breaker 3P 100A',
                'description' => 'Three phase circuit breaker',
                'unit' => 'pcs',
                'stock_quantity' => 5,
                'minimum_stock' => 2,
                'unit_cost' => 125.00,
                'supplier' => 'Electrical Wholesale',
                'location' => 'Warehouse Rack E-3',
            ],
        ];

        foreach ($parts as $part) {
            Part::create($part);
        }
    }
}