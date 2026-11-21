<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\DepositRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use Tests\TestCase;

class WalletTest extends TestCase
{
    private function user(float $dep = 0, float $win = 0): User
    {
        $u = User::create(['name' => 'U', 'email' => 'u' . uniqid() . '@t.com', 'password' => 'x']);
        Wallet::create(['user_id' => $u->id, 'deposit_balance' => $dep, 'winnings_balance' => $win]);
        return $u;
    }

    private function admin(string $role): Admin
    {
        return Admin::create(['name' => 'A', 'email' => $role . '@t.com', 'password' => 'secret123', 'role' => $role, 'is_active' => true]);
    }

    public function test_deposit_does_not_credit_wallet_until_admin_approves(): void
    {
        $u = $this->user();

        $this->actingAs($u)->post('/wallet/deposit', ['amount' => 500, 'payment_method' => 'upi'])
            ->assertRedirect(route('wallet.index'));

        $this->assertEquals(0, (float) Wallet::where('user_id', $u->id)->first()->deposit_balance);
        $dep = DepositRequest::firstOrFail();
        $this->assertSame('pending', $dep->status);

        $this->actingAs($this->admin('manager'), 'admin')
            ->post(route('admin.wallet.deposits.approve', $dep))->assertRedirect();

        $this->assertEquals(500, (float) Wallet::where('user_id', $u->id)->first()->deposit_balance);
        $this->assertSame('approved', $dep->fresh()->status);
    }

    public function test_deposit_cannot_be_approved_twice(): void
    {
        $u = $this->user();
        $dep = DepositRequest::create(['user_id' => $u->id, 'amount' => 300, 'method' => 'upi', 'status' => 'pending']);
        $admin = $this->admin('manager');

        $this->actingAs($admin, 'admin')->post(route('admin.wallet.deposits.approve', $dep));
        $this->actingAs($admin, 'admin')->post(route('admin.wallet.deposits.approve', $dep));

        $this->assertEquals(300, (float) Wallet::where('user_id', $u->id)->first()->deposit_balance);
    }

    public function test_rejected_deposit_adds_nothing(): void
    {
        $u = $this->user();
        $dep = DepositRequest::create(['user_id' => $u->id, 'amount' => 300, 'method' => 'upi', 'status' => 'pending']);

        $this->actingAs($this->admin('manager'), 'admin')
            ->post(route('admin.wallet.deposits.reject', $dep), ['admin_remarks' => 'no payment']);

        $this->assertEquals(0, (float) Wallet::where('user_id', $u->id)->first()->deposit_balance);
        $this->assertSame('rejected', $dep->fresh()->status);
    }

    public function test_withdraw_holds_winnings_and_creates_pending_request(): void
    {
        $u = $this->user(dep: 1000, win: 400);

        $this->actingAs($u)->post('/wallet/withdraw', ['amount' => 300, 'bank_details' => 'Name 12345678 IFSC0001 Bank'])
            ->assertRedirect(route('wallet.index'));

        $w = Wallet::where('user_id', $u->id)->first();
        $this->assertEquals(100, (float) $w->winnings_balance);
        $this->assertEquals(1000, (float) $w->deposit_balance);
        $this->assertSame('pending', WithdrawalRequest::firstOrFail()->status);
    }

    public function test_cannot_withdraw_more_than_winnings(): void
    {
        $u = $this->user(dep: 5000, win: 200);

        $this->actingAs($u)->post('/wallet/withdraw', ['amount' => 300, 'bank_details' => 'Name 12345678 IFSC0001 Bank'])
            ->assertSessionHas('error');

        $this->assertEquals(0, WithdrawalRequest::count());
        $this->assertEquals(200, (float) Wallet::where('user_id', $u->id)->first()->winnings_balance);
    }

    public function test_rejected_withdrawal_refunds_winnings(): void
    {
        $u = $this->user(win: 400);
        $this->actingAs($u)->post('/wallet/withdraw', ['amount' => 300, 'bank_details' => 'Name 12345678 IFSC0001 Bank']);
        $req = WithdrawalRequest::firstOrFail();

        $this->actingAs($this->admin('manager'), 'admin')
            ->post(route('admin.wallet.withdrawals.reject', $req), ['admin_remarks' => 'bad details']);

        $this->assertEquals(400, (float) Wallet::where('user_id', $u->id)->first()->winnings_balance);
        $this->assertSame('rejected', $req->fresh()->status);
    }

    public function test_approved_withdrawal_does_not_deduct_again(): void
    {
        $u = $this->user(win: 400);
        $this->actingAs($u)->post('/wallet/withdraw', ['amount' => 300, 'bank_details' => 'Name 12345678 IFSC0001 Bank']);
        $req = WithdrawalRequest::firstOrFail();

        $this->actingAs($this->admin('manager'), 'admin')->post(route('admin.wallet.withdrawals.approve', $req));

        $this->assertEquals(100, (float) Wallet::where('user_id', $u->id)->first()->winnings_balance);
        $this->assertSame('approved', $req->fresh()->status);
    }

    public function test_support_admin_cannot_approve_money_or_manage_admins(): void
    {
        $u = $this->user();
        $dep = DepositRequest::create(['user_id' => $u->id, 'amount' => 300, 'method' => 'upi', 'status' => 'pending']);
        $support = $this->admin('support');

        $this->actingAs($support, 'admin')->post(route('admin.wallet.deposits.approve', $dep))->assertForbidden();
        $this->actingAs($support, 'admin')->get(route('admin.admin-users.index'))->assertForbidden();
        $this->assertEquals(0, (float) Wallet::where('user_id', $u->id)->first()->deposit_balance);
    }

    public function test_manager_cannot_manage_admin_accounts(): void
    {
        $this->actingAs($this->admin('manager'), 'admin')->get(route('admin.admin-users.index'))->assertForbidden();
    }
}
