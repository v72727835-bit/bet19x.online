<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Agent;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\AgentUserTransfer;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AgentUserTransferTest extends TestCase
{
    private function agent(string $name, string $role = 'master_agent', float $limit = 1000, float $used = 0, float $balance = 0, bool $active = true): Agent
    {
        return Agent::create(['name' => $name, 'email' => strtolower($name) . '@ag.com', 'password' => Hash::make('pass1234'), 'role' => $role,
            'commission_rate' => 10, 'credit_limit' => $limit, 'credit_used' => $used, 'wallet_balance' => $balance, 'is_active' => $active]);
    }

    private function user(string $name, float $dep = 0, float $win = 0, ?int $agentId = null, bool $blocked = false): User
    {
        $u = User::create(['name' => $name, 'email' => strtolower(str_replace(' ', '', $name)) . '@t.com', 'password' => 'x', 'agent_id' => $agentId, 'is_blocked' => $blocked]);
        Wallet::create(['user_id' => $u->id, 'deposit_balance' => $dep, 'winnings_balance' => $win]);

        return $u;
    }

    private function admin(): Admin
    {
        return Admin::create(['name' => 'Boss', 'email' => uniqid() . '@admin.com', 'password' => 'secret123', 'role' => 'super_admin', 'is_active' => true]);
    }

    private function wallet(User $u): Wallet
    {
        return Wallet::where('user_id', $u->id)->first();
    }

    // ---------- agent -> player ----------

    public function test_every_agent_type_sends_credit_to_any_player_even_outside_its_network(): void
    {
        $other = $this->agent('Other');
        $master = $this->agent('Alpha', 'master_agent', 1000);
        $dist = $this->agent('Dist', 'agent', 500);
        $retail = $this->agent('Retail', 'sub_agent', 300);
        $p = $this->user('Ravi Kumar', 0, 0, $other->id);   // belongs to someone else's network

        $this->actingAs($master, 'agent')->get('/agent/transfer')->assertOk()->assertSee('Send to a player');
        $this->actingAs($master, 'agent')->post('/agent/transfer-user', ['to' => $p->id, 'kind' => 'credit', 'amount' => 200])->assertRedirect()->assertSessionHas('status');
        $this->actingAs($dist, 'agent')->post('/agent/transfer-user', ['to' => 'ravikumar@t.com', 'kind' => 'credit', 'amount' => 100])->assertSessionHas('status');
        $this->actingAs($retail, 'agent')->post('/agent/transfer-user', ['to' => '#' . $p->id, 'kind' => 'credit', 'amount' => 50])->assertSessionHas('status');

        $this->assertEquals(800, (float) $master->fresh()->credit_limit);
        $this->assertEquals(400, (float) $dist->fresh()->credit_limit);
        $this->assertEquals(250, (float) $retail->fresh()->credit_limit);
        $this->assertEquals(350, (float) $this->wallet($p)->deposit_balance);
        $this->assertSame(3, WalletTransaction::where('type', 'transfer_in')->count());
        $this->assertStringContainsString('from agent Alpha', WalletTransaction::where('type', 'transfer_in')->orderBy('id')->value('description'));
        $this->assertSame(3, AuditLog::where('module', AgentUserTransfer::MODULE)->count());

        $this->actingAs($master, 'agent')->get('/agent/transfer')->assertSee('Your player transfers')->assertSee('−₹200.00', false);
    }

    public function test_agent_sends_commission_balance_to_player(): void
    {
        $a = $this->agent('Alpha', 'master_agent', 0, 0, 120);
        $p = $this->user('Bala');

        $this->actingAs($a, 'agent')->post('/agent/transfer-user', ['to' => $p->id, 'kind' => 'balance', 'amount' => 100])->assertSessionHas('status');
        $this->assertEquals(20, (float) $a->fresh()->wallet_balance);
        $this->assertEquals(100, (float) $this->wallet($p)->deposit_balance);
    }

    public function test_agent_to_player_refuses_used_credit_blocked_unknown_and_small_amounts(): void
    {
        $a = $this->agent('Alpha', 'master_agent', 1000, 900, 5);
        $p = $this->user('Bala');
        $blocked = $this->user('Bad Guy', 0, 0, null, true);

        $this->actingAs($a, 'agent')->post('/agent/transfer-user', ['to' => $p->id, 'kind' => 'credit', 'amount' => 150])->assertSessionHasErrors('to_amount');   // only 100 free
        $this->actingAs($a, 'agent')->post('/agent/transfer-user', ['to' => $p->id, 'kind' => 'balance', 'amount' => 10])->assertSessionHasErrors('to_amount');   // only 5
        $this->actingAs($a, 'agent')->post('/agent/transfer-user', ['to' => $blocked->id, 'kind' => 'credit', 'amount' => 10])->assertSessionHasErrors('to_amount');
        $this->actingAs($a, 'agent')->post('/agent/transfer-user', ['to' => 99999, 'kind' => 'credit', 'amount' => 10])->assertSessionHasErrors('to');
        $this->actingAs($a, 'agent')->post('/agent/transfer-user', ['to' => $p->id, 'kind' => 'credit', 'amount' => 5])->assertSessionHasErrors('amount');
        $this->actingAs($a, 'agent')->post('/agent/transfer-user', ['to' => $p->id, 'kind' => 'gold', 'amount' => 10])->assertSessionHasErrors('kind');

        $this->assertEquals(1000, (float) $a->fresh()->credit_limit);
        $this->assertEquals(5, (float) $a->fresh()->wallet_balance);
        $this->assertEquals(0, (float) $this->wallet($p)->deposit_balance);
        $this->assertSame(0, AuditLog::where('module', AgentUserTransfer::MODULE)->count());
    }

    public function test_agent_player_lookup_masks_the_name(): void
    {
        $a = $this->agent('Alpha');
        $p = $this->user('Ravi Kumar Sharma');

        $this->actingAs($a, 'agent')->getJson('/agent/transfer-user/lookup?to=' . $p->id)->assertJson(['found' => true, 'id' => $p->id, 'name' => 'Ravi K. S.', 'blocked' => false]);
        $this->actingAs($a, 'agent')->getJson('/agent/transfer-user/lookup?to=nobody@x.com')->assertJson(['found' => false]);
    }

    public function test_player_without_wallet_row_still_receives(): void
    {
        $a = $this->agent('Alpha');
        $p = User::create(['name' => 'Fresh', 'email' => 'fresh@t.com', 'password' => 'x']);

        $this->actingAs($a, 'agent')->post('/agent/transfer-user', ['to' => $p->id, 'kind' => 'credit', 'amount' => 30])->assertSessionHas('status');
        $this->assertEquals(30, (float) $this->wallet($p)->deposit_balance);
    }

    // ---------- player -> agent ----------

    public function test_player_sends_to_any_agent_by_code_and_agent_gets_credit(): void
    {
        $retail = $this->agent('Retail', 'sub_agent', 100);
        $p = $this->user('Bala', 50, 80);

        $this->actingAs($p)->get('/wallet/transfer')->assertOk()->assertSee('Send money to an agent')->assertSee('#' . $p->id);
        $this->actingAs($p)->getJson('/wallet/transfer-agent/lookup?code=' . $retail->referralCode())->assertJson(['found' => true, 'name' => 'Retail', 'type' => 'Sub-agent']);
        $this->actingAs($p)->post('/wallet/transfer-agent', ['code' => strtolower($retail->referralCode()), 'amount' => 100])->assertRedirect(route('wallet.index'))->assertSessionHas('status');

        $w = $this->wallet($p);
        $this->assertEquals(0, (float) $w->deposit_balance);   // deposit used first
        $this->assertEquals(30, (float) $w->winnings_balance);
        $this->assertEquals(200, (float) $retail->fresh()->credit_limit);
        $this->assertStringContainsString('to agent Retail', WalletTransaction::where('type', 'transfer_out')->value('description'));

        $this->actingAs($retail, 'agent')->get('/agent/transfer')->assertSee('Player → you')->assertSee('+₹100.00', false);
    }

    public function test_player_to_agent_refuses_overdraft_bad_code_and_inactive_agent(): void
    {
        $a = $this->agent('Alpha', 'master_agent', 0);
        $off = $this->agent('Gamma', 'master_agent', 0, 0, 0, false);
        $p = $this->user('Bala', 40);

        $this->actingAs($p)->post('/wallet/transfer-agent', ['code' => $a->referralCode(), 'amount' => 50])->assertSessionHas('error');
        $this->actingAs($p)->post('/wallet/transfer-agent', ['code' => 'BXZZZZ', 'amount' => 20])->assertSessionHas('error');
        $this->actingAs($p)->post('/wallet/transfer-agent', ['code' => $off->referralCode(), 'amount' => 20])->assertSessionHas('error');
        $this->actingAs($p)->getJson('/wallet/transfer-agent/lookup?code=' . $off->referralCode())->assertJson(['found' => false]);

        $this->assertEquals(40, (float) $this->wallet($p)->deposit_balance);
        $this->assertEquals(0, (float) $a->fresh()->credit_limit);
        $this->assertSame(0, WalletTransaction::count());
    }

    public function test_blocked_player_cannot_send_to_agent(): void
    {
        $a = $this->agent('Alpha', 'master_agent', 0);
        $p = $this->user('Bala', 100, 0, null, true);

        $this->actingAs($p)->post('/wallet/transfer-agent', ['code' => $a->referralCode(), 'amount' => 20])->assertSessionHas('error');
        $this->assertEquals(100, (float) $this->wallet($p)->deposit_balance);
    }

    public function test_guests_cannot_use_the_new_routes(): void
    {
        $this->post('/wallet/transfer-agent', ['code' => 'BX1', 'amount' => 20])->assertRedirect(route('login'));
        $this->post('/agent/transfer-user', ['to' => 1, 'kind' => 'credit', 'amount' => 20])->assertRedirect(route('agent.login'));
        $this->getJson('/agent/transfer-user/lookup?to=1')->assertUnauthorized();
    }

    // ---------- admin ----------

    public function test_admin_moves_money_both_ways_between_any_agent_and_player(): void
    {
        $a = $this->agent('Alpha', 'agent', 1000, 0, 300);
        $p = $this->user('Bala', 200);

        $this->actingAs($this->admin(), 'admin')->get('/admin/agents/transfer')->assertOk()->assertSee('Agent ↔ player transfer');

        $this->actingAs($this->admin(), 'admin')->post('/admin/agents/transfer-user', ['direction' => 'agent_to_user', 'agent_id' => $a->id, 'user' => $p->id, 'kind' => 'credit', 'amount' => 400, 'reason' => 'top-up'])
            ->assertRedirect()->assertSessionHas('status');
        $this->assertEquals(600, (float) $a->fresh()->credit_limit);
        $this->assertEquals(600, (float) $this->wallet($p)->deposit_balance);
        $this->assertStringContainsString('by admin Boss', WalletTransaction::where('type', 'transfer_in')->value('description'));

        $this->actingAs($this->admin(), 'admin')->post('/admin/agents/transfer-user', ['direction' => 'user_to_agent', 'agent_id' => $a->id, 'user' => 'bala@t.com', 'kind' => 'balance', 'amount' => 150, 'reason' => 'settle'])
            ->assertSessionHas('status');
        $this->assertEquals(450, (float) $a->fresh()->wallet_balance);
        $this->assertEquals(450, (float) $this->wallet($p)->deposit_balance);

        $this->actingAs($this->admin(), 'admin')->get('/admin/agents/transfer')->assertSee('top-up')->assertSee('settle')->assertSee('400.00');
    }

    public function test_admin_errors_leave_balances_unchanged(): void
    {
        $a = $this->agent('Alpha', 'agent', 100);
        $p = $this->user('Bala', 10);

        $this->actingAs($this->admin(), 'admin')->post('/admin/agents/transfer-user', ['direction' => 'agent_to_user', 'agent_id' => $a->id, 'user' => $p->id, 'kind' => 'credit', 'amount' => 500, 'reason' => 'x'])->assertSessionHasErrors('user_amount');
        $this->actingAs($this->admin(), 'admin')->post('/admin/agents/transfer-user', ['direction' => 'user_to_agent', 'agent_id' => $a->id, 'user' => $p->id, 'kind' => 'credit', 'amount' => 50, 'reason' => 'x'])->assertSessionHasErrors('user_amount');
        $this->actingAs($this->admin(), 'admin')->post('/admin/agents/transfer-user', ['direction' => 'agent_to_user', 'agent_id' => 9999, 'user' => $p->id, 'kind' => 'credit', 'amount' => 50, 'reason' => 'x'])->assertSessionHasErrors('agent_id');
        $this->actingAs($this->admin(), 'admin')->post('/admin/agents/transfer-user', ['direction' => 'agent_to_user', 'agent_id' => $a->id, 'user' => 'ghost', 'kind' => 'credit', 'amount' => 50, 'reason' => 'x'])->assertSessionHasErrors('user');

        $this->assertEquals(100, (float) $a->fresh()->credit_limit);
        $this->assertEquals(10, (float) $this->wallet($p)->deposit_balance);
    }

    public function test_sub_admin_without_agents_permission_cannot_transfer(): void
    {
        $sub = Admin::create(['name' => 'S', 'email' => 's@admin.com', 'password' => 'secret123', 'role' => 'support', 'is_active' => true, 'permissions' => ['bets']]);
        $this->actingAs($sub, 'admin')->post('/admin/agents/transfer-user', ['direction' => 'agent_to_user', 'agent_id' => 1, 'user' => 1, 'kind' => 'credit', 'amount' => 50, 'reason' => 'x'])->assertForbidden();
    }
}
