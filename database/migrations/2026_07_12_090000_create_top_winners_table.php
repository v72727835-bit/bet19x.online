<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('top_winners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('avatar_url')->nullable();
            $table->string('contest_name');
            $table->decimal('amount_won', 12, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('top_winners');
    }
};
