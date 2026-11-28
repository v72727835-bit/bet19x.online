<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DepositRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    /**
     * Show the user's wallet page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        // Ensure wallet exists (should already exist via registration)
        $wallet = $user->wallet ?? Wallet::firstOrCreate(['user_id' => $user->id]);
        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $depositRequests = DepositRequest::where('user_id', $user->id)
            ->latest()->limit(10)->get();

        return view('wallet.index', compact('wallet', 'transactions', 'depositRequests'));
    }

    /**
     * Show deposit form: the payment details the admin has set up (UPI ID, QR, bank).
     */
    public function deposit()
    {
        $methods = \App\Models\PaymentMethod::available();

        return view('wallet.deposit', compact('methods'));
    }

    /**
     * Handle deposit request.
     *
     * Wallet is NOT credited here. A pending DepositRequest is created and the
     * balance is added only when an admin approves it (Admin\WalletManagementController).
     */
    public function storeDeposit(Request $request)
    {
        $methods = \App\Models\PaymentMethod::available();
        if ($methods->isEmpty()) {
            return redirect()->route('wallet.deposit')->with('error', 'Deposit abhi available nahi hai. Thodi der baad try karein ya support se baat karein.');
        }

        $data = $request->validate([
            'payment_method_id' => 'required|integer',
            'amount' => 'required|numeric|min:1|max:10000000',
            'reference_note' => ['required', 'string', 'min:6', 'max:40', 'regex:/^[A-Za-z0-9\-_ ]+$/'],
            'screenshot' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
        ], [
            'reference_note.required' => 'Payment ka UTR / transaction ID daalna zaroori hai.',
            'reference_note.min' => 'UTR / transaction ID kam se kam 6 akshar ka hona chahiye.',
            'reference_note.regex' => 'UTR me sirf akshar aur number ho sakte hain.',
            'screenshot.image' => 'Screenshot ek image honi chahiye (PNG, JPG ya WebP).',
            'screenshot.max' => 'Screenshot 4 MB se chhota hona chahiye.',
        ]);

        $method = $methods->firstWhere('id', (int) $data['payment_method_id']);
        if (! $method) {
            return back()->withInput()->with('error', 'Ye payment option ab available nahi hai. Dusra chuniye.');
        }

        $amount = round((float) $data['amount'], 2);
        if ($amount < (float) $method->min_amount) {
            return back()->withInput()->withErrors(['amount' => 'Is option par kam se kam ₹' . number_format($method->min_amount, 0) . ' deposit karein.']);
        }
        if ($method->max_amount && $amount > (float) $method->max_amount) {
            return back()->withInput()->withErrors(['amount' => 'Is option par zyada se zyada ₹' . number_format($method->max_amount, 0) . ' deposit ho sakta hai.']);
        }

        $user = Auth::user();

        if (DepositRequest::where('user_id', $user->id)->where('status', 'pending')->count() >= 5) {
            return redirect()->back()->withInput()
                ->with('error', 'Aapki 5 deposit requests pehle se pending hain. Admin approval ka intezaar karein.');
        }

        $utr = strtoupper(trim($data['reference_note']));
        if (DepositRequest::where('reference_note', $utr)->whereIn('status', ['pending', 'approved'])->exists()) {
            return back()->withInput()->withErrors(['reference_note' => 'Ye UTR pehle se use ho chuka hai. Sahi UTR daalein.']);
        }

        DepositRequest::create([
            'user_id' => $user->id,
            'payment_method_id' => $method->id,
            'amount' => $amount,
            'method' => $method->title,
            'reference_note' => $utr,
            'screenshot_path' => $request->hasFile('screenshot') ? $request->file('screenshot')->store('deposit-proofs', 'public') : null,
            'status' => 'pending',
        ]);

        return redirect()->route('wallet.index')
            ->with('status', 'Deposit request of ₹' . number_format($amount, 2) . ' submit ho gayi. Admin payment check karke approve karega, tab paisa wallet me add hoga.');
    }

    /**
     * Show withdraw form.
     */
    public function withdraw()
    {
        return view('wallet.withdraw');
    }

    /**
     * Handle withdraw request.
     *
     * Only winnings are withdrawable (not deposits/bonus). The amount is held
     * (deducted) atomically under a row lock and a pending WithdrawalRequest is
     * created. Admin approval marks it paid; rejection refunds the held amount.
     */
    public function storeWithdraw(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:100|max:1000000',
            'bank_details' => 'required|string|min:10|max:500',
        ]);

        $user = Auth::user();
        $amount = round((float) $data['amount'], 2);

        try {
            DB::transaction(function () use ($user, $amount, $data) {
                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                if (! $wallet) {
                    throw new \DomainException('Wallet not found.');
                }

                if ($amount > (float) $wallet->winnings_balance + 0.00001) {
                    throw new \DomainException('Insufficient winnings balance. You can withdraw up to ₹'
                        . number_format((float) $wallet->winnings_balance, 2));
                }

                $wallet->winnings_balance = round((float) $wallet->winnings_balance - $amount, 2);
                $wallet->save();

                $req = WithdrawalRequest::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'account_details' => $data['bank_details'],
                    'status' => 'pending',
                ]);

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'withdrawal',
                    'amount' => $amount,
                    'description' => 'Withdrawal request (pending approval)',
                    'reference_id' => 'WDL_' . $req->id,
                ]);
            });
        } catch (\DomainException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()->with('error', 'Withdrawal failed. Please try again.');
        }

        return redirect()->route('wallet.index')
            ->with('status', 'Withdrawal request of ₹' . number_format($amount, 2) . ' submit ho gayi. Admin approve karega tab bank me transfer hoga.');
    }

    /** Send money to another player. */
    public function transfer()
    {
        $wallet = Wallet::firstOrCreate(['user_id' => Auth::id()]);

        return view('wallet.transfer', compact('wallet'));
    }

    /** Shows whose account a user ID / email / phone belongs to before sending (name partly hidden). */
    public function transferLookup(Request $request)
    {
        $user = \App\Services\WalletTransfer::findUser($request->query('to'));
        if (! $user || $user->id === Auth::id()) {
            return response()->json(['found' => false]);
        }
        return response()->json(['found' => true, 'id' => $user->id, 'name' => \App\Services\WalletTransfer::maskName($user->name), 'blocked' => (bool) $user->is_blocked]);
    }

    /** Shows which agent an agent code belongs to before the player sends to it. */
    public function agentLookup(Request $request)
    {
        $agent = \App\Models\Agent::findByReferralCode($request->query('code'));
        if (! $agent) {
            return response()->json(['found' => false]);
        }

        return response()->json(['found' => true, 'name' => $agent->name, 'type' => $agent->roleLabel(), 'code' => $agent->referralCode()]);
    }

    /** Player sends money from its wallet to any active agent by the agent's code. The agent gets it as credit. */
    public function storeAgentTransfer(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20',
            'amount' => 'required|numeric|min:' . \App\Services\AgentUserTransfer::MIN . '|max:' . \App\Services\AgentUserTransfer::MAX,
            'note' => 'nullable|string|max:120',
        ]);

        $agent = \App\Models\Agent::findByReferralCode($data['code']);
        if (! $agent) {
            return back()->withInput()->with('error', 'Is agent code ka koi active agent nahi mila. Code dobara check karein.');
        }

        try {
            $ref = \App\Services\AgentUserTransfer::userToAgent(Auth::user(), $agent, (float) $data['amount'], 'credit', 'player', $data['note'] ?? null);
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('wallet.index')
            ->with('status', '₹' . number_format((float) $data['amount'], 2) . " agent {$agent->name} ({$agent->referralCode()}) ko bhej diye. Ref {$ref}");
    }

    public function storeTransfer(Request $request)
    {
        $data = $request->validate([
            'to' => 'required|max:255',
            'amount' => 'required|numeric|min:' . \App\Services\WalletTransfer::MIN . '|max:1000000',
            'note' => 'nullable|string|max:120',
        ]);

        $to = \App\Services\WalletTransfer::findUser($data['to']);
        if (! $to) {
            return back()->withInput()->with('error', 'Is user ID / email / phone ka koi player nahi mila.');
        }

        try {
            $ref = \App\Services\WalletTransfer::send(Auth::user(), $to, (float) $data['amount'], 'player', $data['note'] ?? null);
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('wallet.index')
            ->with('status', '₹' . number_format((float) $data['amount'], 2) . " {$to->name} (#{$to->id}) ko bhej diye. Ref {$ref}");
    }
}
