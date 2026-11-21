<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Contest;
use App\Models\User;
use Illuminate\Http\Request;

class RiskManagementController extends Controller
{
    public function index(Request $request)
    {
        // Exposure = potential prize payout vs collected entry fees, per contest
        $contests = Contest::with('matchEvent')
            ->withCount('entries')
            ->withSum('entries as collected', 'entry_fee_paid')
            ->orderByDesc('id')
            ->take(30)
            ->get()
            ->map(function ($contest) {
                $contest->exposure = (float) $contest->total_prize_pool - (float) ($contest->collected ?? 0);
                return $contest;
            });

        $highLimitUsers = User::orderByDesc('max_contest_entry_limit')->take(20)->get();

        return view('admin.risk.index', compact('contests', 'highLimitUsers'));
    }

    public function updateUserLimits(Request $request, User $user)
    {
        $data = $request->validate([
            'daily_deposit_limit' => 'required|numeric|min:0',
            'max_contest_entry_limit' => 'required|numeric|min:0',
        ]);

        $user->update($data);

        AuditLog::record('Risk Management', 'Update Limits', "Updated limits for user #{$user->id}");

        return back()->with('status', 'User limits update ho gaye.');
    }
}
