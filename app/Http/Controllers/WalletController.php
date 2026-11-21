<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DepositRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
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
     */
    public function storeWithdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'bank_details' => 'required|string|min:10',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return redirect()->route('wallet.index')
                ->with('error', 'Wallet not found.');
        }

        // Check sufficient balance (winnings + bonus)
        $available_balance = $wallet->winnings_balance + $wallet->bonus_balance;
        if ($request->input('amount') > $available_balance) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Insufficient balance. You can withdraw up to ₹' . number_format($available_balance, 2));
        }

        // Begin database transaction
        DB::beginTransaction();
        try {
            // Deduct amount from winnings balance first, then bonus
            $amountToWithdraw = $request->input('amount');

            if ($wallet->winnings_balance >= $amountToWithdraw) {
                $wallet->winnings_balance -= $amountToWithdraw;
            } else {
                $remaining = $amountToWithdraw - $wallet->winnings_balance;
                $wallet->winnings_balance = 0;
                $wallet->bonus_balance -= $remaining;
            }

            $wallet->save();

            // Record transaction
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'withdrawal',
                'amount' => $amountToWithdraw,
                'description' => 'Withdrawal to bank account',
                'reference_id' => 'WDL_' . Str::upper(Str::random(8)),
            ]);

            DB::commit();

            return redirect()->route('wallet.index')
                ->with('status', 'Withdrawal request of ₹' . number_format($amountToWithdraw, 2) . ' submitted successfully. Funds will be transferred to your bank account within 2-4 business hours.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Withdrawal failed. Please try again.');
        }
    }
}