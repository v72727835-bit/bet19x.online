<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AstechBetLog;
use App\Models\SupportConversation;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminFeaturesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('astech_games');
        Http::fake(['*' => Http::response(['status' => 'success', 'content' => ['games' => [
            ['gameId' => 'g1', 'title' => 'Aviator', 'provider' => 'spribe', 'category' => 'crash', 'currencies' => ['INR']],
        ]]])]);
    }

    private function user(string $email = null): User
    {
        $u = User::create(['name' => 'Player One', 'email' => $email ?? 'p' . uniqid() . '@t.com', 'password' => Hash::make('oldpass123')]);
        Wallet::create(['user_id' => $u->id, 'deposit_balance' => 100]);
        return $u;
    }

    private function admin(string $role = 'super_admin', ?array $permissions = null): Admin
    {
        return Admin::create(['name' => 'A', 'email' => uniqid() . '@admin.com', 'password' => 'secret123', 'role' => $role, 'is_active' => true, 'permissions' => $permissions]);
    }

    private function bet(User $u, float $bet, float $win, string $txn): void
    {
        AstechBetLog::create(['transaction_id' => $txn, 'user_id' => (string) $u->id, 'game_id' => 'g1', 'bet' => $bet, 'win' => $win, 'balance_after' => 50]);
    }

    // ---- bet history ----
    public function test_player_sees_only_their_own_bet_history(): void
    {
        $me = $this->user(); $other = $this->user();
        $this->bet($me, 10, 25, 't1');
        $this->bet($other, 99, 0, 't2');

        $this->actingAs($me)->get('/bets')->assertOk()->assertSee('Aviator')->assertSee('25.00')->assertDontSee('99.00');
    }

    public function test_guest_cannot_see_bet_history(): void
    {
        $this->get('/bets')->assertRedirect(route('login'));
    }

    public function test_admin_sees_everyones_bets_and_can_filter_by_player(): void
    {
        $a = $this->user('alice@t.com'); $b = $this->user('bob@t.com');
        $this->bet($a, 10, 0, 't1');
        $this->bet($b, 20, 5, 't2');

        $this->actingAs($this->admin(), 'admin')->get('/admin/game-bets')->assertOk()->assertSee('alice@t.com')->assertSee('bob@t.com');
        $this->actingAs($this->admin(), 'admin')->get('/admin/game-bets?user=alice')->assertOk()->assertSee('alice@t.com')->assertDontSee('bob@t.com');
    }

    // ---- user management ----
    public function test_admin_can_view_edit_and_reset_password(): void
    {
        $u = $this->user();
        $admin = $this->admin();
        $this->bet($u, 10, 0, 't9');

        $this->actingAs($admin, 'admin')->get("/admin/users/{$u->id}")->assertOk()->assertSee('Game bet history')->assertSee('Login as user');
        $this->actingAs($admin, 'admin')->get("/admin/users/{$u->id}/edit")->assertOk();
        $this->actingAs($admin, 'admin')->put("/admin/users/{$u->id}/update", [
            'name' => 'Renamed', 'email' => 'new@t.com', 'phone' => '999', 'new_password' => 'brandnew123',
        ])->assertRedirect(route('admin.users.show', $u));

        $u->refresh();
        $this->assertSame('Renamed', $u->name);
        $this->assertSame('new@t.com', $u->email);
        $this->assertTrue(Hash::check('brandnew123', $u->password));
    }

    public function test_admin_can_delete_a_user(): void
    {
        $u = $this->user();
        $this->actingAs($this->admin(), 'admin')->delete("/admin/users/{$u->id}/delete")->assertRedirect(route('admin.users.index'));
        $this->assertNull(User::find($u->id));
        $this->assertNull(Wallet::where('user_id', $u->id)->first());
    }

    public function test_admin_can_login_as_user_and_come_back(): void
    {
        $u = $this->user();
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->post("/admin/users/{$u->id}/login-as")->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($u, 'web');
        $this->assertSame($admin->id, session('impersonator_admin_id'));

        $this->post('/impersonation/leave')->assertRedirect(route('admin.users.show', $u->id));
        $this->assertGuest('web');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    // ---- sub-admins & permissions ----
    public function test_super_admin_creates_sub_admin_with_chosen_permissions(): void
    {
        $this->actingAs($this->admin(), 'admin')->post('/admin/admin-users', [
            'name' => 'Sub', 'email' => 'sub@admin.com', 'password' => 'subpass123', 'type' => 'sub_admin', 'permissions' => ['support', 'bets'],
        ])->assertRedirect();

        $sub = Admin::where('email', 'sub@admin.com')->firstOrFail();
        $this->assertSame(['support', 'bets'], $sub->permissions);
        $this->assertTrue($sub->hasPermission('bets'));
        $this->assertFalse($sub->hasPermission('wallet'));
    }

    public function test_sub_admin_only_opens_permitted_sections(): void
    {
        $sub = $this->admin('support', ['bets']);

        $this->actingAs($sub, 'admin')->get('/admin/game-bets')->assertOk();
        $this->actingAs($sub, 'admin')->get('/admin/wallet/deposits')->assertForbidden();
        $this->actingAs($sub, 'admin')->get('/admin/support')->assertForbidden();
        $this->actingAs($sub, 'admin')->get('/admin/users')->assertForbidden();
        $this->actingAs($sub, 'admin')->get('/admin/admin-users')->assertForbidden();
    }

    public function test_sidebar_hides_sections_without_permission(): void
    {
        $sub = $this->admin('support', ['bets']);
        $this->actingAs($sub, 'admin')->get('/admin/game-bets')->assertOk()
            ->assertSee(route('admin.game-bets.index'))
            ->assertDontSee(route('admin.wallet.deposits'))
            ->assertDontSee(route('admin.support.index'));
    }

    public function test_super_admin_updates_sub_admin_permissions(): void
    {
        $sub = $this->admin('support', ['bets']);
        $this->actingAs($this->admin(), 'admin')->put("/admin/admin-users/{$sub->id}/update", ['permissions' => ['wallet']])->assertRedirect();
        $this->assertSame(['wallet'], $sub->fresh()->permissions);
    }

    // ---- live support ----
    public function test_live_support_round_trip(): void
    {
        $u = $this->user();
        $admin = $this->admin('support', ['support']);

        $this->actingAs($u)->postJson('/support/messages', ['body' => 'Deposit not added'])->assertOk()->assertJsonPath('message.body', 'Deposit not added');
        $conv = SupportConversation::where('user_id', $u->id)->firstOrFail();
        $this->assertSame(1, $conv->admin_unread);

        $this->actingAs($admin, 'admin')->get('/admin/support')->assertOk()->assertSee('Deposit not added');
        $this->actingAs($admin, 'admin')->get("/admin/support/{$conv->id}")->assertOk();
        $this->assertSame(0, $conv->fresh()->admin_unread);
        $this->actingAs($admin, 'admin')->postJson("/admin/support/{$conv->id}/reply", ['body' => 'Checking now'])->assertOk();

        $this->actingAs($u)->getJson('/support/messages')->assertOk()->assertJsonPath('unread', 1)->assertJsonPath('messages.1.body', 'Checking now');
        $this->actingAs($u)->getJson('/support/messages?seen=1')->assertJsonPath('unread', 0);
    }

    public function test_guest_cannot_post_support_messages(): void
    {
        $this->postJson('/support/messages', ['body' => 'hi'])->assertUnauthorized();
    }
}
