<x-app-layout>
    <div style="background-color:#14152B; min-height:100vh;">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-10">

            <a href="{{ route('contests.index') }}" style="color:#8A8CB3;" class="text-sm hover:text-[#FFB627] transition">&larr; Back to Contests</a>

            @if (session('status'))
                <div style="background:#1B2A1E; border:1px solid #2E5C33; color:#8CD98F;" class="rounded-lg px-4 py-3 text-sm mt-4">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div style="background:#2A1B1B; border:1px solid #5C2E2E; color:#D98C8C;" class="rounded-lg px-4 py-3 text-sm mt-4">{{ session('error') }}</div>
            @endif

            <div class="rounded-2xl p-6 sm:p-8 mt-4" style="background:linear-gradient(145deg, #1D1E3D 0%, #181933 100%); border:1px solid #24254A;">
                <h1 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-2xl sm:text-3xl mb-2">
                    {{ $contest->name }}
                </h1>
                <p style="color:#8A8CB3;" class="text-sm mb-6">
                    {{ $contest->matchEvent->team_a_name }} vs {{ $contest->matchEvent->team_b_name }}
                    · {{ $contest->matchEvent->sport->name ?? '' }}
                    · {{ $contest->matchEvent->start_time?->format('d M, Y H:i') }}
                </p>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                    <div class="rounded-xl p-4 text-center" style="background:#14152B; border:1px solid #24254A;">
                        <p style="color:#8A8CB3;" class="text-xs">Prize Pool</p>
                        <p style="color:#FFB627; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-lg mt-1">₹{{ number_format($contest->total_prize_pool) }}</p>
                    </div>
                    <div class="rounded-xl p-4 text-center" style="background:#14152B; border:1px solid #24254A;">
                        <p style="color:#8A8CB3;" class="text-xs">Entry Fee</p>
                        <p style="color:{{ $contest->entry_fee > 0 ? '#FF6B4A' : '#4ADE80' }}; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-lg mt-1">
                            {{ $contest->entry_fee > 0 ? '₹' . number_format($contest->entry_fee) : 'FREE' }}
                        </p>
                    </div>
                    <div class="rounded-xl p-4 text-center" style="background:#14152B; border:1px solid #24254A;">
                        <p style="color:#8A8CB3;" class="text-xs">Spots Filled</p>
                        <p style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-lg mt-1">{{ $contest->current_participants }}/{{ $contest->max_participants }}</p>
                    </div>
                    <div class="rounded-xl p-4 text-center" style="background:#14152B; border:1px solid #24254A;">
                        <p style="color:#8A8CB3;" class="text-xs">Team Size</p>
                        <p style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-lg mt-1">11 Players</p>
                    </div>
                </div>

                @if ($alreadyJoined)
                    <div style="background-color:#24254A; color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-center rounded-full px-6 py-3">
                        ✓ Aap already join kar chuke hain
                    </div>
                @elseif ($contest->current_participants >= $contest->max_participants)
                    <div style="background-color:#24254A; color:#8A8CB3; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-center rounded-full px-6 py-3">
                        Contest Full
                    </div>
                @else
                    <a href="{{ route('contests.build-team', $contest) }}"
                       style="background-color:#FFB627; color:#14152B; font-family:'Rajdhani',sans-serif; font-weight:700;"
                       class="block text-center rounded-full px-6 py-3 hover:opacity-90 transition">
                        Create Team & Join Contest
                    </a>
                @endif
            </div>
        </div>
    </div>
    <x-bottom-nav />
</x-app-layout>
