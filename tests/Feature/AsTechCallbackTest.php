<?php

namespace Tests\Feature;

use App\Models\Wallet;
use Tests\TestCase;

class AsTechCallbackTest extends TestCase
{
    private function cb(array $payload, ?string $sig = null)
    {
        $raw = json_encode($payload);
        $sig ??= hash_hmac('sha256', $raw, 'test-secret');

        return $this->call('POST', '/api/astech/callback', [], [], [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_SIGNATURE' => $sig], $raw);
    }

    private function wallet(float $dep = 100, float $win = 50): void
    {
        Wallet::create(['user_id' => 7, 'deposit_balance' => $dep, 'winnings_balance' => $win]);
    }

    public function test_bad_signature_is_rejected(): void
    {
        $this->wallet();
        $this->cb(['cmd' => 'getBalance', 'user_login' => '7'], 'deadbeef')->assertJson(['status' => 'fail']);
    }

    public function test_get_balance(): void
    {
        $this->wallet();
        $this->cb(['cmd' => 'getBalance', 'user_login' => '7'])
            ->assertJson(['status' => 'success', 'content' => ['cash' => '150.00']]);
    }

    public function test_write_bet_debits_deposit_first_and_is_idempotent(): void
    {
        $this->wallet();
        $p = ['cmd' => 'writeBet', 'user_login' => '7', 'transaction_id' => 't1', 'bet' => 120, 'win' => 0, 'currency' => 'INR'];

        $this->cb($p)->assertJson(['status' => 'success', 'content' => ['cash' => '30.00']]);
        $this->cb($p)->assertJson(['status' => 'success', 'content' => ['cash' => '30.00']]);

        $w = Wallet::where('user_id', 7)->first();
        $this->assertEquals(0, (float) $w->deposit_balance);
        $this->assertEquals(30, (float) $w->winnings_balance);
    }

    public function test_insufficient_funds(): void
    {
        $this->wallet(10, 0);
        $this->cb(['cmd' => 'writeBet', 'user_login' => '7', 'transaction_id' => 't2', 'bet' => 50, 'win' => 0])
            ->assertJson(['status' => 'fail', 'error' => 'INSUFFICIENT_FUNDS']);
    }

    public function test_negative_amounts_are_rejected(): void
    {
        $this->wallet();
        $this->cb(['cmd' => 'writeBet', 'user_login' => '7', 'transaction_id' => 't3', 'bet' => -50, 'win' => 0])
            ->assertJson(['status' => 'fail']);
        $this->cb(['cmd' => 'writeBet', 'user_login' => '7', 'transaction_id' => 't4', 'bet' => 0, 'win' => -5])
            ->assertJson(['status' => 'fail']);
        $this->assertEquals(150, (float) Wallet::where('user_id', 7)->first()->deposit_balance + (float) Wallet::where('user_id', 7)->first()->winnings_balance);
    }

    public function test_currency_mismatch_is_rejected(): void
    {
        $this->wallet();
        $this->cb(['cmd' => 'writeBet', 'user_login' => '7', 'transaction_id' => 't5', 'bet' => 10, 'win' => 0, 'currency' => 'USD'])
            ->assertJson(['status' => 'fail', 'error' => 'Currency mismatch']);
    }

    public function test_rollback_restores_balance_once(): void
    {
        $this->wallet();
        $this->cb(['cmd' => 'writeBet', 'user_login' => '7', 'transaction_id' => 't6', 'bet' => 100, 'win' => 0]);
        $rb = ['cmd' => 'rollback', 'user_login' => '7', 'transaction_id' => 't6'];
        $this->cb($rb)->assertJson(['content' => ['cash' => '150.00']]);
        $this->cb($rb)->assertJson(['content' => ['cash' => '150.00']]);
    }
}
