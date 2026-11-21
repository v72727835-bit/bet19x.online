<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['welcome', 'cashback', 'daily', 'referral', 'vip', 'other'])->default('other');
            $table->enum('value_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('value', 10, 2);
            $table->decimal('max_bonus', 10, 2)->nullable();
            $table->decimal('min_deposit', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonuses');
    }
};
