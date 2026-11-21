<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('featured_contests', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('prize_text')->nullable();
            $table->string('link_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_contests');
    }
};
