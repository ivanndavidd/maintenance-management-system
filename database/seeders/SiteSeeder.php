<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sites = [
            [
                'code' => 'site_a',
                'name' => 'Warehouse Site A',
                'database_name' => 'warehouse_site_a',
                'description' => 'Main warehouse facility',
                'is_active' => true,
            ],
            [
                'code' => 'site_b',
                'name' => 'Warehouse Site B',
                'database_name' => 'warehouse_site_b',
                'description' => 'Secondary warehouse facility',
                'is_active' => true,
            ],
        ];

        foreach ($sites as $site) {
            Site::on('central')->updateOrCreate(
                ['code' => $site['code']],
                $site
            );
        }

        $this->command->info('Sites seeded successfully!');
    }
}
