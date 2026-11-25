<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Agent;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AgentTransferTest extends TestCase
{
    private function agent(string $name, string $role = 'master_agent', float $limit = 1000, float $used = 0, float $balance = 0, bool $active = true): Agent
    {
        return Agent::create(['name' => $name, 'email' => strtolower($name) . '@ag.com', 'password' => Hash::make('pass1234'), 'role' => $role,
            'commission_rate' => 10, 'credit_limit' => $limit, 'credit_used' => $used, 'wallet_balance' => $balance, 'is_active' => $active]);
    }

    private function admin(): Admin
    {
        return Admin::create(['name' => 'Boss', 'email' => uniqid() . '@admin.com', 'password' => 'secret123', 'role' => 'super_admin', 'is_active' => true]);
    }

    public function test_admin_page_opens_and_is_not_shadowed_by_agent_show_route(): void
    {
        $this->agent('Alpha');
        $this->actingAs($this->admin(), 'admin')->get('/admin/agents/transfer')->assertOk()->assertSee('Agent transfer')->assertSee('Alpha');
        $this->actingAs($this->admin(), 'admin')->get('/admin/agents')->assertOk()->assertSee(route('admin.agents.transfer'));
    }

    public function test_admin_moves_unused_credit_between_masters(): void
    {
        $a = $this->agent('Alpha', 'master_agent', 1000, 300);
        $b = $this->agent('Beta', 'master_agent', 500);

        $this->actingAs($this->admin(), 'admin')->post('/admin/agents/transfer', ['from_id' => $a->id, 'to_id' => $b->id, 'kind' => 'credit', 'amount' => 600, 'reason' => 'rebalance'])
            ->assertRedirect()->assertSessionHas('status');

        $this->assertEquals(400, (float) $a->fresh()->credit_limit);   // 1000 - 600 (used 300 stays)
        $this->assertEquals(1100, (float) $b->fresh()->credit_limit);
        $this->assertSame(1, AuditLog::where('module', 'Agent Transfer')->count());
        $this->actingAs($this->admin(), 'admin')->get('/admin/agents/transfer')->assertSee('rebalance')->assertSee('600.00');
    }

    public function test_cannot_move_used_credit_or_more_balance_than_exists(): void
    {
        $a = $this->agent('Alpha', 'master_agent', 1000, 900, 50);
        $b = $this->agent('Beta');

        $this->actingAs($this->admin(), 'admin')->post('/admin/agents/transfer', ['from_id' => $a->id, 'to_id' => $b->id, 'kind' => 'credit', 'amount' => 200, 'reason' => 'x'])
            ->assertSessionHasErrors('amount');
        $this->actingAs($this->admin(), 'admin')->post('/admin/agents/transfer', ['from_id' => $a->id, 'to_id' => $b->id, 'kind' => 'balance', 'amount' => 60, 'reason' => 'x'])
            ->assertSessionHasErrors('amount');
        $this->actingAs($this->admin(), 'admin')->post('/admin/agents/transfer', ['from_id' => $a->id, 'to_id' => $a->id, 'kind' => 'credit', 'amount' => 10, 'reason' => 'x'])
            ->assertSessionHasErrors('from_id');

        $this->assertEquals(1000, (float) $a->fresh()->credit_limit);
        $this->assertEquals(50, (float) $a->fresh()->wallet_balance);
        $this->assertSame(0, AuditLog::where('module', 'Agent Transfer')->count());
    }

    public function test_admin_moves_commission_balance_and_refuses_inactive_receiver(): void
    {
        $a = $this->agent('Alpha', 'master_agent', 0, 0, 250);
        $b = $this->agent('Beta');
        $off = $this->agent('Gamma', 'master_agent', 0, 0, 0, false);

        $this->actingAs($this->admin(), 'admin')->post('/admin/agents/transfer', ['from_id' => $a->id, 'to_id' => $b->id, 'kind' => 'balance', 'amount' => 100, 'reason' => 'pay'])->assertRedirect();
        $this->assertEquals(150, (float) $a->fresh()->wallet_balance);
        $this->assertEquals(100, (float) $b->fresh()->wallet_balance);

        $this->actingAs($this->admin(), 'admin')->post('/admin/agents/transfer', ['from_id' => $a->id, 'to_id' => $off->id, 'kind' => 'balance', 'amount' => 10, 'reason' => 'x'])->assertSessionHasErrors('amount');
        $this->assertEquals(0, (float) $off->fresh()->wallet_balance);
    }

    public function test_master_sends_to_another_agent_by_code_from_its_panel(): void
    {
        $a = $this->agent('Alpha', 'master_agent', 1000);
        $b = $this->agent('Beta', 'master_agent', 0);

        $this->actingAs($a, 'agent')->get('/agent/transfer')->assertOk()->assertSee('Send to an agent')->assertSee($a->referralCode());
        $this->actingAs($a, 'agent')->post('/agent/transfer-agent', ['code' => strtolower($b->referralCode()), 'kind' => 'credit', 'amount' => 250])
            ->assertRedirect()->assertSessionHas('status');

        $this->assertEquals(750, (float) $a->fresh()->credit_limit);
        $this->assertEquals(250, (float) $b->fresh()->credit_limit);
        $this->actingAs($b, 'agent')->get('/agent/transfer')->assertSee('Your agent transfers')->assertSee('+₹250.00', false);
    }

    public function test_distributor_and_retailer_can_send_too_in_every_direction(): void
    {
        $master = $this->agent('Alpha', 'master_agent', 1000);
        $dist = $this->agent('Dist', 'agent', 1000);
        $retail = $this->agent('Retail', 'sub_agent', 1000);

        $this->actingAs($dist, 'agent')->get('/agent/transfer')->assertOk()->assertSee('Send to an agent');
        $this->actingAs($retail, 'agent')->get('/agent/transfer')->assertOk()->assertSee('Send to an agent');

        $this->actingAs($dist, 'agent')->post('/agent/transfer-agent', ['code' => $master->referralCode(), 'kind' => 'credit', 'amount' => 100])->assertSessionHas('status');   // up
        $this->actingAs($master, 'agent')->post('/agent/transfer-agent', ['code' => $retail->referralCode(), 'kind' => 'credit', 'amount' => 50])->assertSessionHas('status');  // down, skipping a level
        $this->actingAs($retail, 'agent')->post('/agent/transfer-agent', ['code' => $dist->referralCode(), 'kind' => 'credit', 'amount' => 25])->assertSessionHas('status');    // sideways/up

        $this->assertEquals(1000 + 100 - 50, (float) $master->fresh()->credit_limit);   // got 100 from dist, sent 50 to retail
        $this->assertEquals(1000 - 100 + 25, (float) $dist->fresh()->credit_limit);     // sent 100 to master, got 25 from retail
        $this->assertEquals(1000 + 50 - 25, (float) $retail->fresh()->credit_limit);    // got 50 from master, sent 25 to dist
    }

    public function test_code_lookup_shows_who_will_receive(): void
    {
        $a = $this->agent('Alpha');
        $b = $this->agent('Beta', 'agent');
        $off = $this->agent('Gamma', 'master_agent', 0, 0, 0, false);

        $this->actingAs($a, 'agent')->getJson('/agent/transfer-agent/lookup?code=' . $b->referralCode())->assertJson(['found' => true, 'name' => 'Beta', 'type' => 'Agent']);
        $this->actingAs($a, 'agent')->getJson('/agent/transfer-agent/lookup?code=' . $a->referralCode())->assertJson(['found' => false]);   // own code
        $this->actingAs($a, 'agent')->getJson('/agent/transfer-agent/lookup?code=' . $off->referralCode())->assertJson(['found' => false]); // inactive
        $this->actingAs($a, 'agent')->getJson('/agent/transfer-agent/lookup?code=BXNOPE')->assertJson(['found' => false]);
    }

    public function test_bad_code_own_code_and_limits_are_refused_without_moving_money(): void
    {
        $a = $this->agent('Alpha', 'master_agent', 1000, 900);
        $b = $this->agent('Beta', 'agent', 0);

        $this->actingAs($a, 'agent')->post('/agent/transfer-agent', ['code' => 'BXZZZZ', 'kind' => 'credit', 'amount' => 10])->assertSessionHasErrors('code');
        $this->actingAs($a, 'agent')->post('/agent/transfer-agent', ['code' => $a->referralCode(), 'kind' => 'credit', 'amount' => 10])->assertSessionHasErrors('code');
        $this->actingAs($a, 'agent')->post('/agent/transfer-agent', ['code' => $b->referralCode(), 'kind' => 'credit', 'amount' => 200])->assertSessionHasErrors('amount'); // only 100 free

        $this->assertEquals(1000, (float) $a->fresh()->credit_limit);
        $this->assertEquals(0, (float) $b->fresh()->credit_limit);
        $this->assertSame(0, AuditLog::where('module', 'Agent Transfer')->count());
    }

    public function test_guest_cannot_transfer_or_look_up_agents(): void
    {
        $this->post('/agent/transfer-agent', ['code' => 'BX1', 'kind' => 'credit', 'amount' => 1])->assertRedirect(route('agent.login'));
        $this->getJson('/agent/transfer-agent/lookup?code=BX1')->assertUnauthorized();
    }

    public function test_admin_lists_show_each_agents_code(): void
    {
        $a = $this->agent('Alpha');
        $this->actingAs($this->admin(), 'admin')->get('/admin/agents')->assertOk()->assertSee($a->referralCode());
        $this->actingAs($this->admin(), 'admin')->get('/admin/agents/transfer')->assertOk()->assertSee($a->referralCode());
    }

    public function test_sub_admin_without_agents_permission_cannot_open_it(): void
    {
        $sub = Admin::create(['name' => 'S', 'email' => 's@admin.com', 'password' => 'secret123', 'role' => 'support', 'is_active' => true, 'permissions' => ['bets']]);
        $this->actingAs($sub, 'admin')->get('/admin/agents/transfer')->assertForbidden();
    }
}
