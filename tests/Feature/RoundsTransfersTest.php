<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Agent;
use App\Models\AstechBetLog;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\BetRounds;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RoundsTransfersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('astech_games');
        Http::fake(['*' => Http::response(['status' => 'success', 'content' => ['games' => [
            ['gameId' => 'ls', 'title' => 'Lightning Storm', 'provider' => 'evolution', 'category' => 'live_casino', 'currencies' => ['INR']],
            ['gameId' => 'j1', 'title' => 'Super Ace', 'provider' => 'jili', 'category' => 'slots', 'currencies' => ['INR']],
            ['gameId' => 'j2', 'title' => 'Fortune Gems', 'provider' => 'jili', 'category' => 'slots', 'currencies' => ['INR']],
        ]]])]);
    }

    private function user(string $name, float $dep = 0, float $win = 0, ?int $agentId = null): User
    {
        $u = User::create(['name' => $name, 'email' => strtolower(str_replace(' ', '', $name)) . '@t.com', 'password' => 'x', 'agent_id' => $agentId]);
        Wallet::create(['user_id' => $u->id, 'deposit_balance' => $dep, 'winnings_balance' => $win]);
        return $u;
    }

    private function admin(): Admin
    {
        return Admin::create(['name' => 'Boss', 'email' => uniqid() . '@admin.com', 'password' => 'secret123', 'role' => 'super_admin', 'is_active' => true]);
    }

    private function log(User $u, string $txn, string $round, float $bet, float $win): void
    {
        AstechBetLog::create(['transaction_id' => $txn, 'user_id' => (string) $u->id, 'game_id' => 'ls', 'round_id' => $round, 'bet' => $bet, 'win' => $win, 'balance_after' => 100]);
    }

    private function bal(User $u): array
    {
        $w = Wallet::where('user_id', $u->id)->first();
        return [(float) $w->deposit_balance, (float) $w->winnings_balance];
    }

    // ---------- rounds ----------
    public function test_stake_and_payout_of_a_round_become_one_line(): void
    {
        $p = $this->user('Dipa Malo');
        $this->log($p, 't1', 'R1', 4400, 0);     // stake
        $this->log($p, 't2', 'R1', 0, 90000);    // payout of the same round
        $this->log($p, 't3', 'R2', 3000, 0);     // lost round

        $t = BetRounds::totals(AstechBetLog::query());
        $this->assertSame(2, (int) $t->rounds);
        $this->assertEquals(7400, $t->bet);
        $this->assertEquals(90000, $t->win);
        $this->assertEquals(85600, $t->profit);
        $this->assertEquals(3000, $t->loss);

        $rounds = BetRounds::paginate(AstechBetLog::query())->getCollection()->keyBy('round_key');
        $this->assertEquals(85600, $rounds['R1']->result);
        $this->assertSame(2, (int) $rounds['R1']->entries);
        $this->assertEquals(-3000, $rounds['R2']->result);
    }

    public function test_bet_history_pages_show_round_profit_and_loss(): void
    {
        $p = $this->user('Dipa Malo');
        $this->log($p, 't1', 'R1', 4400, 0);
        $this->log($p, 't2', 'R1', 0, 90000);

        $this->actingAs($p)->get('/bets')->assertOk()->assertSee('85,600.00')->assertSee('Profit')->assertSee('Lightning Storm');
        $this->actingAs($this->admin(), 'admin')->get('/admin/game-bets')->assertOk()
            ->assertSee('Who is betting how much')->assertSee('Profit +₹85,600.00', false)->assertSee('Dipa Malo');
        $this->actingAs($this->admin(), 'admin')->get("/admin/users/{$p->id}")->assertOk()->assertSee('Profit +₹85,600.00', false);
    }

    // ---------- transfers ----------
    public function test_player_sends_money_to_another_player(): void
    {
        $a = $this->user('Amit', 300, 200);
        $b = $this->user('Bala', 0, 0);

        $this->actingAs($a)->getJson('/wallet/transfer/lookup?to=' . $b->id)->assertJson(['found' => true, 'name' => 'Bala']);
        $this->actingAs($a)->post('/wallet/transfer', ['to' => 'bala@t.com', 'amount' => 400])->assertRedirect(route('wallet.index'));

        $this->assertSame([0.0, 100.0], $this->bal($a));   // deposit used first, then winnings
        $this->assertSame([400.0, 0.0], $this->bal($b));   // arrives as deposit balance
        $this->assertSame(1, WalletTransaction::where('type', 'transfer_out')->count());
        $this->assertSame(1, WalletTransaction::where('type', 'transfer_in')->count());
    }

    public function test_transfer_refuses_overdraft_self_and_blocked(): void
    {
        $a = $this->user('Amit', 50, 0);
        $b = $this->user('Bala');

        $this->actingAs($a)->post('/wallet/transfer', ['to' => $b->id, 'amount' => 60])->assertSessionHas('error');
        $this->actingAs($a)->post('/wallet/transfer', ['to' => $a->id, 'amount' => 20])->assertSessionHas('error');
        $b->update(['is_blocked' => true]);
        $this->actingAs($a)->post('/wallet/transfer', ['to' => $b->id, 'amount' => 20])->assertSessionHas('error');

        $this->assertSame([50.0, 0.0], $this->bal($a));
        $this->assertSame(0, WalletTransaction::count());
    }

    public function test_guest_cannot_transfer(): void
    {
        $this->post('/wallet/transfer', ['to' => 1, 'amount' => 20])->assertRedirect(route('login'));
    }

    public function test_admin_transfers_between_players(): void
    {
        $a = $this->user('Amit', 500);
        $b = $this->user('Bala');

        $this->actingAs($this->admin(), 'admin')->get('/admin/wallet/transfer')->assertOk();
        $this->actingAs($this->admin(), 'admin')->post('/admin/wallet/transfer', ['from' => $a->id, 'to' => 'bala@t.com', 'amount' => 125, 'reason' => 'correction'])->assertRedirect();

        $this->assertSame([375.0, 0.0], $this->bal($a));
        $this->assertSame([125.0, 0.0], $this->bal($b));
        $this->assertStringContainsString('by admin Boss', WalletTransaction::where('type', 'transfer_in')->value('description'));
    }

    public function test_agent_transfers_only_inside_its_network(): void
    {
        $ag = Agent::create(['name' => 'Ag', 'email' => 'ag@x.com', 'password' => Hash::make('agpass1'), 'role' => 'agent', 'commission_rate' => 5, 'is_active' => true]);
        $mine1 = $this->user('Mine One', 200, 0, $ag->id);
        $mine2 = $this->user('Mine Two', 0, 0, $ag->id);
        $other = $this->user('Other Guy', 0, 0, null);

        $this->actingAs($ag, 'agent')->get('/agent/transfer')->assertOk()->assertSee('Mine One')->assertDontSee('Other Guy');
        $this->actingAs($ag, 'agent')->post('/agent/transfer', ['from' => $mine1->id, 'to' => $mine2->id, 'amount' => 80])->assertRedirect();
        $this->assertSame([120.0, 0.0], $this->bal($mine1));
        $this->assertSame([80.0, 0.0], $this->bal($mine2));

        $this->actingAs($ag, 'agent')->post('/agent/transfer', ['from' => $mine1->id, 'to' => $other->id, 'amount' => 20])->assertSessionHasErrors('from');
        $this->assertSame([0.0, 0.0], $this->bal($other));
    }

    // ---------- provider order ----------
    public function test_pinned_providers_come_first_on_the_home_page(): void
    {
        // by game count jili (2) would be first; pin evolution to position 1
        $this->assertSame(['jili', 'evolution'], $this->getJson('/games/meta')->json('provider_order'));

        $this->actingAs($this->admin(), 'admin')->get('/admin/providers')->assertOk()->assertSee('evolution')->assertSee('jili');
        $this->actingAs($this->admin(), 'admin')->post('/admin/providers', ['position' => ['evolution' => 1, 'jili' => null]])->assertRedirect();

        $this->assertSame(['evolution' => 1], SiteSetting::providerOrder());
        $this->assertSame(['evolution', 'jili'], $this->getJson('/games/meta')->json('provider_order'));

        $this->actingAs($this->admin(), 'admin')->post('/admin/providers', ['clear' => 1]);
        $this->assertSame(['jili', 'evolution'], $this->getJson('/games/meta')->json('provider_order'));
    }
}
