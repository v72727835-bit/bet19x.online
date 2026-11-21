<x-admin-layout title="VIP & Loyalty">

    <div class="fx-card p-5 mb-6">
        <p class="fx-heading text-sm mb-3">Loyalty Points Settings</p>
        <form method="POST" action="{{ route('admin.vip.settings') }}" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div>
                <label class="fx-muted text-xs block mb-1">Points per ₹100 Played</label>
                <input type="number" step="0.01" name="loyalty_points_per_100_played" value="{{ $setting->loyalty_points_per_100_played }}" class="fx-input">
            </div>
            <div>
                <label class="fx-muted text-xs block mb-1">Point Value (₹ per point)</label>
                <input type="number" step="0.0001" name="loyalty_point_value" value="{{ $setting->loyalty_point_value }}" class="fx-input">
            </div>
            <button class="fx-btn text-sm">Save Settings</button>
        </form>
    </div>

    <div class="fx-card p-5 mb-6">
        <p class="fx-heading text-sm mb-3">Naya VIP Tier Banao</p>
        <form method="POST" action="{{ route('admin.vip.store') }}" class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @csrf
            <input type="text" name="name" placeholder="Tier Name (e.g. Gold)" class="fx-input" required>
            <input type="number" name="min_points" placeholder="Min Points Required" class="fx-input" required>
            <input type="number" step="0.01" name="cashback_percent" placeholder="Cashback %" class="fx-input" required>
            <input type="number" step="0.01" name="deposit_bonus_percent" placeholder="Deposit Bonus %" class="fx-input" required>
            <input type="color" name="badge_color" value="#FFB627" class="fx-input h-10">
            <input type="number" name="sort_order" placeholder="Sort Order" class="fx-input">
            <input type="text" name="perks" placeholder="Perks (comma separated)" class="fx-input col-span-2">
            <button class="fx-btn text-sm">+ Add Tier</button>
        </form>
    </div>

    <div class="fx-card p-5 overflow-x-auto">
        <p class="fx-heading text-sm mb-3">VIP Tiers</p>
        <table class="w-full fx-table">
            <thead><tr><th>Tier</th><th>Min Points</th><th>Cashback %</th><th>Deposit Bonus %</th><th>Users</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($tiers as $t)
                    <tr>
                        <td><span class="fx-badge" style="background-color: {{ $t->badge_color }}22; color: {{ $t->badge_color }};">{{ $t->name }}</span></td>
                        <td>{{ number_format($t->min_points) }}</td>
                        <td>{{ $t->cashback_percent }}%</td>
                        <td>{{ $t->deposit_bonus_percent }}%</td>
                        <td>{{ $t->users_count }}</td>
                        <td>
                            <span class="fx-badge" style="background-color: {{ $t->is_active ? 'rgba(52,211,153,.15)' : 'rgba(248,113,113,.15)' }}; color: {{ $t->is_active ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ $t->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="flex gap-1">
                            <form method="POST" action="{{ route('admin.vip.toggle', $t) }}">
                                @csrf
                                <button class="fx-btn-outline text-xs">{{ $t->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.vip.destroy', $t) }}" onsubmit="return confirm('Delete this tier?')">
                                @csrf @method('DELETE')
                                <button class="fx-btn-danger text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center fx-muted py-6">Koi VIP tier nahi bana. Upar se add karo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-admin-layout>
