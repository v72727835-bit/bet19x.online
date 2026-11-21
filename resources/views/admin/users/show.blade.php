<x-admin-layout title="User: {{ $user->name }}">

    <a href="{{ route('admin.users.index') }}" class="fx-muted text-sm">&larr; Back to Users</a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
        <div class="fx-card p-5 lg:col-span-1">
            <p class="fx-heading text-lg">{{ $user->name }}</p>
            <p class="fx-muted text-sm">{{ $user->email }}</p>
            <p class="fx-muted text-sm">{{ $user->phone ?? 'Phone not added' }}</p>

            <div class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><span class="fx-muted">Deposit Balance</span><span>₹{{ number_format($user->wallet->deposit_balance ?? 0, 2) }}</span></div>
                <div class="flex justify-between"><span class="fx-muted">Winnings Balance</span><span>₹{{ number_format($user->wallet->winnings_balance ?? 0, 2) }}</span></div>
                <div class="flex justify-between"><span class="fx-muted">Bonus Balance</span><span>₹{{ number_format($user->wallet->bonus_balance ?? 0, 2) }}</span></div>
            </div>

            <div class="mt-5 flex gap-2">
                @if($user->is_blocked)
                    <form method="POST" action="{{ route('admin.users.unblock', $user) }}">
                        @csrf
                        <button class="fx-btn-success text-xs">Unblock User</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.users.block', $user) }}" onsubmit="return confirm('Block this user?');">
                        @csrf
                        <input type="hidden" name="blocked_reason" value="Blocked by admin">
                        <button class="fx-btn-danger text-xs">Block User</button>
                    </form>
                @endif
            </div>

            <div class="mt-6 pt-4" style="border-top: 1px solid var(--fx-border);">
                <p class="fx-heading text-sm mb-2">KYC Status:
                    <span style="color: {{ $user->kyc_status === 'approved' ? 'var(--fx-green)' : ($user->kyc_status === 'rejected' ? 'var(--fx-red)' : 'var(--fx-amber)') }};">
                        {{ ucfirst(str_replace('_',' ',$user->kyc_status)) }}
                    </span>
                </p>
                @if($user->kyc_id_proof_path)
                    <a href="{{ asset('storage/'.$user->kyc_id_proof_path) }}" target="_blank" class="text-xs" style="color: var(--fx-amber);">View ID Proof</a><br>
                @endif
                @if($user->kyc_address_proof_path)
                    <a href="{{ asset('storage/'.$user->kyc_address_proof_path) }}" target="_blank" class="text-xs" style="color: var(--fx-amber);">View Address Proof</a>
                @endif
                @if(!$user->kyc_id_proof_path && !$user->kyc_address_proof_path)
                    <p class="fx-muted text-xs">Koi KYC document upload nahi hua.</p>
                @endif

                <div class="flex gap-2 mt-3">
                    <form method="POST" action="{{ route('admin.users.kyc-approve', $user) }}">
                        @csrf
                        <button class="fx-btn-success text-xs">Approve KYC</button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.kyc-reject', $user) }}" onsubmit="return confirm('Reject KYC?');">
                        @csrf
                        <input type="hidden" name="kyc_remarks" value="Documents unclear / invalid">
                        <button class="fx-btn-danger text-xs">Reject KYC</button>
                    </form>
                </div>
            </div>

            <div class="mt-6 pt-4" style="border-top: 1px solid var(--fx-border);">
                <p class="fx-heading text-sm mb-2">Manual Wallet Adjustment</p>
                <form method="POST" action="{{ route('admin.wallet.adjust', $user) }}" class="space-y-2">
                    @csrf
                    <select name="balance_type" class="fx-input text-xs w-full">
                        <option value="deposit_balance">Deposit Balance</option>
                        <option value="winnings_balance">Winnings Balance</option>
                        <option value="bonus_balance">Bonus Balance</option>
                    </select>
                    <div class="flex gap-2">
                        <select name="action" class="fx-input text-xs">
                            <option value="credit">Credit</option>
                            <option value="debit">Debit</option>
                        </select>
                        <input type="number" step="0.01" name="amount" placeholder="Amount" class="fx-input text-xs flex-1" required>
                    </div>
                    <input type="text" name="reason" placeholder="Reason" class="fx-input text-xs w-full" required>
                    <button class="fx-btn text-xs w-full">Apply Adjustment</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-4">
            <div class="fx-card p-5">
                <p class="fx-heading text-sm mb-3">Contest Entries</p>
                <table class="w-full fx-table">
                    <thead><tr><th>Contest</th><th>Status</th><th>Entry Fee</th><th>Prize Won</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($contestEntries as $entry)
                            <tr>
                                <td>{{ $entry->contest->name ?? '-' }}</td>
                                <td><span class="fx-badge" style="background-color: var(--fx-card-hover);">{{ ucfirst($entry->status) }}</span></td>
                                <td>₹{{ number_format($entry->entry_fee_paid, 2) }}</td>
                                <td>₹{{ number_format($entry->prize_won, 2) }}</td>
                                <td class="fx-muted">{{ $entry->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center fx-muted py-4">Koi entry nahi mili.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="fx-card p-5">
                <p class="fx-heading text-sm mb-3">Recent Wallet Transactions</p>
                <table class="w-full fx-table">
                    <thead><tr><th>Type</th><th>Amount</th><th>Description</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse(optional($user->wallet)->transactions ?? [] as $t)
                            <tr>
                                <td style="color: {{ $t->type === 'credit' ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ ucfirst($t->type) }}</td>
                                <td>₹{{ number_format($t->amount, 2) }}</td>
                                <td class="fx-muted">{{ $t->description }}</td>
                                <td class="fx-muted">{{ $t->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center fx-muted py-4">Koi transaction nahi mili.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="fx-card p-5">
                <p class="fx-heading text-sm mb-3">Login History</p>
                <table class="w-full fx-table">
                    <thead><tr><th>IP</th><th>Device</th><th>Time</th></tr></thead>
                    <tbody>
                        @forelse($loginLogs as $log)
                            <tr>
                                <td>{{ $log->ip_address }}</td>
                                <td class="fx-muted text-xs">{{ \Illuminate\Support\Str::limit($log->device, 40) }}</td>
                                <td class="fx-muted">{{ $log->login_at?->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center fx-muted py-4">Koi login history nahi mili.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
