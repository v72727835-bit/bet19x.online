<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('entry_fee', 10, 2)->default(0);
            $table->decimal('total_prize_pool', 12, 2)->default(0);
            $table->unsignedInteger('max_participants');
            $table->unsignedInteger('current_participants')->default(0);
            $table->enum('status', ['open', 'full', 'locked', 'completed', 'cancelled'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contests');
    }
};
