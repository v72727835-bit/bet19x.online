<?php

namespace Tests;

use App\Http\Middleware\CheckBlockedIp;
use App\Http\Middleware\CheckLicense;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // All product models use the "tenant" connection. In tests point it (and the
        // default connection) at one in-memory sqlite database.
        config([
            'database.default' => 'tenant',
            'database.connections.tenant' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
            ],
            'astech.secret' => 'test-secret',
            'astech.currency' => 'INR',
        ]);
        app('db')->purge('tenant');

        $this->withoutMiddleware([ResolveTenant::class, CheckBlockedIp::class, CheckLicense::class]);
        $this->createSchema();
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email')->unique(); $t->string('phone')->nullable();
            $t->timestamp('email_verified_at')->nullable(); $t->string('password'); $t->rememberToken();
            $t->boolean('is_blocked')->default(false); $t->timestamps();
        });
        Schema::create('admins', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email')->unique(); $t->string('password');
            $t->string('role')->default('support'); $t->boolean('is_active')->default(true);
            $t->timestamp('email_verified_at')->nullable(); $t->rememberToken(); $t->timestamps();
        });
        Schema::create('wallets', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id')->unique();
            $t->decimal('deposit_balance', 10, 2)->default(0);
            $t->decimal('winnings_balance', 10, 2)->default(0);
            $t->decimal('bonus_balance', 10, 2)->default(0); $t->timestamps();
        });
        Schema::create('wallet_transactions', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('wallet_id'); $t->string('type'); $t->decimal('amount', 10, 2);
            $t->string('description')->nullable(); $t->string('reference_id')->nullable(); $t->timestamps();
        });
        Schema::create('deposit_requests', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->decimal('amount', 10, 2);
            $t->string('method')->default('manual'); $t->string('reference_note')->nullable();
            $t->string('status')->default('pending'); $t->text('admin_remarks')->nullable();
            $t->unsignedBigInteger('processed_by')->nullable(); $t->timestamp('processed_at')->nullable(); $t->timestamps();
        });
        Schema::create('withdrawal_requests', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->decimal('amount', 10, 2);
            $t->string('account_details')->nullable(); $t->string('status')->default('pending');
            $t->text('admin_remarks')->nullable(); $t->unsignedBigInteger('processed_by')->nullable();
            $t->timestamp('processed_at')->nullable(); $t->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('admin_id')->nullable(); $t->string('module'); $t->string('action');
            $t->text('description')->nullable(); $t->string('ip_address')->nullable(); $t->timestamps();
        });
        Schema::create('astech_bet_logs', function (Blueprint $t) {
            $t->id(); $t->string('transaction_id', 191)->unique(); $t->string('user_id', 50)->index();
            $t->string('game_id', 120)->nullable(); $t->string('round_id', 191)->nullable();
            $t->decimal('bet', 12, 2)->default(0); $t->decimal('win', 12, 2)->default(0);
            $t->decimal('from_deposit', 12, 2)->default(0); $t->decimal('from_winnings', 12, 2)->default(0);
            $t->decimal('balance_before', 12, 2)->default(0); $t->decimal('balance_after', 12, 2)->default(0);
            $t->string('currency', 10)->default('INR'); $t->boolean('rolled_back')->default(false); $t->timestamps();
        });
    }
}
