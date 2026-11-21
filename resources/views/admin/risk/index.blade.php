<x-admin-layout title="Risk & Exposure">

    <div class="fx-card p-5 mb-6 overflow-x-auto">
        <p class="fx-heading text-sm mb-3">Contest Exposure (Prize Pool vs Collected Fees)</p>
        <table class="w-full fx-table">
            <thead><tr><th>Contest</th><th>Match</th><th>Entries</th><th>Prize Pool</th><th>Collected</th><th>Exposure</th></tr></thead>
            <tbody>
                @forelse($contests as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td class="fx-muted text-xs">{{ $c->matchEvent->team_a_name ?? '' }} vs {{ $c->matchEvent->team_b_name ?? '' }}</td>
                        <td>{{ $c->entries_count }}</td>
                        <td>₹{{ number_format($c->total_prize_pool, 2) }}</td>
                        <td>₹{{ number_format($c->collected ?? 0, 2) }}</td>
                        <td style="color: {{ $c->exposure > 0 ? 'var(--fx-red)' : 'var(--fx-green)' }};">₹{{ number_format($c->exposure, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center fx-muted py-6">Koi contest nahi mila.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="fx-card p-5 overflow-x-auto">
        <p class="fx-heading text-sm mb-3">User Betting Limits</p>
        <table class="w-full fx-table">
            <thead><tr><th>User</th><th>Daily Deposit Limit</th><th>Max Entry Limit</th><th>Update</th></tr></thead>
            <tbody>
                @forelse($highLimitUsers as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>₹{{ number_format($u->daily_deposit_limit, 2) }}</td>
                        <td>₹{{ number_format($u->max_contest_entry_limit, 2) }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.risk.update-limits', $u) }}" class="flex gap-1">
                                @csrf
                                <input type="number" step="0.01" name="daily_deposit_limit" value="{{ $u->daily_deposit_limit }}" class="fx-input text-xs w-24">
                                <input type="number" step="0.01" name="max_contest_entry_limit" value="{{ $u->max_contest_entry_limit }}" class="fx-input text-xs w-24">
                                <button class="fx-btn-outline text-xs">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center fx-muted py-6">Koi user nahi mila.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
