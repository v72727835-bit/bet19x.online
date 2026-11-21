<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

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

        return view('admin.users.show', compact('user', 'userTeams', 'contestEntries', 'loginLogs'));
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
