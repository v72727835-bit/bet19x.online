<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\DepositRequest;
use App\Models\MatchEvent;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $activeBets = ContestEntry::where('status', 'pending')->count();
        $pendingWithdrawals = WithdrawalRequest::where('status', 'pending')->count();
        $pendingDeposits = DepositRequest::where('status', 'pending')->count();

        $totalRevenue = ContestEntry::sum('entry_fee_paid');
        $totalPayouts = ContestEntry::sum('prize_won');

        $today = Carbon::today();
        $todayRevenue = ContestEntry::whereDate('created_at', $today)->sum('entry_fee_paid');
        $todayPayouts = ContestEntry::whereDate('created_at', $today)->sum('prize_won');
        $todayProfitLoss = $todayRevenue - $todayPayouts;

        // Last 7 days revenue & volume for charts
        $days = collect(range(6, 0))->map(fn ($i) => Carbon::today()->subDays($i));
        $revenueChart = $days->map(function ($day) {
            return [
                'label' => $day->format('d M'),
                'revenue' => (float) ContestEntry::whereDate('created_at', $day)->sum('entry_fee_paid'),
                'volume' => ContestEntry::whereDate('created_at', $day)->count(),
            ];
        })->values();

        $recentTransactions = WalletTransaction::with('wallet.user')
            ->latest()
            ->take(10)
            ->get();

        $topUsers = User::withSum('contestEntries as total_winnings', 'prize_won')
            ->orderByDesc('total_winnings')
            ->take(5)
            ->get();

        $liveMatches = MatchEvent::where('status', 'live')
            ->orWhere('status', 'upcoming')
            ->orderBy('start_time')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'activeBets', 'pendingWithdrawals', 'pendingDeposits',
            'totalRevenue', 'totalPayouts', 'todayProfitLoss', 'revenueChart',
            'recentTransactions', 'topUsers', 'liveMatches'
        ));
    }
}
