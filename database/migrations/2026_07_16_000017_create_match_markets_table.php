<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_markets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_event_id')->constrained('match_events')->cascadeOnDelete();
            $table->enum('market_type', ['match_winner', 'toss', 'fancy', 'session', 'custom'])->default('match_winner');
            $table->string('name'); // e.g. "Match Winner", "Team A 10 Over Runs"
            $table->string('selection_a')->nullable(); // e.g. team/side name for winner markets
            $table->string('selection_b')->nullable();
            $table->decimal('odds_back', 8, 2)->nullable();
            $table->decimal('odds_lay', 8, 2)->nullable();
            $table->integer('session_target')->nullable(); // for fancy/session runs-based markets
            $table->decimal('session_yes_rate', 6, 2)->nullable();
            $table->decimal('session_no_rate', 6, 2)->nullable();
            $table->enum('status', ['active', 'suspended', 'closed'])->default('active');
            $table->boolean('is_manual_override')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_markets');
    }
};
