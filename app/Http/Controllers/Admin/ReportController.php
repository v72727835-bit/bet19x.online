<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->get('from') ?: now()->subDays(30)->toDateString();
        $to = $request->get('to') ?: now()->toDateString();

        $revenue = ContestEntry::whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])->sum('entry_fee_paid');
        $payouts = ContestEntry::whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])->sum('prize_won');
        $profitLoss = $revenue - $payouts;

        $betCount = ContestEntry::whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])->count();
        $activeUsers = User::whereHas('contestEntries', function ($q) use ($from, $to) {
            $q->whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"]);
        })->count();

        $gameWise = ContestEntry::join('contests', 'contest_entries.contest_id', '=', 'contests.id')
            ->join('match_events', 'contests.match_event_id', '=', 'match_events.id')
            ->join('sports', 'match_events.sport_id', '=', 'sports.id')
            ->whereBetween('contest_entries.created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])
            ->groupBy('sports.name')
            ->select('sports.name', DB::raw('SUM(contest_entries.entry_fee_paid) as revenue'), DB::raw('COUNT(*) as entries'))
            ->get();

        $agentPerformance = \App\Models\Agent::withCount('users')->get()->map(function ($agent) use ($from, $to) {
            $userIds = $agent->users()->pluck('id');
            $rev = ContestEntry::whereIn('user_id', $userIds)
                ->whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])
                ->sum('entry_fee_paid');
            $pay = ContestEntry::whereIn('user_id', $userIds)
                ->whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])
                ->sum('prize_won');
            $net = $rev - $pay;

            return (object) [
                'agent' => $agent,
                'revenue' => $rev,
                'net_revenue' => $net,
                'commission' => max($net, 0) * ($agent->commission_rate / 100),
            ];
        });

        return view('admin.reports.index', compact(
            'from', 'to', 'revenue', 'payouts', 'profitLoss', 'betCount', 'activeUsers', 'gameWise', 'agentPerformance'
        ));
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        $from = $request->get('from') ?: now()->subDays(30)->toDateString();
        $to = $request->get('to') ?: now()->toDateString();

        $filename = "{$type}-report-{$from}-to-{$to}.csv";

        return response()->streamDownload(function () use ($type, $from, $to) {
            $out = fopen('php://output', 'w');

            if ($type === 'revenue' || $type === 'profit-loss') {
                fputcsv($out, ['Date', 'Revenue', 'Payouts', 'Profit/Loss']);
                $entries = ContestEntry::whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])
                    ->selectRaw('DATE(created_at) as d, SUM(entry_fee_paid) as rev, SUM(prize_won) as pay')
                    ->groupBy('d')->orderBy('d')->get();
                foreach ($entries as $row) {
                    fputcsv($out, [$row->d, $row->rev, $row->pay, $row->rev - $row->pay]);
                }
            } elseif ($type === 'betting') {
                fputcsv($out, ['Entry ID', 'User', 'Contest', 'Status', 'Entry Fee', 'Prize Won', 'Date']);
                ContestEntry::with(['user', 'contest'])
                    ->whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])
                    ->chunk(500, function ($chunk) use ($out) {
                        foreach ($chunk as $e) {
                            fputcsv($out, [$e->id, $e->user->name ?? '-', $e->contest->name ?? '-', $e->status, $e->entry_fee_paid, $e->prize_won, $e->created_at]);
                        }
                    });
            } elseif ($type === 'user-activity') {
                fputcsv($out, ['User ID', 'Name', 'Email', 'Total Entries', 'Total Winnings', 'Joined']);
                User::withCount('contestEntries')->withSum('contestEntries as winnings', 'prize_won')
                    ->chunk(500, function ($chunk) use ($out) {
                        foreach ($chunk as $u) {
                            fputcsv($out, [$u->id, $u->name, $u->email, $u->contest_entries_count, $u->winnings ?? 0, $u->created_at]);
                        }
                    });
            } elseif ($type === 'game-wise') {
                fputcsv($out, ['Sport', 'Total Entries', 'Total Revenue']);
                $rows = ContestEntry::join('contests', 'contest_entries.contest_id', '=', 'contests.id')
                    ->join('match_events', 'contests.match_event_id', '=', 'match_events.id')
                    ->join('sports', 'match_events.sport_id', '=', 'sports.id')
                    ->whereBetween('contest_entries.created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])
                    ->groupBy('sports.name')
                    ->select('sports.name', DB::raw('COUNT(*) as entries'), DB::raw('SUM(contest_entries.entry_fee_paid) as revenue'))
                    ->get();
                foreach ($rows as $row) {
                    fputcsv($out, [$row->name, $row->entries, $row->revenue]);
                }
            } elseif ($type === 'agent-performance') {
                fputcsv($out, ['Agent', 'Role', 'Users', 'Commission %', 'Revenue', 'Net Revenue', 'Commission Earned']);
                $agents = \App\Models\Agent::withCount('users')->get();
                foreach ($agents as $agent) {
                    $userIds = $agent->users()->pluck('id');
                    $rev = ContestEntry::whereIn('user_id', $userIds)->whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])->sum('entry_fee_paid');
                    $pay = ContestEntry::whereIn('user_id', $userIds)->whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])->sum('prize_won');
                    $net = $rev - $pay;
                    fputcsv($out, [$agent->name, $agent->role, $agent->users_count, $agent->commission_rate, $rev, $net, max($net, 0) * ($agent->commission_rate / 100)]);
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
