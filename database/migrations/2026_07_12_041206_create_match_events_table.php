<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id')->constrained()->cascadeOnDelete();
            $table->string('team_a_name');
            $table->string('team_b_name');
            $table->dateTime('start_time');
            $table->dateTime('lineup_lock_time');
            $table->string('venue')->nullable();
            $table->enum('status', ['upcoming', 'live', 'completed', 'cancelled'])->default('upcoming');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_events');
    }
};
