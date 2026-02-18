<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('super')->default(false)->after('is_active');
        });

        // Set admin@warehouse.com as super admin on localhost
        $appUrl = config('app.url');
        if (str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1') || str_contains($appUrl, 'warehouse-maintenance.test')) {
            DB::table('users')
                ->where('email', 'admin@warehouse.com')
                ->update(['super' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('super');
        });
    }
};
