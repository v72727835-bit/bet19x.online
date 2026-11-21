<x-admin-layout title="Promo Codes">
    <div class="fx-card p-5 mb-6 max-w-2xl">
        <p class="fx-heading text-sm mb-3">Create Promo Code</p>
        <form method="POST" action="{{ route('admin.promo-codes.store') }}" class="grid grid-cols-2 gap-3">
            @csrf
            <input type="text" name="code" placeholder="CODE (e.g. WELCOME100)" class="fx-input text-sm">
            <select name="value_type" class="fx-input text-sm">
                <option value="fixed">Fixed (₹)</option>
                <option value="percentage">Percentage (%)</option>
            </select>
            <input type="number" step="0.01" name="value" placeholder="Value" class="fx-input text-sm" required>
            <input type="number" name="usage_limit" placeholder="Usage limit (optional)" class="fx-input text-sm">
            <input type="datetime-local" name="expires_at" class="fx-input text-sm col-span-2">
            <button class="fx-btn text-sm col-span-2">Create Promo Code</button>
        </form>
    </div>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>Code</th><th>Value</th><th>Usage</th><th>Expires</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($promoCodes as $p)
                    <tr>
                        <td class="font-mono">{{ $p->code }}</td>
                        <td>{{ $p->value_type === 'percentage' ? $p->value.'%' : '₹'.number_format($p->value, 2) }}</td>
                        <td>{{ $p->used_count }}/{{ $p->usage_limit ?? '∞' }}</td>
                        <td class="fx-muted text-xs">{{ $p->expires_at?->format('d M Y') ?? 'Never' }}</td>
                        <td><span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $p->is_active ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ $p->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="space-x-1">
                            <form method="POST" action="{{ route('admin.promo-codes.toggle', $p) }}" class="inline">
                                @csrf
                                <button class="fx-btn-outline text-xs">{{ $p->is_active ? 'Disable' : 'Enable' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.promo-codes.destroy', $p) }}" class="inline" onsubmit="return confirm('Delete?');">
                                @csrf @method('DELETE')
                                <button class="fx-btn-danger text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center fx-muted py-6">Koi promo code nahi mila.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $promoCodes->links() }}</div>
</x-admin-layout>
