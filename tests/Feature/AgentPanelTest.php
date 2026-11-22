<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Agent;
use App\Models\AstechBetLog;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AgentPanelTest extends TestCase
{
    private function agent(string $role, ?int $parent = null, string $pass = 'agentpass1', bool $active = true): Agent
    {
        return Agent::create(['name' => ucfirst($role) . uniqid(), 'email' => uniqid() . '@agent.com', 'password' => Hash::make($pass),
            'role' => $role, 'parent_id' => $parent, 'commission_rate' => 10, 'credit_limit' => 1000, 'is_active' => $active]);
    }

    private function player(?Agent $agent, string $name): User
    {
        $u = User::create(['name' => $name, 'email' => uniqid() . '@p.com', 'password' => 'x', 'agent_id' => $agent?->id]);
        Wallet::create(['user_id' => $u->id, 'deposit_balance' => 50]);
        return $u;
    }

    private function admin(): Admin
    {
        return Admin::create(['name' => 'A', 'email' => uniqid() . '@admin.com', 'password' => 'secret123', 'role' => 'super_admin', 'is_active' => true]);
    }

    public function test_agent_can_log_in_with_correct_password(): void
    {
        $a = $this->agent('master_agent');
        $this->post('/agent/login', ['email' => $a->email, 'password' => 'agentpass1'])->assertRedirect(route('agent.dashboard'));
        $this->assertAuthenticatedAs($a, 'agent');
        $this->get('/agent/dashboard')->assertOk()->assertSee($a->name);
    }

    public function test_login_is_case_insensitive_for_email(): void
    {
        $a = $this->agent('agent');
        $this->post('/agent/login', ['email' => strtoupper($a->email), 'password' => 'agentpass1'])->assertRedirect(route('agent.dashboard'));
    }

    public function test_wrong_password_and_inactive_agent_are_refused(): void
    {
        $a = $this->agent('agent');
        $this->post('/agent/login', ['email' => $a->email, 'password' => 'nope'])->assertSessionHasErrors('email');
        $this->assertGuest('agent');

        $off = $this->agent('agent', null, 'agentpass1', false);
        $this->post('/agent/login', ['email' => $off->email, 'password' => 'agentpass1'])->assertSessionHasErrors('email');
        $this->assertGuest('agent');
    }

    public function test_guest_is_sent_to_agent_login(): void
    {
        $this->get('/agent/dashboard')->assertRedirect(route('agent.login'));
    }

    public function test_each_agent_only_sees_its_own_network(): void
    {
        $master = $this->agent('master_agent');
        $agent = $this->agent('agent', $master->id);
        $sub = $this->agent('sub_agent', $agent->id);
        $other = $this->agent('master_agent');

        $this->player($master, 'Player Master');
        $this->player($agent, 'Player Agent');
        $pSub = $this->player($sub, 'Player Sub');
        $this->player($other, 'Player Other');
        AstechBetLog::create(['transaction_id' => 't1', 'user_id' => (string) $pSub->id, 'game_id' => 'g', 'bet' => 100, 'win' => 40]);

        $this->actingAs($master, 'agent')->get('/agent/players')->assertOk()
            ->assertSee('Player Master')->assertSee('Player Agent')->assertSee('Player Sub')->assertDontSee('Player Other');
        $this->actingAs($master, 'agent')->get('/agent/agents')->assertOk()->assertSee($agent->name)->assertSee($sub->name)->assertDontSee($other->name);

        $this->actingAs($sub, 'agent')->get('/agent/players')->assertOk()
            ->assertSee('Player Sub')->assertDontSee('Player Agent')->assertDontSee('Player Master')->assertDontSee('Player Other');
    }

    public function test_admin_can_login_as_agent_and_return(): void
    {
        $a = $this->agent('agent');
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->post("/admin/agents/{$a->id}/login-as")->assertRedirect(route('agent.dashboard'));
        $this->assertAuthenticatedAs($a, 'agent');
        $this->get('/agent/dashboard')->assertOk()->assertSee('Back to admin panel');

        $this->post('/agent/logout')->assertRedirect(route('admin.agents.show', $a->id));
        $this->assertGuest('agent');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_edits_agent_and_sets_password_then_agent_logs_in(): void
    {
        $master = $this->agent('master_agent');
        $a = $this->agent('agent');

        $this->actingAs($this->admin(), 'admin')->get("/admin/agents/{$a->id}/edit")->assertOk();
        $this->actingAs($this->admin(), 'admin')->put("/admin/agents/{$a->id}", [
            'name' => 'Renamed', 'email' => 'New@Agent.com', 'phone' => '1', 'role' => 'sub_agent', 'parent_id' => $master->id,
            'commission_rate' => 5, 'credit_limit' => 200, 'new_password' => 'fresh123',
        ])->assertRedirect(route('admin.agents.show', $a));

        $a->refresh();
        $this->assertSame('new@agent.com', $a->email);
        $this->assertSame($master->id, $a->parent_id);
        $this->assertSame('sub_agent', $a->role);

        $this->post('/agent/login', ['email' => 'new@agent.com', 'password' => 'fresh123'])->assertRedirect(route('agent.dashboard'));
    }

    public function test_agent_cannot_be_placed_under_its_own_downline(): void
    {
        $master = $this->agent('master_agent');
        $child = $this->agent('agent', $master->id);

        $this->actingAs($this->admin(), 'admin')->put("/admin/agents/{$master->id}", [
            'name' => $master->name, 'commission_rate' => 1, 'credit_limit' => 1, 'parent_id' => $child->id,
        ])->assertSessionHasErrors('parent_id');
        $this->assertNull($master->fresh()->parent_id);
    }

    public function test_admin_can_delete_agent_without_downline(): void
    {
        $a = $this->agent('sub_agent');
        $this->actingAs($this->admin(), 'admin')->delete("/admin/agents/{$a->id}/delete")->assertRedirect(route('admin.agents.index'));
        $this->assertNull(Agent::find($a->id));
    }

    public function test_agents_page_shows_actions_and_login_link(): void
    {
        $a = $this->agent('agent');
        $this->actingAs($this->admin(), 'admin')->get('/admin/agents')->assertOk()
            ->assertSee(route('agent.login'))->assertSee(route('admin.agents.edit', $a))->assertSee(route('admin.agents.login-as', $a));
    }

    public function test_referral_link_signup_joins_the_agent(): void
    {
        $a = $this->agent('sub_agent');
        $this->get('/r/' . $a->referralCode())->assertRedirect(route('register', ['ref' => $a->referralCode()]));
        $this->get('/register?ref=' . $a->referralCode())->assertOk()->assertSee('Invited by ' . $a->name);

        $this->post('/register', ['name' => 'New Guy', 'email' => 'newguy@t.com', 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'ref' => $a->referralCode()]);
        $this->assertSame($a->id, User::where('email', 'newguy@t.com')->value('agent_id'));
    }

    public function test_bad_or_inactive_referral_code_is_ignored(): void
    {
        $off = $this->agent('agent', null, 'agentpass1', false);
        $this->assertNull(Agent::findByReferralCode($off->referralCode()));
        $this->assertNull(Agent::findByReferralCode('BXZZZZ'));
        $this->get('/r/NOPE')->assertRedirect(route('register'));
    }

    public function test_share_is_commission_percent_of_players_win_or_loss(): void
    {
        $a = $this->agent('agent'); // 10%
        $p = $this->player($a, 'Loser');
        AstechBetLog::create(['transaction_id' => 'l1', 'user_id' => (string) $p->id, 'game_id' => 'g', 'bet' => 1000, 'win' => 200]);
        // players lost 800 -> agent share +80
        $this->actingAs($a, 'agent')->get('/agent/dashboard?range=all')->assertOk()->assertSee('₹80.00')->assertSee('10%');

        $b = $this->agent('agent');
        $w = $this->player($b, 'Winner');
        AstechBetLog::create(['transaction_id' => 'w1', 'user_id' => (string) $w->id, 'game_id' => 'g', 'bet' => 100, 'win' => 600]);
        // players won 500 -> agent share -50
        $this->actingAs($b, 'agent')->get('/agent/dashboard?range=all')->assertOk()->assertSee('−₹50.00');
    }

    public function test_agent_sees_only_their_players_bets_deposits_and_withdrawals(): void
    {
        $a = $this->agent('agent');
        $mine = $this->player($a, 'Mine');
        $theirs = $this->player($this->agent('agent'), 'Theirs');
        AstechBetLog::create(['transaction_id' => 'm1', 'user_id' => (string) $mine->id, 'game_id' => 'mygame', 'bet' => 10, 'win' => 0]);
        AstechBetLog::create(['transaction_id' => 'o1', 'user_id' => (string) $theirs->id, 'game_id' => 'othergame', 'bet' => 10, 'win' => 0]);
        \App\Models\DepositRequest::create(['user_id' => $mine->id, 'amount' => 777, 'method' => 'upi', 'status' => 'approved']);
        \App\Models\DepositRequest::create(['user_id' => $theirs->id, 'amount' => 999, 'method' => 'upi', 'status' => 'approved']);
        \App\Models\WithdrawalRequest::create(['user_id' => $mine->id, 'amount' => 333, 'status' => 'pending']);
        \App\Models\WithdrawalRequest::create(['user_id' => $theirs->id, 'amount' => 444, 'status' => 'pending']);

        $this->actingAs($a, 'agent')->get('/agent/bets?range=all')->assertOk()->assertSee('mygame')->assertDontSee('othergame');
        $this->actingAs($a, 'agent')->get('/agent/deposits?range=all')->assertOk()->assertSee('777.00')->assertDontSee('999.00');
        $this->actingAs($a, 'agent')->get('/agent/withdrawals?range=all')->assertOk()->assertSee('333.00')->assertDontSee('444.00');
        $this->actingAs($a, 'agent')->get('/agent/bets?player=' . $theirs->id)->assertOk()->assertDontSee('othergame');
    }
}
