<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AstechBetLog;
use App\Models\AuditLog;
use App\Models\DepositRequest;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\GameCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('wallet')->withCount('contestEntries');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            if ($status === 'blocked') {
                $query->where('is_blocked', true);
            } elseif ($status === 'active') {
                $query->where('is_blocked', false);
            }
        }

        if ($kyc = $request->get('kyc_status')) {
            $query->where('kyc_status', $kyc);
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['wallet.transactions' => fn ($q) => $q->latest()->take(20)]);
        $userTeams = $user->userTeams()->with('matchEvent')->latest()->take(10)->get();
        $contestEntries = $user->contestEntries()->with('contest')->latest()->take(20)->get();
        $loginLogs = $user->loginLogs()->latest()->take(10)->get();

        $betBase = AstechBetLog::where('user_id', (string) $user->id);
        $betTotals = (clone $betBase)->where('rolled_back', false)
            ->selectRaw('COUNT(*) as rounds, COALESCE(SUM(bet),0) as bet, COALESCE(SUM(win),0) as win')->first();
        $gameBets = (clone $betBase)->latest('id')->take(25)->get();
        $games = $gameBets->isEmpty() ? [] : GameCatalog::lookup();

        return view('admin.users.show', compact('user', 'userTeams', 'contestEntries', 'loginLogs', 'gameBets', 'betTotals', 'games'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique($user->getConnectionName() . '.users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'new_password' => 'nullable|string|min:8|max:100',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $changedPassword = ! empty($data['new_password']);
        if ($changedPassword) {
            $user->password = Hash::make($data['new_password']);
        }
        $user->save();

        AuditLog::record('User Management', 'Edit User', "Edited user #{$user->id} ({$user->email})" . ($changedPassword ? ' and set a new password' : ''));

        return redirect()->route('admin.users.show', $user)
            ->with('status', $changedPassword ? 'User update ho gaya aur naya password set ho gaya.' : 'User update ho gaya.');
    }

    public function destroy(User $user)
    {
        $label = "#{$user->id} ({$user->email})";

        DB::connection($user->getConnectionName())->transaction(function () use ($user) {
            $wallet = Wallet::where('user_id', $user->id)->first();
            if ($wallet) {
                WalletTransaction::where('wallet_id', $wallet->id)->delete();
                $wallet->delete();
            }
            DepositRequest::where('user_id', $user->id)->delete();
            WithdrawalRequest::where('user_id', $user->id)->delete();
            $conv = SupportConversation::where('user_id', $user->id)->first();
            if ($conv) {
                SupportMessage::where('conversation_id', $conv->id)->delete();
                $conv->delete();
            }
            // Game bet logs are kept as an audit trail.
            $user->delete();
        });

        AuditLog::record('User Management', 'Delete User', "Deleted user {$label}");

        return redirect()->route('admin.users.index')->with('status', "User {$label} delete ho gaya.");
    }

    /** Admin opens the player site as this user (admin session stays signed in). */
    public function loginAs(Request $request, User $user)
    {
        Auth::guard('web')->login($user);
        $request->session()->put('impersonator_admin_id', auth('admin')->id());

        AuditLog::record('User Management', 'Login As User', "Logged in as user #{$user->id} ({$user->email})");

        return redirect()->route('dashboard');
    }

    public function leaveImpersonation(Request $request)
    {
        $userId = Auth::guard('web')->id();
        $adminId = $request->session()->pull('impersonator_admin_id');
        Auth::guard('web')->logout();

        if ($adminId && auth('admin')->check()) {
            return redirect()->route('admin.users.show', $userId);
        }

        return redirect()->route('login');
    }

    public function block(Request $request, User $user)
    {
        $request->validate(['blocked_reason' => 'nullable|string|max:255']);

        $user->update([
            'is_blocked' => true,
            'blocked_reason' => $request->blocked_reason,
            'blocked_at' => now(),
        ]);

        AuditLog::record('User Management', 'Block User', "Blocked user #{$user->id} ({$user->email})");

        return back()->with('status', 'User block ho gaya.');
    }

    public function unblock(User $user)
    {
        $user->update([
            'is_blocked' => false,
            'blocked_reason' => null,
            'blocked_at' => null,
        ]);

        AuditLog::record('User Management', 'Unblock User', "Unblocked user #{$user->id} ({$user->email})");

        return back()->with('status', 'User unblock ho gaya.');
    }

    public function kycApprove(User $user)
    {
        $user->update(['kyc_status' => 'approved', 'kyc_remarks' => null]);

        AuditLog::record('User Management', 'KYC Approve', "Approved KYC for user #{$user->id}");

        return back()->with('status', 'KYC approve ho gaya.');
    }

    public function kycReject(Request $request, User $user)
    {
        $request->validate(['kyc_remarks' => 'required|string|max:255']);

        $user->update(['kyc_status' => 'rejected', 'kyc_remarks' => $request->kyc_remarks]);

        AuditLog::record('User Management', 'KYC Reject', "Rejected KYC for user #{$user->id}: {$request->kyc_remarks}");

        return back()->with('status', 'KYC reject kar diya gaya.');
    }
}
