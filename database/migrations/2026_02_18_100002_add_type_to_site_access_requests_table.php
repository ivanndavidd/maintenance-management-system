<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_access_requests', function (Blueprint $table) {
            $table->string('type', 30)->default('site_access')->after('target_user_id');
            // Make site-related columns nullable since delete/toggle don't need them
            $table->json('requested_site_ids')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('site_access_requests', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->json('requested_site_ids')->nullable(false)->change();
        });
    }
};
