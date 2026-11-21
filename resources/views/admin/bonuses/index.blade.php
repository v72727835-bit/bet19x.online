<x-admin-layout title="Bonus Management">
    <div class="fx-card p-5 mb-6 max-w-3xl">
        <p class="fx-heading text-sm mb-3">Create Bonus</p>
        <form method="POST" action="{{ route('admin.bonuses.store') }}" class="grid grid-cols-2 gap-3">
            @csrf
            <input type="text" name="name" placeholder="Bonus Name" class="fx-input text-sm" required>
            <select name="type" class="fx-input text-sm" required>
                <option value="welcome">Welcome</option>
                <option value="cashback">Cashback</option>
                <option value="daily">Daily</option>
                <option value="referral">Referral</option>
                <option value="vip">VIP</option>
                <option value="other">Other</option>
            </select>
            <select name="value_type" class="fx-input text-sm" required>
                <option value="fixed">Fixed (₹)</option>
                <option value="percentage">Percentage (%)</option>
            </select>
            <input type="number" step="0.01" name="value" placeholder="Value" class="fx-input text-sm" required>
            <input type="number" step="0.01" name="max_bonus" placeholder="Max Bonus (optional)" class="fx-input text-sm">
            <input type="number" step="0.01" name="min_deposit" placeholder="Min Deposit (optional)" class="fx-input text-sm">
            <input type="date" name="valid_from" class="fx-input text-sm">
            <input type="date" name="valid_to" class="fx-input text-sm">
            <textarea name="description" placeholder="Description" class="fx-input text-sm col-span-2" rows="2"></textarea>
            <button class="fx-btn text-sm col-span-2">Create Bonus</button>
        </form>
    </div>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>Name</th><th>Type</th><th>Value</th><th>Valid</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($bonuses as $b)
                    <tr>
                        <td>{{ $b->name }}</td>
                        <td class="fx-muted">{{ ucfirst($b->type) }}</td>
                        <td>{{ $b->value_type === 'percentage' ? $b->value.'%' : '₹'.number_format($b->value, 2) }}</td>
                        <td class="fx-muted text-xs">{{ $b->valid_from?->format('d M Y') ?? '-' }} to {{ $b->valid_to?->format('d M Y') ?? '-' }}</td>
                        <td><span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $b->is_active ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ $b->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="space-x-1">
                            <form method="POST" action="{{ route('admin.bonuses.toggle', $b) }}" class="inline">
                                @csrf
                                <button class="fx-btn-outline text-xs">{{ $b->is_active ? 'Disable' : 'Enable' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.bonuses.destroy', $b) }}" class="inline" onsubmit="return confirm('Delete?');">
                                @csrf @method('DELETE')
                                <button class="fx-btn-danger text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center fx-muted py-6">Koi bonus nahi mila.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $bonuses->links() }}</div>
</x-admin-layout>
