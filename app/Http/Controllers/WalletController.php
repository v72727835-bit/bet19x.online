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
     * Show deposit form.
     */
    public function deposit()
    {
        return view('wallet.deposit');
    }

    /**
     * Handle deposit request.
     *
     * Wallet is NOT credited here. A pending DepositRequest is created and the
     * balance is added only when an admin approves it (Admin\WalletManagementController).
     */
    public function storeDeposit(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:100|max:1000000',
            'payment_method' => 'required|in:upi,netbanking,card,wallet',
            'reference_note' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();

        $pendingCount = DepositRequest::where('user_id', $user->id)->where('status', 'pending')->count();
        if ($pendingCount >= 5) {
            return redirect()->back()->withInput()
                ->with('error', 'Aapki 5 deposit requests pehle se pending hain. Admin approval ka intezaar karein.');
        }

        DepositRequest::create([
            'user_id' => $user->id,
            'amount' => round((float) $data['amount'], 2),
            'method' => $data['payment_method'],
            'reference_note' => $data['reference_note'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('wallet.index')
            ->with('status', 'Deposit request of ₹' . number_format((float) $data['amount'], 2) . ' submit ho gayi. Admin approve karega tab paisa wallet me add hoga.');
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
}
