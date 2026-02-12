<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetupCentralDatabase extends Command
{
    protected $signature = 'central:setup {--seed : Seed the central database with data from site databases}';

    protected $description = 'Create the central database, run migrations, and optionally seed data';

    public function handle(): int
    {
        $dbName = config('database.connections.central.database');
        $host = config('database.connections.central.host');
        $port = config('database.connections.central.port');
        $username = config('database.connections.central.username');
        $password = config('database.connections.central.password');

        // Step 1: Create database if it doesn't exist
        $this->info("Creating database '{$dbName}' if it doesn't exist...");

        try {
            $pdo = new \PDO(
                "mysql:host={$host};port={$port}",
                $username,
                $password
            );
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->info("Database '{$dbName}' ready.");
        } catch (\Exception $e) {
            $this->error("Failed to create database: {$e->getMessage()}");
            return self::FAILURE;
        }

        // Step 2: Run central migrations
        $this->info('Running central migrations...');
        $this->call('migrate', [
            '--path' => 'database/migrations/central',
            '--database' => 'central',
            '--force' => true,
        ]);

        // Step 3: Optionally seed
        if ($this->option('seed')) {
            $this->info('Seeding central database...');
            $this->call('db:seed', [
                '--class' => 'Database\\Seeders\\CentralDatabaseSeeder',
                '--force' => true,
            ]);
        }

        $this->info('Central database setup complete!');
        return self::SUCCESS;
    }
}
