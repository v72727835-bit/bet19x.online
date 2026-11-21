<x-admin-layout title="Reports">
    <form method="GET" class="fx-card p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="fx-muted text-xs block mb-1">From</label>
            <input type="date" name="from" value="{{ $from }}" class="fx-input text-sm">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">To</label>
            <input type="date" name="to" value="{{ $to }}" class="fx-input text-sm">
        </div>
        <button class="fx-btn text-sm">Apply</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="fx-card p-5">
            <p class="fx-muted text-xs uppercase">Revenue</p>
            <p class="fx-heading text-xl mt-1" style="color: var(--fx-amber);">₹{{ number_format($revenue, 2) }}</p>
        </div>
        <div class="fx-card p-5">
            <p class="fx-muted text-xs uppercase">Payouts</p>
            <p class="fx-heading text-xl mt-1">₹{{ number_format($payouts, 2) }}</p>
        </div>
        <div class="fx-card p-5">
            <p class="fx-muted text-xs uppercase">Profit / Loss</p>
            <p class="fx-heading text-xl mt-1" style="color: {{ $profitLoss >= 0 ? 'var(--fx-green)' : 'var(--fx-red)' }};">₹{{ number_format($profitLoss, 2) }}</p>
        </div>
        <div class="fx-card p-5">
            <p class="fx-muted text-xs uppercase">Active Users</p>
            <p class="fx-heading text-xl mt-1">{{ number_format($activeUsers) }}</p>
        </div>
    </div>

    <div class="fx-card p-5 mb-6 overflow-x-auto">
        <p class="fx-heading text-sm mb-3">Game-wise Report</p>
        <table class="w-full fx-table">
            <thead><tr><th>Sport</th><th>Entries</th><th>Revenue</th></tr></thead>
            <tbody>
                @forelse($gameWise as $g)
                    <tr>
                        <td>{{ $g->name }}</td>
                        <td>{{ $g->entries }}</td>
                        <td>₹{{ number_format($g->revenue, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center fx-muted py-4">Data nahi mili.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="fx-card p-5 mb-6 overflow-x-auto">
        <p class="fx-heading text-sm mb-3">Agent Performance Report</p>
        <table class="w-full fx-table">
            <thead><tr><th>Agent</th><th>Role</th><th>Users</th><th>Commission %</th><th>Net Revenue</th><th>Commission Earned</th></tr></thead>
            <tbody>
                @forelse($agentPerformance as $row)
                    <tr>
                        <td>{{ $row->agent->name }}</td>
                        <td class="fx-muted text-xs">{{ str_replace('_',' ', $row->agent->role) }}</td>
                        <td>{{ $row->agent->users_count }}</td>
                        <td>{{ $row->agent->commission_rate }}%</td>
                        <td style="color: {{ $row->net_revenue >= 0 ? 'var(--fx-green)' : 'var(--fx-red)' }};">₹{{ number_format($row->net_revenue, 2) }}</td>
                        <td>₹{{ number_format($row->commission, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center fx-muted py-4">Koi agent nahi mila.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="fx-card p-5">
        <p class="fx-heading text-sm mb-3">Export Reports (CSV)</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.export', ['type' => 'revenue', 'from' => $from, 'to' => $to]) }}" class="fx-btn-outline text-sm">Revenue Report</a>
            <a href="{{ route('admin.reports.export', ['type' => 'profit-loss', 'from' => $from, 'to' => $to]) }}" class="fx-btn-outline text-sm">Profit & Loss Report</a>
            <a href="{{ route('admin.reports.export', ['type' => 'betting', 'from' => $from, 'to' => $to]) }}" class="fx-btn-outline text-sm">Betting Report</a>
            <a href="{{ route('admin.reports.export', ['type' => 'user-activity', 'from' => $from, 'to' => $to]) }}" class="fx-btn-outline text-sm">User Activity Report</a>
            <a href="{{ route('admin.reports.export', ['type' => 'game-wise', 'from' => $from, 'to' => $to]) }}" class="fx-btn-outline text-sm">Game-wise Report</a>
            <a href="{{ route('admin.reports.export', ['type' => 'agent-performance', 'from' => $from, 'to' => $to]) }}" class="fx-btn-outline text-sm">Agent Performance Report</a>
        </div>
    </div>
</x-admin-layout>
