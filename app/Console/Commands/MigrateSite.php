<?php

namespace App\Console\Commands;

use App\Models\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateSite extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'site:migrate {site_code? : The site code to migrate} {--all : Migrate all sites} {--seed : Run seeders after migration} {--fresh : Drop all tables and re-run migrations}';

    /**
     * The console command description.
     */
    protected $description = 'Run migrations for a specific site database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            $sites = Site::on('central')->active()->get();

            if ($sites->isEmpty()) {
                $this->error('No active sites found.');
                return 1;
            }

            foreach ($sites as $site) {
                $this->migrateSite($site);
            }

            $this->info('All sites migrated successfully!');
            return 0;
        }

        $siteCode = $this->argument('site_code');

        if (!$siteCode) {
            $this->error('Please provide a site code or use --all flag.');
            return 1;
        }

        $site = Site::on('central')->where('code', $siteCode)->first();

        if (!$site) {
            $this->error("Site with code '{$siteCode}' not found.");
            return 1;
        }

        $this->migrateSite($site);

        return 0;
    }

    /**
     * Migrate a specific site
     */
    protected function migrateSite(Site $site): void
    {
        $this->info("Migrating site: {$site->name} ({$site->database_name})...");

        // Configure the site connection
        Config::set('database.connections.site.database', $site->database_name);
        DB::purge('site');
        DB::reconnect('site');

        // Run migrations
        $command = $this->option('fresh') ? 'migrate:fresh' : 'migrate';

        Artisan::call($command, [
            '--database' => 'site',
            '--force' => true,
        ]);

        $this->line(Artisan::output());

        // Run seeders if requested
        if ($this->option('seed')) {
            $this->info("Seeding site: {$site->name}...");

            Artisan::call('db:seed', [
                '--database' => 'site',
                '--force' => true,
            ]);

            $this->line(Artisan::output());
        }

        $this->info("Site '{$site->name}' migrated successfully!");
    }
}
