<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\DepositRequest;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentMethodsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function admin(string $role = 'super_admin', ?array $perm = null): Admin
    {
        return Admin::create(['name' => 'Boss', 'email' => uniqid() . '@admin.com', 'password' => 'secret123', 'role' => $role, 'is_active' => true, 'permissions' => $perm]);
    }

    private function player(): User
    {
        $u = User::create(['name' => 'Raju', 'email' => uniqid() . '@p.com', 'password' => 'x']);
        Wallet::create(['user_id' => $u->id]);
        return $u;
    }

    private function method(array $over = []): PaymentMethod
    {
        return PaymentMethod::create($over + ['title' => 'PhonePe', 'upi_id' => 'shop@okaxis', 'payee_name' => 'BET19X', 'min_amount' => 100]);
    }

    // ---------- admin sets it up ----------
    public function test_admin_adds_upi_qr_and_bank_details(): void
    {
        $qr = UploadedFile::fake()->image('qr.png', 300, 300);

        $this->actingAs($this->admin(), 'admin')->post('/admin/payment-methods', [
            'title' => 'HDFC + UPI', 'upi_id' => 'shop@okaxis', 'payee_name' => 'BET19X', 'qr_image' => $qr,
            'bank_name' => 'HDFC Bank', 'account_holder' => 'BET19X Pvt', 'account_number' => '50100123456789', 'ifsc' => 'hdfc0001234',
            'instructions' => 'Send exact amount', 'min_amount' => 200, 'max_amount' => 50000,
        ])->assertRedirect(route('admin.payment-methods.index'));

        $m = PaymentMethod::firstOrFail();
        $this->assertSame('HDFC0001234', $m->ifsc);                  // upper-cased
        $this->assertNotNull($m->qr_image_path);
        Storage::disk('public')->assertExists($m->qr_image_path);
        $this->assertSame('UPI + QR + Bank', $m->summary());
        $this->actingAs($this->admin(), 'admin')->get('/admin/payment-methods')->assertOk()->assertSee('HDFC + UPI')->assertSee('shop@okaxis');
    }

    public function test_admin_input_is_validated(): void
    {
        $a = $this->admin();
        $a = $this->actingAs($a, 'admin');
        $a->post('/admin/payment-methods', ['title' => 'Empty', 'min_amount' => 100])->assertSessionHasErrors('upi_id');   // nothing to pay to
        $a->post('/admin/payment-methods', ['title' => 'Bad', 'upi_id' => 'not-a-upi', 'min_amount' => 100])->assertSessionHasErrors('upi_id');
        $a->post('/admin/payment-methods', ['title' => 'Svg', 'upi_id' => 'a@b', 'min_amount' => 100,
            'qr_image' => UploadedFile::fake()->create('qr.svg', 10, 'image/svg+xml')])->assertSessionHasErrors('qr_image');
        $a->post('/admin/payment-methods', ['title' => 'Range', 'upi_id' => 'a@bank', 'min_amount' => 500, 'max_amount' => 100])->assertSessionHasErrors('max_amount');
        $this->assertSame(0, PaymentMethod::count());
    }

    public function test_admin_edits_replaces_qr_hides_and_deletes(): void
    {
        $admin = $this->admin();
        $m = PaymentMethod::create(['title' => 'Old', 'upi_id' => 'old@bank', 'min_amount' => 100,
            'qr_image_path' => UploadedFile::fake()->image('a.png')->store('payment-qr', 'public')]);
        $oldQr = $m->qr_image_path;

        $this->actingAs($admin, 'admin')->get("/admin/payment-methods/{$m->id}/edit")->assertOk()->assertSee('Old');
        $this->actingAs($admin, 'admin')->put("/admin/payment-methods/{$m->id}", ['title' => 'New', 'upi_id' => 'new@bank', 'min_amount' => 100,
            'qr_image' => UploadedFile::fake()->image('b.png')])->assertRedirect();
        $m->refresh();
        $this->assertSame('New', $m->title);
        $this->assertNotSame($oldQr, $m->qr_image_path);
        Storage::disk('public')->assertMissing($oldQr);
        Storage::disk('public')->assertExists($m->qr_image_path);

        $this->actingAs($admin, 'admin')->post("/admin/payment-methods/{$m->id}/toggle")->assertRedirect();
        $this->assertFalse($m->fresh()->is_active);

        $this->actingAs($admin, 'admin')->delete("/admin/payment-methods/{$m->id}")->assertRedirect(route('admin.payment-methods.index'));
        $this->assertNull(PaymentMethod::find($m->id));
    }

    public function test_only_admins_with_wallet_permission_manage_payment_details(): void
    {
        $this->actingAs($this->admin('support', ['bets']), 'admin')->get('/admin/payment-methods')->assertForbidden();
        $this->actingAs($this->admin('support', ['wallet']), 'admin')->get('/admin/payment-methods')->assertOk();
    }

    public function test_guest_cannot_open_payment_details(): void
    {
        $this->get('/admin/payment-methods')->assertRedirect(route('admin.login'));
    }

    // ---------- the player sees it ----------
    public function test_deposit_page_shows_the_admins_details_to_the_player(): void
    {
        $this->method(['title' => 'PhonePe', 'upi_id' => 'shop@okaxis', 'qr_image_path' => 'payment-qr/x.png', 'instructions' => 'Pay exact amount']);
        $this->method(['title' => 'HDFC Bank', 'upi_id' => null, 'bank_name' => 'HDFC', 'account_holder' => 'BET19X', 'account_number' => '50100123456789', 'ifsc' => 'HDFC0001234', 'sort_order' => 1]);
        $this->method(['title' => 'Hidden one', 'upi_id' => 'hide@bank', 'is_active' => false]);

        $this->actingAs($this->player())->get('/wallet/deposit')->assertOk()
            ->assertSee('PhonePe')->assertSee('shop@okaxis')->assertSee('payment-qr/x.png')->assertSee('Pay exact amount')
            ->assertSee('HDFC Bank')->assertSee('50100123456789')->assertSee('HDFC0001234')
            ->assertSee('upi:\\/\\/pay?pa=shop%40okaxis', false)   // inside a JS string, slashes are escaped
            ->assertDontSee('Hidden one')->assertDontSee('hide@bank');
    }

    public function test_deposit_page_without_any_method_explains_instead_of_failing(): void
    {
        $this->actingAs($this->player())->get('/wallet/deposit')->assertOk()->assertSee('Deposits are not available right now');
        $this->actingAs($this->player())->post('/wallet/deposit', ['payment_method_id' => 1, 'amount' => 500, 'reference_note' => '123456789012'])
            ->assertRedirect(route('wallet.deposit'))->assertSessionHas('error');
        $this->assertSame(0, DepositRequest::count());
    }

    // ---------- the full flow ----------
    public function test_full_flow_player_requests_admin_approves_wallet_is_credited(): void
    {
        $m = $this->method(['title' => 'PhonePe']);
        $u = $this->player();

        $this->actingAs($u)->post('/wallet/deposit', [
            'payment_method_id' => $m->id, 'amount' => 750, 'reference_note' => '412345678901',
            'screenshot' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertRedirect(route('wallet.index'));

        $dep = DepositRequest::firstOrFail();
        $this->assertSame('pending', $dep->status);
        $this->assertSame('PhonePe', $dep->method);
        $this->assertSame($m->id, $dep->payment_method_id);
        $this->assertSame('412345678901', $dep->reference_note);
        Storage::disk('public')->assertExists($dep->screenshot_path);
        $this->assertEquals(0, (float) Wallet::where('user_id', $u->id)->value('deposit_balance'));   // nothing yet

        $admin = $this->admin();
        $this->actingAs($admin, 'admin')->get('/admin/wallet/deposits')->assertOk()->assertSee('412345678901')->assertSee('View screenshot')->assertSee('PhonePe');
        $this->actingAs($admin, 'admin')->post(route('admin.wallet.deposits.approve', $dep))->assertRedirect();

        $this->assertSame('approved', $dep->fresh()->status);
        $this->assertEquals(750, (float) Wallet::where('user_id', $u->id)->value('deposit_balance'));    // credited on approval
    }

    public function test_deposit_rules_amount_limits_utr_and_duplicates(): void
    {
        $m = $this->method(['min_amount' => 200, 'max_amount' => 5000]);
        $u = $this->player();
        $ok = ['payment_method_id' => $m->id, 'reference_note' => 'UTR000000001'];

        $this->actingAs($u)->post('/wallet/deposit', $ok + ['amount' => 100])->assertSessionHasErrors('amount');               // below this option's minimum
        $this->actingAs($u)->post('/wallet/deposit', $ok + ['amount' => 9000])->assertSessionHasErrors('amount');              // above maximum
        $this->actingAs($u)->post('/wallet/deposit', ['payment_method_id' => $m->id, 'amount' => 300])->assertSessionHasErrors('reference_note');   // UTR is required
        $this->actingAs($u)->post('/wallet/deposit', ['payment_method_id' => $m->id, 'amount' => 300, 'reference_note' => '12'])->assertSessionHasErrors('reference_note');
        $this->actingAs($u)->post('/wallet/deposit', $ok + ['amount' => 300, 'screenshot' => UploadedFile::fake()->create('x.exe', 10, 'application/x-msdownload')])->assertSessionHasErrors('screenshot');
        $this->assertSame(0, DepositRequest::count());

        $this->actingAs($u)->post('/wallet/deposit', $ok + ['amount' => 300])->assertRedirect(route('wallet.index'));
        $this->actingAs($this->player())->post('/wallet/deposit', $ok + ['amount' => 300])->assertSessionHasErrors('reference_note');   // same UTR, other player
        $this->assertSame(1, DepositRequest::count());
    }

    public function test_hidden_method_cannot_be_used(): void
    {
        $m = $this->method(['is_active' => false]);
        $this->method(['title' => 'Other']);
        $this->actingAs($this->player())->post('/wallet/deposit', ['payment_method_id' => $m->id, 'amount' => 500, 'reference_note' => 'UTR000000002'])->assertSessionHas('error');
        $this->assertSame(0, DepositRequest::count());
    }
}
