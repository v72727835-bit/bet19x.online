<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection((new \App\Models\Wallet)->getConnectionName())->create('astech_bet_logs', function (Blueprint $t) {
            $t->id();
            $t->string('transaction_id', 191)->unique();
            $t->string('user_id', 50)->index();
            $t->string('game_id', 120)->nullable();
            $t->string('round_id', 191)->nullable();
            $t->decimal('bet', 12, 2)->default(0);
            $t->decimal('win', 12, 2)->default(0);
            $t->decimal('from_deposit', 12, 2)->default(0);
            $t->decimal('from_winnings', 12, 2)->default(0);
            $t->decimal('balance_before', 12, 2)->default(0);
            $t->decimal('balance_after', 12, 2)->default(0);
            $t->string('currency', 10)->default('INR');
            $t->boolean('rolled_back')->default(false);
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::connection((new \App\Models\Wallet)->getConnectionName())->dropIfExists('astech_bet_logs');
    }
};
