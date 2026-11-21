<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->enum('role', ['master_agent', 'agent', 'sub_agent'])->default('agent');
            $table->decimal('commission_rate', 5, 2)->default(0); // % of net revenue from downline
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('credit_used', 12, 2)->default(0);
            $table->decimal('wallet_balance', 12, 2)->default(0); // commission payable balance
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
