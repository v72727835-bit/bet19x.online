<x-admin-layout title="Dashboard">

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
        <div class="fx-card p-5">
            <p class="fx-muted text-xs uppercase">Total Users</p>
            <p class="fx-heading text-2xl mt-1">{{ number_format($totalUsers) }}</p>
        </div>
        <div class="fx-card p-5">
            <p class="fx-muted text-xs uppercase">Total Revenue</p>
            <p class="fx-heading text-2xl mt-1" style="color: var(--fx-amber);">₹{{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="fx-card p-5">
            <p class="fx-muted text-xs uppercase">Active Entries</p>
            <p class="fx-heading text-2xl mt-1">{{ number_format($activeBets) }}</p>
        </div>
        <div class="fx-card p-5">
            <p class="fx-muted text-xs uppercase">Pending Withdrawals</p>
            <p class="fx-heading text-2xl mt-1" style="color: var(--fx-coral);">{{ number_format($pendingWithdrawals) }}</p>
        </div>
        <div class="fx-card p-5">
            <p class="fx-muted text-xs uppercase">Today's P&L</p>
            <p class="fx-heading text-2xl mt-1" style="color: {{ $todayProfitLoss >= 0 ? 'var(--fx-green)' : 'var(--fx-red)' }};">
                ₹{{ number_format($todayProfitLoss, 2) }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="fx-card p-5 lg:col-span-2">
            <p class="fx-heading text-sm mb-3">Revenue & Volume (Last 7 Days)</p>
            <canvas id="revenueChart" height="90"></canvas>
        </div>
        <div class="fx-card p-5">
            <p class="fx-heading text-sm mb-3">Live / Upcoming Matches</p>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($liveMatches as $m)
                    <div class="flex items-center justify-between text-sm border-b pb-2" style="border-color: var(--fx-border);">
                        <div>
                            <p>{{ $m->team_a_name }} vs {{ $m->team_b_name }}</p>
                            <p class="fx-muted text-xs">{{ $m->sport->name ?? '-' }} · {{ $m->start_time?->format('d M, h:i A') }}</p>
                        </div>
                        <span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $m->status === 'live' ? 'var(--fx-green)' : 'var(--fx-amber)' }};">{{ ucfirst($m->status) }}</span>
                    </div>
                @empty
                    <p class="fx-muted text-sm">Koi match nahi mila.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="fx-card p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="fx-heading text-sm">Recent Transactions</p>
                <a href="{{ route('admin.wallet.transactions') }}" class="text-xs" style="color: var(--fx-amber);">View all →</a>
            </div>
            <table class="w-full fx-table">
                <thead><tr><th>User</th><th>Type</th><th>Amount</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($recentTransactions as $t)
                        <tr>
                            <td>{{ $t->wallet->user->name ?? '-' }}</td>
                            <td><span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $t->type === 'credit' ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ ucfirst($t->type) }}</span></td>
                            <td>₹{{ number_format($t->amount, 2) }}</td>
                            <td class="fx-muted">{{ $t->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="fx-muted text-center py-4">Koi transaction nahi mili.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="fx-card p-5">
            <p class="fx-heading text-sm mb-3">Top Users (By Winnings)</p>
            <table class="w-full fx-table">
                <thead><tr><th>#</th><th>Name</th><th>Total Winnings</th></tr></thead>
                <tbody>
                    @forelse($topUsers as $i => $u)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $u->name }}</td>
                            <td style="color: var(--fx-amber);">₹{{ number_format($u->total_winnings ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="fx-muted text-center py-4">Koi data nahi mila.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('admin.wallet.deposits') }}" class="fx-card fx-hoverable p-5 transition">
            <p style="color: var(--fx-text);" class="font-medium">Pending Deposit Requests</p>
            <p class="fx-muted text-sm mt-1">{{ $pendingDeposits }} requests awaiting approval</p>
        </a>
        <a href="{{ route('admin.wallet.withdrawals') }}" class="fx-card fx-hoverable p-5 transition">
            <p style="color: var(--fx-text);" class="font-medium">Pending Withdrawal Requests</p>
            <p class="fx-muted text-sm mt-1">{{ $pendingWithdrawals }} requests awaiting approval</p>
        </a>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script>
        const ctx = document.getElementById('revenueChart');
        const labels = @json($revenueChart->pluck('label'));
        const revenue = @json($revenueChart->pluck('revenue'));
        const volume = @json($revenueChart->pluck('volume'));
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Revenue (₹)', data: revenue, backgroundColor: '#FFB627', yAxisID: 'y' },
                    { label: 'Entries', data: volume, type: 'line', borderColor: '#FF6B4A', backgroundColor: '#FF6B4A', yAxisID: 'y1' },
                ]
            },
            options: {
                scales: {
                    y: { position: 'left', ticks: { color: '#8A8CB3' }, grid: { color: 'rgba(138,140,179,0.1)' } },
                    y1: { position: 'right', ticks: { color: '#8A8CB3' }, grid: { display: false } },
                    x: { ticks: { color: '#8A8CB3' }, grid: { display: false } },
                },
                plugins: { legend: { labels: { color: '#8A8CB3' } } },
            }
        });
    </script>
</x-admin-layout>
