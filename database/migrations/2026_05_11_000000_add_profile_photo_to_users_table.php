<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'site';

    public function up(): void
    {
        Schema::connection('site')->table('users', function (Blueprint $table) {
            $table->string('profile_photo')->nullable()->after('employee_id');
        });
    }

    public function down(): void
    {
        Schema::connection('site')->table('users', function (Blueprint $table) {
            $table->dropColumn('profile_photo');
        });
    }
};
