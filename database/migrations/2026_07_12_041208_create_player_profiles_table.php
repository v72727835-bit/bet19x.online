<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('role', ['batsman', 'bowler', 'all_rounder', 'wicket_keeper']);
            $table->decimal('credit_value', 4, 1)->default(8.0);
            $table->decimal('points', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_profiles');
    }
};
