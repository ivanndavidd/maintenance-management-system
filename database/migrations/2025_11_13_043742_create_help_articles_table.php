<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('help_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('category', ['faq', 'sop', 'tutorial', 'documentation'])->default('faq');
            $table->longText('content'); // Changed to longText to support HTML and base64 images
            $table->string('icon')->nullable(); // FontAwesome icon class
            $table->integer('order')->default(0); // For sorting
            $table->boolean('is_published')->default(true);
            $table->integer('view_count')->default(0);
            $table->timestamps();

            $table->index('category');
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_articles');
    }
};
