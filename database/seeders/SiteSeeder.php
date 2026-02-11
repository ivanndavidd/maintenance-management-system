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
                'code' => 'marunda',
                'name' => 'Warehouse Marunda',
                'database_name' => 'warehouse_maintenance',
                'description' => 'Warehouse Marunda Facility',
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
