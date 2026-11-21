<x-app-layout>
    <div style="background-color:#14152B; min-height:100vh;">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-10">

            <a href="{{ route('in-play.index') }}" style="color:#8A8CB3;" class="text-sm hover:text-[#FFB627] transition">&larr; Back to In Play</a>

            <!-- Scoreboard -->
            <div class="rounded-2xl p-6 mt-4 mb-8" style="background:linear-gradient(145deg, #1D1E3D 0%, #181933 100%); border:1px solid #24254A;">
                <div class="flex items-center justify-between mb-4">
                    <span style="background-color:{{ $matchEvent->status == 'live' ? '#FF6B4A' : '#24254A' }}; color:{{ $matchEvent->status == 'live' ? '#4A190B' : '#8A8CB3' }}; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-xs px-3 py-1 rounded-full uppercase">
                        {{ $matchEvent->status }}
                    </span>
                    <span style="color:#8A8CB3;" class="text-xs">{{ $matchEvent->venue }}</span>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="text-center">
                        <p style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-lg">{{ $matchEvent->team_a_name }}</p>
                        <p style="color:#FFB627; font-family:'Rajdhani',sans-serif;" class="text-2xl font-bold mt-1">{{ $matchEvent->team_a_score ?? '-' }}</p>
                    </div>
                    <div class="text-center">
                        <p style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-lg">{{ $matchEvent->team_b_name }}</p>
                        <p style="color:#FF6B4A; font-family:'Rajdhani',sans-serif;" class="text-2xl font-bold mt-1">{{ $matchEvent->team_b_score ?? '-' }}</p>
                    </div>
                </div>

                @if ($matchEvent->current_over)
                    <p style="color:#8A8CB3;" class="text-center text-sm mt-4">Over: {{ $matchEvent->current_over }}</p>
                @endif
                @if ($matchEvent->live_summary)
                    <p style="color:#F4F3FA;" class="text-center text-sm mt-2 font-medium">{{ $matchEvent->live_summary }}</p>
                @endif
            </div>

            <!-- Contests + Leaderboards -->
            @forelse ($contests as $contest)
                <div class="rounded-2xl p-6 mb-6" style="background:#1D1E3D; border:1px solid #24254A;">
                    <h3 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-lg mb-4">
                        {{ $contest->name }}
                    </h3>

                    @if ($contest->entries->isEmpty())
                        <p style="color:#8A8CB3;" class="text-sm">Koi entry nahi mili.</p>
                    @else
                        <div class="space-y-2">
                            @foreach ($contest->entries as $index => $entry)
                                <div class="flex items-center justify-between rounded-xl px-4 py-3" style="background:#14152B; border:1px solid #24254A;">
                                    <div class="flex items-center gap-3">
                                        <span style="color:#FFB627; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-sm w-6">
                                            #{{ $entry->rank ?? ($index + 1) }}
                                        </span>
                                        <div>
                                            <p style="color:#F4F3FA;" class="text-sm font-medium">
                                                {{ $entry->userTeam->team_name ?? '—' }}
                                            </p>
                                            <p style="color:#6B6D91;" class="text-xs">{{ $entry->user->name ?? '' }}</p>
                                        </div>
                                    </div>
                                    <span style="color:#FF6B4A; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-base">
                                        {{ $entry->points ?? 0 }} pts
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl py-16 text-center" style="background:#1D1E3D; border:1px solid #24254A;">
                    <p style="color:#8A8CB3;">Is match ke liye koi contest nahi mila.</p>
                </div>
            @endforelse
        </div>
    </div>
    <x-bottom-nav />
</x-app-layout>
