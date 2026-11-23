<x-admin-layout title="Transfer between users">
    <form method="POST" action="{{ route('admin.wallet.transfer.store') }}" class="fx-card p-5 max-w-2xl grid grid-cols-1 sm:grid-cols-2 gap-4"
          onsubmit="return confirm('Transfer ₹' + this.amount.value + ' now?');">
        @csrf
        <p class="fx-muted text-sm sm:col-span-2">Money leaves the first player's wallet (deposit balance first, then winnings) and arrives as deposit balance in the second player's wallet. Both players see it in their wallet history.</p>
        <div>
            <label class="fx-muted text-xs block mb-1">From player (user ID, email or phone)</label>
            <input type="text" name="from" value="{{ old('from', request('from')) }}" required class="fx-input w-full">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">To player (user ID, email or phone)</label>
            <input type="text" name="to" value="{{ old('to') }}" required class="fx-input w-full">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Amount (₹)</label>
            <input type="number" step="0.01" min="{{ \App\Services\WalletTransfer::MIN }}" name="amount" value="{{ old('amount') }}" required class="fx-input w-full">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Reason</label>
            <input type="text" name="reason" maxlength="120" value="{{ old('reason') }}" required class="fx-input w-full" placeholder="Shown in both players' history">
        </div>
        <div class="sm:col-span-2"><button class="fx-btn text-sm">Transfer</button></div>
    </form>

    <div class="fx-card overflow-x-auto mt-4">
        <p class="fx-heading text-base px-4 pt-4">Recent transfers (all sources)</p>
        <table class="w-full fx-table">
            <thead><tr><th>Time</th><th>From</th><th>Details</th><th>Amount</th><th>Ref</th></tr></thead>
            <tbody>
                @forelse($recent as $t)
                    <tr>
                        <td class="fx-muted whitespace-nowrap">{{ $t->created_at->format('d M Y H:i') }}</td>
                        <td>{{ $t->wallet->user->name ?? '—' }} <span class="fx-muted text-xs">#{{ $t->wallet->user_id ?? '' }}</span></td>
                        <td class="text-sm">{{ $t->description }}</td>
                        <td>₹{{ number_format($t->amount, 2) }}</td>
                        <td class="fx-muted text-xs">{{ $t->reference_id }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center fx-muted py-6">Abhi tak koi transfer nahi hua.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
