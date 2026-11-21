<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['audio', 'video']);
            $table->string('status', 30)->default('calling');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();
            $table->index('caller_id');
            $table->index('receiver_id');
        });
    }

    public function down(): void { Schema::dropIfExists('calls'); }
};
