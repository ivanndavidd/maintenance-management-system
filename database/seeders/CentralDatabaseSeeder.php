<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CentralDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding central database (warehouse_central)...');

        // 1. Copy sites from main DB to central DB
        $this->seedSites();

        // 2. Seed roles to central DB
        $this->seedRoles();

        // 3. Copy distinct users from all site DBs to central DB + create site_user pivot
        $this->seedUsersAndPivot();

        $this->command->info('Central database seeding complete!');
    }

    protected function seedSites(): void
    {
        $sites = DB::connection('mysql')->table('sites')->get();

        foreach ($sites as $site) {
            DB::connection('central')->table('sites')->updateOrInsert(
                ['code' => $site->code],
                [
                    'name' => $site->name,
                    'database_name' => $site->database_name,
                    'description' => $site->description,
                    'logo' => $site->logo,
                    'is_active' => $site->is_active,
                    'created_at' => $site->created_at,
                    'updated_at' => $site->updated_at,
                ]
            );
        }

        $count = DB::connection('central')->table('sites')->count();
        $this->command->info("  Sites: {$count} records");
    }

    protected function seedRoles(): void
    {
        $roles = ['admin', 'supervisor_maintenance', 'staff_maintenance', 'pic'];

        foreach ($roles as $roleName) {
            DB::connection('central')->table('roles')->updateOrInsert(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $count = DB::connection('central')->table('roles')->count();
        $this->command->info("  Roles: {$count} records");
    }

    protected function seedUsersAndPivot(): void
    {
        $sites = DB::connection('central')->table('sites')->get();
        $centralUsers = []; // email => central_user_id

        foreach ($sites as $site) {
            $this->command->info("  Processing site: {$site->name} ({$site->database_name})");

            try {
                // Configure temp connection for this site
                config([
                    'database.connections.seed_check.driver' => 'mysql',
                    'database.connections.seed_check.host' => config('database.connections.mysql.host'),
                    'database.connections.seed_check.port' => config('database.connections.mysql.port'),
                    'database.connections.seed_check.database' => $site->database_name,
                    'database.connections.seed_check.username' => config('database.connections.mysql.username'),
                    'database.connections.seed_check.password' => config('database.connections.mysql.password'),
                    'database.connections.seed_check.charset' => 'utf8mb4',
                    'database.connections.seed_check.collation' => 'utf8mb4_unicode_ci',
                ]);
                DB::purge('seed_check');
                DB::connection('seed_check')->getPdo(); // test connection

                // Only get users with admin role from this site DB
                $siteUsers = DB::connection('seed_check')->table('users')
                    ->join('model_has_roles', function ($join) {
                        $join->on('users.id', '=', 'model_has_roles.model_id')
                            ->where('model_has_roles.model_type', 'App\\Models\\User');
                    })
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->where('roles.name', 'admin')
                    ->select('users.*')
                    ->distinct()
                    ->get();

                foreach ($siteUsers as $user) {
                    $roles = ['admin'];

                    // Insert/update user in central DB (dedup by email)
                    if (!isset($centralUsers[$user->email])) {
                        $centralUserId = DB::connection('central')->table('users')
                            ->where('email', $user->email)
                            ->value('id');

                        if (!$centralUserId) {
                            $centralUserId = DB::connection('central')->table('users')->insertGetId([
                                'employee_id' => $user->employee_id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'phone' => $user->phone ?? null,
                                'email_verified_at' => $user->email_verified_at ?? null,
                                'password' => $user->password,
                                'is_active' => $user->is_active ?? true,
                                'super' => $user->super ?? false,
                                'last_login_at' => $user->last_login_at ?? null,
                                'remember_token' => $user->remember_token ?? null,
                                'created_at' => $user->created_at,
                                'updated_at' => $user->updated_at,
                            ]);
                        } else {
                            // If user has super flag in any site, propagate to central
                            if (!empty($user->super)) {
                                DB::connection('central')->table('users')
                                    ->where('id', $centralUserId)
                                    ->update(['super' => true]);
                            }
                        }

                        $centralUsers[$user->email] = $centralUserId;

                        // Assign roles in central DB
                        foreach ($roles as $roleName) {
                            $roleId = DB::connection('central')->table('roles')
                                ->where('name', $roleName)
                                ->where('guard_name', 'web')
                                ->value('id');

                            if ($roleId) {
                                DB::connection('central')->table('model_has_roles')->updateOrInsert(
                                    [
                                        'role_id' => $roleId,
                                        'model_type' => 'App\\Models\\User',
                                        'model_id' => $centralUserId,
                                    ]
                                );
                            }
                        }
                    }

                    // Create site_user pivot
                    $centralUserId = $centralUsers[$user->email];
                    DB::connection('central')->table('site_user')->updateOrInsert(
                        ['user_id' => $centralUserId, 'site_id' => $site->id],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }

                $this->command->info("    -> {$siteUsers->count()} admin users processed");
            } catch (\Exception $e) {
                $this->command->error("    -> Error: {$e->getMessage()}");
            }
        }

        $totalUsers = DB::connection('central')->table('users')->count();
        $totalPivots = DB::connection('central')->table('site_user')->count();
        $this->command->info("  Central users: {$totalUsers}, Site-user mappings: {$totalPivots}");
    }
}
