<x-app-layout>
    <div style="background-color:#14152B; min-height:100vh;">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-10">

            <h1 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-3xl sm:text-4xl mb-8">
                Contests
            </h1>

            @if (session('status'))
                <div style="background:#1B2A1E; border:1px solid #2E5C33; color:#8CD98F;" class="rounded-lg px-4 py-3 text-sm mb-6">{{ session('status') }}</div>
            @endif

            @forelse ($contests as $contest)
                <a href="{{ route('contests.show', $contest) }}" class="block rounded-2xl p-6 mb-4" style="background:linear-gradient(145deg, #1D1E3D 0%, #181933 100%); border:1px solid #24254A; text-decoration:none;">
                    <div class="flex items-center justify-between mb-3">
                        <p style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-lg">
                            {{ $contest->name }}
                        </p>
                        <span style="background-color:#FFB627; color:#14152B; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-xs px-3 py-1 rounded-full">
                            ₹{{ number_format($contest->total_prize_pool) }} Prize Pool
                        </span>
                    </div>
                    <p style="color:#8A8CB3;" class="text-sm mb-4">
                        {{ $contest->matchEvent->team_a_name ?? '' }} vs {{ $contest->matchEvent->team_b_name ?? '' }}
                        · {{ $contest->matchEvent->sport->name ?? '' }}
                    </p>
                    <div class="flex items-center justify-between text-sm">
                        <span style="color:#8A8CB3;">
                            {{ $contest->current_participants }}/{{ $contest->max_participants }} joined
                        </span>
                        <span style="color:{{ $contest->entry_fee > 0 ? '#FF6B4A' : '#4ADE80' }}; font-family:'Rajdhani',sans-serif; font-weight:700;">
                            {{ $contest->entry_fee > 0 ? '₹' . number_format($contest->entry_fee) . ' Entry' : 'FREE Entry' }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl py-16 text-center" style="background:#1D1E3D; border:1px solid #24254A;">
                    <p style="color:#8A8CB3;">Abhi koi contest available nahi hai.</p>
                </div>
            @endforelse
        </div>
    </div>
    <x-bottom-nav />
</x-app-layout>
