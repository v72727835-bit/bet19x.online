<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_team_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_profile_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_captain')->default(false);
            $table->boolean('is_vice_captain')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_team_players');
    }
};
