<x-admin-layout title="Wallet Transactions">
    <form method="GET" class="fx-card p-4 mb-4 flex flex-wrap gap-3 items-end">
        <select name="type" class="fx-input text-sm">
            <option value="">All Types</option>
            <option value="credit" @selected(request('type')==='credit')>Credit</option>
            <option value="debit" @selected(request('type')==='debit')>Debit</option>
        </select>
        <input type="number" name="user_id" placeholder="User ID" value="{{ request('user_id') }}" class="fx-input text-sm w-32">
        <button class="fx-btn text-sm">Filter</button>
        <a href="{{ route('admin.wallet.transactions') }}" class="fx-btn-outline text-sm">Reset</a>
    </form>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>User</th><th>Type</th><th>Amount</th><th>Description</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($transactions as $t)
                    <tr>
                        <td>{{ $t->wallet->user->name ?? '-' }}</td>
                        <td style="color: {{ $t->type === 'credit' ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ ucfirst($t->type) }}</td>
                        <td>₹{{ number_format($t->amount, 2) }}</td>
                        <td class="fx-muted">{{ $t->description }}</td>
                        <td class="fx-muted">{{ $t->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center fx-muted py-6">Koi transaction nahi mili.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $transactions->links() }}</div>
</x-admin-layout>
