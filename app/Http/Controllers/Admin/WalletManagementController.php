<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DepositRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletManagementController extends Controller
{
    public function deposits(Request $request)
    {
        $query = DepositRequest::with('user');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $deposits = $query->latest()->paginate(20)->withQueryString();

        return view('admin.wallet.deposits', compact('deposits'));
    }

    public function approveDeposit(Request $request, DepositRequest $depositRequest)
    {
        $approved = DB::transaction(function () use ($request, $depositRequest) {
            // Lock the row so a double-click / two admins can't credit twice.
            $dep = DepositRequest::lockForUpdate()->find($depositRequest->id);
            if (! $dep || $dep->status !== 'pending') {
                return false;
            }

            $wallet = Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $dep->user_id],
                ['deposit_balance' => 0, 'winnings_balance' => 0, 'bonus_balance' => 0]
            );
            $wallet->increment('deposit_balance', $dep->amount);
            $wallet->transactions()->create([
                'type' => 'deposit',
                'amount' => $dep->amount,
                'description' => 'Deposit approved (' . $dep->method . ')',
                'reference_id' => 'DEP_' . $dep->id,
            ]);

            $dep->update([
                'status' => 'approved',
                'admin_remarks' => $request->admin_remarks,
                'processed_by' => auth('admin')->id(),
                'processed_at' => now(),
            ]);

            return true;
        });

        if (! $approved) {
            return back()->with('status', 'Ye request pehle hi process ho chuki hai.');
        }

        AuditLog::record('Wallet Management', 'Approve Deposit', "Deposit #{$depositRequest->id} approved");

        return back()->with('status', 'Deposit approve ho gaya.');
    }

    public function rejectDeposit(Request $request, DepositRequest $depositRequest)
    {
        $request->validate(['admin_remarks' => 'required|string|max:255']);

        if ($depositRequest->status !== 'pending') {
            return back()->with('status', 'Ye request pehle hi process ho chuki hai.');
        }

        $depositRequest->update([
            'status' => 'rejected',
            'admin_remarks' => $request->admin_remarks,
            'processed_by' => auth('admin')->id(),
            'processed_at' => now(),
        ]);

        AuditLog::record('Wallet Management', 'Reject Deposit', "Deposit #{$depositRequest->id} rejected");

        return back()->with('status', 'Deposit reject kar diya gaya.');
    }

    public function withdrawals(Request $request)
    {
        $query = WithdrawalRequest::with('user');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $withdrawals = $query->latest()->paginate(20)->withQueryString();

        return view('admin.wallet.withdrawals', compact('withdrawals'));
    }

    public function approveWithdrawal(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        // The amount was already held (deducted) when the user requested it.
        $done = DB::transaction(function () use ($request, $withdrawalRequest) {
            $req = WithdrawalRequest::lockForUpdate()->find($withdrawalRequest->id);
            if (! $req || $req->status !== 'pending') {
                return false;
            }

            $req->update([
                'status' => 'approved',
                'admin_remarks' => $request->admin_remarks,
                'processed_by' => auth('admin')->id(),
                'processed_at' => now(),
            ]);

            return true;
        });

        if (! $done) {
            return back()->with('status', 'Ye request pehle hi process ho chuki hai.');
        }

        AuditLog::record('Wallet Management', 'Approve Withdrawal', "Withdrawal #{$withdrawalRequest->id} approved");

        return back()->with('status', 'Withdrawal approve ho gaya.');
    }

    public function rejectWithdrawal(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        $request->validate(['admin_remarks' => 'required|string|max:255']);

        // Rejecting refunds the held amount back to the winnings balance.
        $done = DB::transaction(function () use ($request, $withdrawalRequest) {
            $req = WithdrawalRequest::lockForUpdate()->find($withdrawalRequest->id);
            if (! $req || $req->status !== 'pending') {
                return false;
            }

            $wallet = Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $req->user_id],
                ['deposit_balance' => 0, 'winnings_balance' => 0, 'bonus_balance' => 0]
            );
            $wallet->increment('winnings_balance', $req->amount);
            $wallet->transactions()->create([
                'type' => 'refund',
                'amount' => $req->amount,
                'description' => 'Withdrawal rejected - amount refunded',
                'reference_id' => 'WDL_' . $req->id,
            ]);

            $req->update([
                'status' => 'rejected',
                'admin_remarks' => $request->admin_remarks,
                'processed_by' => auth('admin')->id(),
                'processed_at' => now(),
            ]);

            return true;
        });

        if (! $done) {
            return back()->with('status', 'Ye request pehle hi process ho chuki hai.');
        }

        AuditLog::record('Wallet Management', 'Reject Withdrawal', "Withdrawal #{$withdrawalRequest->id} rejected");

        return back()->with('status', 'Withdrawal reject kar diya gaya.');
    }

    public function manualAdjust(Request $request, User $user)
    {
        $request->validate([
            'action' => 'required|in:credit,debit',
            'balance_type' => 'required|in:deposit_balance,winnings_balance,bonus_balance',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        $wallet = $user->wallet;

        DB::transaction(function () use ($request, $wallet) {
            if ($request->action === 'credit') {
                $wallet->increment($request->balance_type, $request->amount);
            } else {
                $wallet->decrement($request->balance_type, $request->amount);
            }

            $wallet->transactions()->create([
                'type' => $request->action,
                'amount' => $request->amount,
                'description' => 'Manual adjustment: ' . $request->reason,
                'reference_id' => 'manual-' . now()->timestamp,
            ]);
        });

        AuditLog::record('Wallet Management', 'Manual Adjustment', "User #{$user->id}: {$request->action} {$request->amount} ({$request->balance_type})");

        return back()->with('status', 'Wallet manually adjust ho gaya.');
    }

    public function transactions(Request $request)
    {
        $query = WalletTransaction::with('wallet.user');

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($userId = $request->get('user_id')) {
            $query->whereHas('wallet', fn ($q) => $q->where('user_id', $userId));
        }

        $transactions = $query->latest()->paginate(30)->withQueryString();

        return view('admin.wallet.transactions', compact('transactions'));
    }

    /** Admin moves money from one player's wallet to another's. */
    public function transferForm(Request $request)
    {
        $recent = WalletTransaction::with('wallet.user')->where('type', 'transfer_out')->latest()->take(20)->get();

        return view('admin.wallet.transfer', compact('recent'));
    }

    public function transfer(Request $request)
    {
        $data = $request->validate([
            'from' => 'required|max:255',
            'to' => 'required|max:255',
            'amount' => 'required|numeric|min:' . \App\Services\WalletTransfer::MIN,
            'reason' => 'required|string|max:120',
        ]);

        $from = \App\Services\WalletTransfer::findUser($data['from']);
        $to = \App\Services\WalletTransfer::findUser($data['to']);
        if (! $from || ! $to) {
            return back()->withInput()->withErrors(['from' => (! $from ? 'Bhejne wala' : 'Paane wala') . ' player nahi mila (user ID, email ya phone check karein).']);
        }

        $admin = auth('admin')->user();
        try {
            $ref = \App\Services\WalletTransfer::send($from, $to, (float) $data['amount'], 'admin ' . $admin->name, $data['reason']);
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        }

        AuditLog::record('Wallet Management', 'Transfer', "₹{$data['amount']} from #{$from->id} ({$from->email}) to #{$to->id} ({$to->email}) — {$data['reason']} [{$ref}]");

        return back()->with('status', '₹' . number_format((float) $data['amount'], 2) . " {$from->name} se {$to->name} ko transfer ho gaye. Ref {$ref}");
    }
}
