<x-admin-layout title="Bet / Entry Management">

    <form method="GET" class="fx-card p-4 mb-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="fx-muted text-xs block mb-1">Match</label>
            <select name="match_event_id" class="fx-input text-sm">
                <option value="">All Matches</option>
                @foreach($matches as $m)
                    <option value="{{ $m->id }}" @selected(request('match_event_id') == $m->id)>{{ $m->team_a_name }} vs {{ $m->team_b_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">User ID</label>
            <input type="number" name="user_id" value="{{ request('user_id') }}" class="fx-input text-sm w-24">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Status</label>
            <select name="status" class="fx-input text-sm">
                <option value="">All</option>
                <option value="pending" @selected(request('status')==='pending')>Pending</option>
                <option value="won" @selected(request('status')==='won')>Won</option>
                <option value="lost" @selected(request('status')==='lost')>Lost</option>
                <option value="void" @selected(request('status')==='void')>Void</option>
                <option value="cancelled" @selected(request('status')==='cancelled')>Cancelled</option>
            </select>
        </div>
        <button class="fx-btn text-sm">Filter</button>
        <a href="{{ route('admin.bets.index') }}" class="fx-btn-outline text-sm">Reset</a>
    </form>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead>
                <tr><th>Entry #</th><th>User</th><th>Contest</th><th>Match</th><th>Entry Fee</th><th>Prize</th><th>Status</th><th>Settle</th></tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr>
                        <td>#{{ $entry->id }}</td>
                        <td>{{ $entry->user->name ?? '-' }}</td>
                        <td>{{ $entry->contest->name ?? '-' }}</td>
                        <td class="fx-muted text-xs">{{ $entry->contest->matchEvent->team_a_name ?? '' }} vs {{ $entry->contest->matchEvent->team_b_name ?? '' }}</td>
                        <td>₹{{ number_format($entry->entry_fee_paid, 2) }}</td>
                        <td>₹{{ number_format($entry->prize_won, 2) }}</td>
                        <td><span class="fx-badge" style="background-color: var(--fx-card-hover);">{{ ucfirst($entry->status) }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('admin.bets.settle', $entry) }}" class="flex gap-1 items-center">
                                @csrf
                                <select name="status" class="fx-input text-xs">
                                    <option value="won">Win</option>
                                    <option value="lost">Loss</option>
                                    <option value="void">Void</option>
                                    <option value="cancelled">Cancel</option>
                                </select>
                                <input type="number" step="0.01" name="prize_won" placeholder="Prize" class="fx-input text-xs w-20">
                                <button class="fx-btn-outline text-xs">Go</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center fx-muted py-6">Koi entry nahi mili.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $entries->links() }}</div>
</x-admin-layout>
