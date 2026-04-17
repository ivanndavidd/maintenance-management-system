<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection("site")->create("sparepart_usages", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("sparepart_id");
            $table->integer("quantity_used");
            $table->date("used_at");
            $table->text("notes")->nullable();
            $table->unsignedBigInteger("used_by")->nullable();
            $table->timestamps();
            $table->foreign("sparepart_id")->references("id")->on("spareparts")->onDelete("cascade");
            $table->foreign("used_by")->references("id")->on("users")->onDelete("set null");
        });
    }

    public function down(): void
    {
        Schema::connection("site")->dropIfExists("sparepart_usages");
    }
};
