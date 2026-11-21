<x-admin-layout title="Edit Match">
    <div class="fx-card p-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.matches.update', $match) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="fx-muted text-xs block mb-1">Sport</label>
                <select name="sport_id" class="fx-input w-full" required>
                    @foreach($sports as $s)
                        <option value="{{ $s->id }}" @selected($match->sport_id == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="fx-muted text-xs block mb-1">Team A</label>
                    <input type="text" name="team_a_name" class="fx-input w-full" required value="{{ $match->team_a_name }}">
                </div>
                <div>
                    <label class="fx-muted text-xs block mb-1">Team B</label>
                    <input type="text" name="team_b_name" class="fx-input w-full" required value="{{ $match->team_b_name }}">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="fx-muted text-xs block mb-1">Start Time</label>
                    <input type="datetime-local" name="start_time" class="fx-input w-full" required value="{{ $match->start_time?->format('Y-m-d\TH:i') }}">
                </div>
                <div>
                    <label class="fx-muted text-xs block mb-1">Lineup Lock Time</label>
                    <input type="datetime-local" name="lineup_lock_time" class="fx-input w-full" required value="{{ $match->lineup_lock_time?->format('Y-m-d\TH:i') }}">
                </div>
            </div>
            <div>
                <label class="fx-muted text-xs block mb-1">Venue</label>
                <input type="text" name="venue" class="fx-input w-full" value="{{ $match->venue }}">
            </div>
            <div>
                <label class="fx-muted text-xs block mb-1">Status</label>
                <select name="status" class="fx-input w-full">
                    @foreach(['upcoming','live','completed','suspended','cancelled'] as $st)
                        <option value="{{ $st }}" @selected($match->status === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="fx-btn">Update Match</button>
        </form>
    </div>
</x-admin-layout>
