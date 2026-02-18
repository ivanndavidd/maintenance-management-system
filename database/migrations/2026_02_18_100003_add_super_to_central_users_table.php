<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            // Add super column to central DB users table
            $columns = DB::connection('central')->select("SHOW COLUMNS FROM users LIKE 'super'");
            if (empty($columns)) {
                DB::connection('central')->statement(
                    "ALTER TABLE users ADD COLUMN `super` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`"
                );
            }

            // Set admin@warehouse.com as super admin in central
            DB::connection('central')->table('users')
                ->where('email', 'admin@warehouse.com')
                ->update(['super' => true]);
        } catch (\Exception $e) {
            // Central DB might not be available
        }
    }

    public function down(): void
    {
        try {
            $columns = DB::connection('central')->select("SHOW COLUMNS FROM users LIKE 'super'");
            if (!empty($columns)) {
                DB::connection('central')->statement("ALTER TABLE users DROP COLUMN `super`");
            }
        } catch (\Exception $e) {
            // Central DB might not be available
        }
    }
};
