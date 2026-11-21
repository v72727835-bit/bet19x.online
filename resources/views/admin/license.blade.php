<x-admin-layout title="License / Subscription">

    @if(!$tenant)
        <div class="fx-card p-8 text-center fx-muted">
            Yeh copy kisi reseller ke Master Panel se provision nahi hui hai — koi license record nahi mila.
            (Yeh original/direct install hai, white-label resold copy nahi.)
        </div>
    @else
        @php
            $daysLeft = $tenant->license_expires_at ? now()->diffInDays($tenant->license_expires_at, false) : null;
            $isExpired = $tenant->license_expires_at && $tenant->license_expires_at->isPast();
            $isBillingOverdue = $tenant->isBillingOverdue();
        @endphp

        @if($isExpired)
            <div class="fx-card p-5 mb-6" style="border-color: var(--fx-red);">
                <p style="color: var(--fx-red); font-weight:600;">⚠ Aapka license expire ho chuka hai ({{ $tenant->license_expires_at->format('d M Y') }} ko). Kripya apne provider se renewal ke liye contact karein.</p>
            </div>
        @elseif($daysLeft !== null && $daysLeft <= 7)
            <div class="fx-card p-5 mb-6" style="border-color: #FBBF24;">
                <p style="color: #FBBF24; font-weight:600;">⏳ Aapka license {{ $daysLeft }} din mein expire hone wala hai ({{ $tenant->license_expires_at->format('d M Y') }}). Renewal ke liye apne provider se contact karein.</p>
            </div>
        @endif

        @if($isBillingOverdue)
            <div class="fx-card p-5 mb-6" style="border-color: var(--fx-red);">
                <p style="color: var(--fx-red); font-weight:600;">⚠ Aapki subscription payment due date nikal chuki hai ({{ $tenant->next_billing_date->format('d M Y') }}). Kripya payment complete karein.</p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="fx-card p-5">
                <p class="fx-muted text-xs uppercase">Plan Type</p>
                <p class="fx-heading text-xl mt-1" style="color: var(--fx-amber);">
                    {{ $tenant->sale_type === 'subscription' ? 'Subscription' : 'One-Time Purchase' }}
                </p>
            </div>
            <div class="fx-card p-5">
                <p class="fx-muted text-xs uppercase">{{ $tenant->sale_type === 'subscription' ? 'Billing Amount' : 'Purchase Price' }}</p>
                <p class="fx-heading text-xl mt-1">₹{{ number_format($tenant->price ?? 0, 2) }}
                    @if($tenant->sale_type === 'subscription' && $tenant->billing_cycle)
                        <span class="fx-muted text-sm">/ {{ str_replace('_', ' ', $tenant->billing_cycle) }}</span>
                    @endif
                </p>
            </div>
            <div class="fx-card p-5">
                <p class="fx-muted text-xs uppercase">Status</p>
                @php
                    $statusColor = match($tenant->status) { 'active' => 'var(--fx-green)', 'suspended' => 'var(--fx-red)', default => 'var(--fx-muted)' };
                @endphp
                <p class="fx-heading text-xl mt-1" style="color: {{ $statusColor }};">{{ ucfirst($tenant->status) }}</p>
            </div>
        </div>

        <div class="fx-card p-6 mb-6">
            <p class="fx-heading text-sm mb-4">License Details</p>
            <table class="w-full fx-table">
                <tbody>
                    <tr><td class="fx-muted" style="width:220px;">Brand / Domain</td><td>{{ $tenant->brand_name }} ({{ $tenant->domain }})</td></tr>
                    <tr><td class="fx-muted">Plan Type</td><td>{{ $tenant->sale_type === 'subscription' ? 'Subscription' : 'One-Time Purchase' }}</td></tr>
                    @if($tenant->sale_type === 'subscription')
                        <tr><td class="fx-muted">Billing Cycle</td><td>{{ ucfirst(str_replace('_',' ', $tenant->billing_cycle ?? '—')) }}</td></tr>
                        <tr>
                            <td class="fx-muted">Next Billing Date</td>
                            <td style="color: {{ $isBillingOverdue ? 'var(--fx-red)' : 'var(--fx-text)' }};">
                                {{ $tenant->next_billing_date?->format('d M Y') ?? '—' }}
                                @if($isBillingOverdue) (overdue) @endif
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td class="fx-muted">License Expiry</td>
                        <td style="color: {{ $isExpired ? 'var(--fx-red)' : 'var(--fx-text)' }};">
                            {{ $tenant->license_expires_at?->format('d M Y') ?? 'No expiry set' }}
                            @if($daysLeft !== null && !$isExpired)
                                <span class="fx-muted">({{ $daysLeft }} din baaki)</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="fx-card p-5 text-sm fx-muted">
            License ya billing se related koi bhi sawaal ho to apne platform provider se seedha contact karein — yeh unhi ke through renew/upgrade hoti hai.
        </div>
    @endif

</x-admin-layout>
