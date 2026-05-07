<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection('site')->statement("ALTER TABLE tool_usage_requests MODIFY COLUMN status ENUM('pending','approved','rejected','in_use','used','returned','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::connection('site')->statement("ALTER TABLE tool_usage_requests MODIFY COLUMN status ENUM('pending','approved','rejected','in_use','returned','cancelled') NOT NULL DEFAULT 'pending'");
    }
};
