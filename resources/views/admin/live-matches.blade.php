<x-admin-layout>
    <div style="background-color:#0F1023;" class="min-h-screen py-8">
        <div class="max-w-5xl mx-auto px-4 space-y-6">
            <h2 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-2xl">Live Matches</h2>

            @if (session('status'))
                <div style="background:#1B2A1E; border:1px solid #2E5C33; color:#8CD98F;" class="rounded-lg px-4 py-3 text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div style="background:#2A1B1B; border:1px solid #5C2E2E; color:#D98C8C;" class="rounded-lg px-4 py-3 text-sm">{{ $errors->first() }}</div>
            @endif

            <!-- Add New Match -->
            <div style="border:1px solid #24254A;" class="rounded-2xl p-5">
                <h3 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-lg mb-4">Add New Match</h3>
                <form method="POST" action="{{ route('admin.live-matches.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label style="color:#8A8CB3;" class="block text-xs mb-1">Sport</label>
                        <select name="sport_id" required style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                            <option value="">Select Sport</option>
                            @foreach ($sports as $sport)
                                <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="color:#8A8CB3;" class="block text-xs mb-1">Start Time</label>
                        <input type="datetime-local" name="start_time" required style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label style="color:#8A8CB3;" class="block text-xs mb-1">Team A Name</label>
                        <input type="text" name="team_a_name" required style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label style="color:#8A8CB3;" class="block text-xs mb-1">Team B Name</label>
                        <input type="text" name="team_b_name" required style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label style="color:#8A8CB3;" class="block text-xs mb-1">Venue (optional)</label>
                        <input type="text" name="venue" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" style="background-color:#FFB627; color:#14152B; font-weight:700;" class="rounded-lg px-4 py-2 text-sm hover:opacity-90">Add Match</button>
                    </div>
                </form>
            </div>

            <!-- Existing Matches -->
            <div class="space-y-4">
                @foreach ($matches as $match)
                    <div style="border:1px solid #24254A;" class="rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-base">
                                    {{ $match->team_a_name }} vs {{ $match->team_b_name }}
                                </p>
                                <p style="color:#8A8CB3;" class="text-xs mt-1">
                                    {{ $match->sport->name ?? '' }} · {{ $match->start_time?->format('d M, Y H:i') }} · {{ $match->venue }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('admin.live-matches.destroy', $match) }}" onsubmit="return confirm('Delete karein?')">
                                @csrf
                                @method('DELETE')
                                <button style="color:#FF6B4A;" class="text-xs hover:underline">Delete</button>
                            </form>
                        </div>

                        <form method="POST" action="{{ route('admin.live-matches.score', $match) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                            @csrf
                            <div>
                                <label style="color:#8A8CB3;" class="block text-xs mb-1">Status</label>
                                <select name="status" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                                    <option value="upcoming" @selected($match->status == 'upcoming')>Upcoming</option>
                                    <option value="live" @selected($match->status == 'live')>Live</option>
                                    <option value="completed" @selected($match->status == 'completed')>Completed</option>
                                </select>
                            </div>
                            <div>
                                <label style="color:#8A8CB3;" class="block text-xs mb-1">{{ $match->team_a_name }} Score</label>
                                <input type="text" name="team_a_score" value="{{ $match->team_a_score }}" placeholder="e.g. 145/3" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label style="color:#8A8CB3;" class="block text-xs mb-1">{{ $match->team_b_name }} Score</label>
                                <input type="text" name="team_b_score" value="{{ $match->team_b_score }}" placeholder="e.g. 98/5" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label style="color:#8A8CB3;" class="block text-xs mb-1">Over</label>
                                <input type="text" name="current_over" value="{{ $match->current_over }}" placeholder="e.g. 14.2" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div>
                                <button type="submit" style="background-color:#FFB627; color:#14152B; font-weight:700;" class="rounded-lg px-4 py-2 text-sm hover:opacity-90 w-full">Update</button>
                            </div>
                            <div class="md:col-span-5">
                                <label style="color:#8A8CB3;" class="block text-xs mb-1">Live Summary (optional short text)</label>
                                <input type="text" name="live_summary" value="{{ $match->live_summary }}" placeholder="e.g. Need 12 runs off 8 balls" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-admin-layout>
