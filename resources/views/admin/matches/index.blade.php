<x-admin-layout title="Match Management">

    <div class="flex justify-between items-center mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <select name="sport_id" class="fx-input text-sm" onchange="this.form.submit()">
                <option value="">All Sports</option>
                @foreach($sports as $s)
                    <option value="{{ $s->id }}" @selected(request('sport_id') == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
            <select name="status" class="fx-input text-sm" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="upcoming" @selected(request('status')==='upcoming')>Upcoming</option>
                <option value="live" @selected(request('status')==='live')>Live</option>
                <option value="completed" @selected(request('status')==='completed')>Completed</option>
                <option value="suspended" @selected(request('status')==='suspended')>Suspended</option>
                <option value="cancelled" @selected(request('status')==='cancelled')>Cancelled</option>
            </select>
        </form>
        <a href="{{ route('admin.matches.create') }}" class="fx-btn text-sm">+ Add Match</a>
    </div>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>Match</th><th>Sport</th><th>Start Time</th><th>Contests</th><th>Status</th><th>Result</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($matches as $m)
                    <tr>
                        <td>{{ $m->team_a_name }} vs {{ $m->team_b_name }}</td>
                        <td>{{ $m->sport->name ?? '-' }}</td>
                        <td class="fx-muted">{{ $m->start_time?->format('d M Y, h:i A') }}</td>
                        <td>{{ $m->contests_count }}</td>
                        <td>
                            <span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $m->status === 'live' ? 'var(--fx-green)' : ($m->status === 'suspended' ? 'var(--fx-red)' : 'var(--fx-amber)') }};">
                                {{ ucfirst($m->status) }}
                            </span>
                        </td>
                        <td class="fx-muted text-xs">{{ $m->winner_team ?? '-' }}</td>
                        <td class="space-x-1 whitespace-nowrap">
                            <a href="{{ route('admin.matches.edit', $m) }}" class="fx-btn-outline text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.matches.toggle-suspend', $m) }}" class="inline">
                                @csrf
                                <button class="fx-btn-outline text-xs">{{ $m->status === 'suspended' ? 'Unsuspend' : 'Suspend' }}</button>
                            </form>
                            <button onclick="document.getElementById('result-{{ $m->id }}').classList.toggle('hidden')" class="fx-btn-outline text-xs">Result</button>
                            <form method="POST" action="{{ route('admin.matches.destroy', $m) }}" class="inline" onsubmit="return confirm('Delete this match?');">
                                @csrf @method('DELETE')
                                <button class="fx-btn-danger text-xs">Delete</button>
                            </form>
                            <div id="result-{{ $m->id }}" class="hidden mt-2">
                                <form method="POST" action="{{ route('admin.matches.declare-result', $m) }}" class="space-y-1">
                                    @csrf
                                    <input type="text" name="winner_team" placeholder="Winner team" class="fx-input text-xs w-full" value="{{ $m->winner_team }}" required>
                                    <input type="text" name="result_summary" placeholder="Result summary" class="fx-input text-xs w-full" value="{{ $m->result_summary }}">
                                    <button class="fx-btn text-xs w-full">Declare Result</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center fx-muted py-6">Koi match nahi mila.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $matches->links() }}</div>
</x-admin-layout>
