<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['private', 'group'])->default('private');
            $table->string('name', 150)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->tinyInteger('is_deleted')->default(0);
        });
    }

    public function down(): void { Schema::dropIfExists('conversations'); }
};