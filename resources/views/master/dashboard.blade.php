<x-master-layout title="Dashboard">

    <div class="mp-grid mp-grid-5" style="margin-bottom:24px;">
        <div class="mp-card" style="padding:20px;">
            <p style="font-size:11px; text-transform:uppercase; color: var(--mp-muted); margin:0;">Total Tenants</p>
            <p style="font-size:26px; margin:6px 0 0 0; font-family:'Rajdhani',sans-serif; font-weight:700;">{{ $totalTenants }}</p>
        </div>
        <div class="mp-card" style="padding:20px;">
            <p style="font-size:11px; text-transform:uppercase; color: var(--mp-muted); margin:0;">Active</p>
            <p style="font-size:26px; margin:6px 0 0 0; font-family:'Rajdhani',sans-serif; font-weight:700; color: var(--mp-green);">{{ $activeTenants }}</p>
        </div>
        <div class="mp-card" style="padding:20px;">
            <p style="font-size:11px; text-transform:uppercase; color: var(--mp-muted); margin:0;">Provisioning</p>
            <p style="font-size:26px; margin:6px 0 0 0; font-family:'Rajdhani',sans-serif; font-weight:700; color:#FBBF24;">{{ $provisioningTenants }}</p>
        </div>
        <div class="mp-card" style="padding:20px;">
            <p style="font-size:11px; text-transform:uppercase; color: var(--mp-muted); margin:0;">Failed</p>
            <p style="font-size:26px; margin:6px 0 0 0; font-family:'Rajdhani',sans-serif; font-weight:700; color: var(--mp-red);">{{ $failedTenants }}</p>
        </div>
        <div class="mp-card" style="padding:20px;">
            <p style="font-size:11px; text-transform:uppercase; color: var(--mp-muted); margin:0;">Suspended</p>
            <p style="font-size:26px; margin:6px 0 0 0; font-family:'Rajdhani',sans-serif; font-weight:700; color: var(--mp-muted);">{{ $suspendedTenants }}</p>
        </div>
    </div>

    <div class="mp-grid mp-grid-2" style="margin-bottom:24px;">
        <div class="mp-card" style="padding:20px;">
            <p style="font-size:11px; text-transform:uppercase; color: var(--mp-muted); margin:0;">Total One-Time Revenue</p>
            <p style="font-size:26px; margin:6px 0 0 0; font-family:'Rajdhani',sans-serif; font-weight:700; color: var(--mp-accent);">₹{{ number_format($totalOneTimeRevenue, 0) }}</p>
        </div>
        <div class="mp-card" style="padding:20px;">
            <p style="font-size:11px; text-transform:uppercase; color: var(--mp-muted); margin:0;">Est. Monthly Recurring Revenue (Subscriptions)</p>
            <p style="font-size:26px; margin:6px 0 0 0; font-family:'Rajdhani',sans-serif; font-weight:700; color: var(--mp-accent);">₹{{ number_format($monthlyRecurringEstimate, 0) }}</p>
        </div>
    </div>

    @if($overdueTenants->isNotEmpty())
    <div class="mp-card" style="padding:20px; margin-bottom:24px; border-color: var(--mp-red);">
        <p style="font-size:13px; font-weight:600; color: var(--mp-red); margin:0 0 12px 0;">⚠ Billing Overdue ({{ $overdueTenants->count() }})</p>
        <table class="mp-table">
            <thead><tr><th>Domain</th><th>Brand</th><th>Amount</th><th>Was Due</th></tr></thead>
            <tbody>
                @foreach($overdueTenants as $t)
                    <tr>
                        <td>{{ $t->domain }}</td>
                        <td>{{ $t->brand_name }}</td>
                        <td>₹{{ number_format($t->price ?? 0, 0) }}</td>
                        <td style="color: var(--mp-red);">{{ $t->next_billing_date->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="mp-card" style="padding:20px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
            <p style="font-size:13px; font-weight:600; margin:0;">Recent Tenants</p>
            <a href="{{ route('master.tenants.index') }}" style="color: var(--mp-accent); font-size:12px; text-decoration:none;">View all &rarr;</a>
        </div>
        <table class="mp-table">
            <thead><tr><th>Domain</th><th>Brand</th><th>Sale</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($recentTenants as $t)
                    <tr>
                        <td>{{ $t->domain }}</td>
                        <td>{{ $t->brand_name }}</td>
                        <td>{{ $t->sale_type === 'subscription' ? 'Subscription' : 'One-Time' }}</td>
                        <td>
                            @php $color = match($t->status) { 'active' => 'var(--mp-green)', 'provisioning' => '#FBBF24', 'failed' => 'var(--mp-red)', default => 'var(--mp-muted)' }; @endphp
                            <span class="mp-badge" style="background-color:{{ $color }}22; color:{{ $color }};">{{ $t->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center; padding:24px; color: var(--mp-muted);">Koi tenant nahi abhi tak.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-master-layout>
