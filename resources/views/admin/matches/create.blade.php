<x-admin-layout title="Add Match">
    <div class="fx-card p-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.matches.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="fx-muted text-xs block mb-1">Sport</label>
                <select name="sport_id" class="fx-input w-full" required>
                    @foreach($sports as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="fx-muted text-xs block mb-1">Team A</label>
                    <input type="text" name="team_a_name" class="fx-input w-full" required value="{{ old('team_a_name') }}">
                </div>
                <div>
                    <label class="fx-muted text-xs block mb-1">Team B</label>
                    <input type="text" name="team_b_name" class="fx-input w-full" required value="{{ old('team_b_name') }}">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="fx-muted text-xs block mb-1">Start Time</label>
                    <input type="datetime-local" name="start_time" class="fx-input w-full" required>
                </div>
                <div>
                    <label class="fx-muted text-xs block mb-1">Lineup Lock Time</label>
                    <input type="datetime-local" name="lineup_lock_time" class="fx-input w-full" required>
                </div>
            </div>
            <div>
                <label class="fx-muted text-xs block mb-1">Venue</label>
                <input type="text" name="venue" class="fx-input w-full" value="{{ old('venue') }}">
            </div>
            <div>
                <label class="fx-muted text-xs block mb-1">Status</label>
                <select name="status" class="fx-input w-full">
                    <option value="upcoming">Upcoming</option>
                    <option value="live">Live</option>
                    <option value="completed">Completed</option>
                    <option value="suspended">Suspended</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <button class="fx-btn">Create Match</button>
        </form>
    </div>
</x-admin-layout>
