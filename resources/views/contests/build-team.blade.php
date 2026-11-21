<x-app-layout>
    <div style="background-color:#14152B; min-height:100vh;">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-10 pb-32">

            <a href="{{ route('contests.show', $contest) }}" style="color:#8A8CB3;" class="text-sm hover:text-[#FFB627] transition">&larr; Back to Contest</a>

            <h1 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-2xl sm:text-3xl mt-4 mb-2">
                Build Your Team
            </h1>
            <p style="color:#8A8CB3;" class="text-sm mb-6">
                Select exactly {{ $teamSize }} players within {{ $creditBudget }} credits.
            </p>

            @if (session('error'))
                <div style="background:#2A1B1B; border:1px solid #5C2E2E; color:#D98C8C;" class="rounded-lg px-4 py-3 text-sm mb-6">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div style="background:#2A1B1B; border:1px solid #5C2E2E; color:#D98C8C;" class="rounded-lg px-4 py-3 text-sm mb-6">{{ $errors->first() }}</div>
            @endif

            <div id="statusBar" class="sticky top-0 z-10 rounded-2xl p-4 mb-6 flex items-center justify-between" style="background:#1D1E3D; border:1px solid #24254A;">
                <div>
                    <p style="color:#8A8CB3;" class="text-xs">Players Selected</p>
                    <p id="playerCount" style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-lg">0 / {{ $teamSize }}</p>
                </div>
                <div class="text-right">
                    <p style="color:#8A8CB3;" class="text-xs">Credits Left</p>
                    <p id="creditsLeft" style="color:#FFB627; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-lg">{{ $creditBudget }}.0</p>
                </div>
            </div>

            <form method="POST" action="{{ route('contests.join', $contest) }}" id="teamForm">
                @csrf

                <div class="mb-6">
                    <label style="color:#8A8CB3; font-family:'Rajdhani',sans-serif; font-weight:600;" class="block text-sm mb-2">Team Name</label>
                    <input type="text" name="team_name" required maxlength="100"
                           style="background-color:#1D1E3D; border:1px solid #24254A; color:#F4F3FA;"
                           class="w-full px-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FFB627]"
                           placeholder="e.g. My Dream XI">
                </div>

                @php
                    $grouped = $players->groupBy('team_id');
                @endphp

                @foreach ($grouped as $teamId => $teamPlayers)
                    <h3 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-lg mb-3 mt-6">
                        {{ $teamPlayers->first()->team->name ?? 'Team' }}
                    </h3>
                    <div class="space-y-2 mb-4">
                        @foreach ($teamPlayers as $player)
                            <label class="flex items-center justify-between rounded-xl px-4 py-3 cursor-pointer player-row" style="background:#1D1E3D; border:1px solid #24254A;" data-credit="{{ $player->credit_value }}">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="players[]" value="{{ $player->id }}" class="player-checkbox" data-credit="{{ $player->credit_value }}" style="accent-color:#FFB627; width:18px; height:18px;">
                                    <div>
                                        <p style="color:#F4F3FA;" class="text-sm font-medium">{{ $player->name }}</p>
                                        <p style="color:#6B6D91;" class="text-xs uppercase">{{ str_replace('_', ' ', $player->role) }}</p>
                                    </div>
                                </div>
                                <span style="color:#FFB627; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-sm">
                                    {{ $player->credit_value }} Cr
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endforeach

                <div class="mt-8 rounded-2xl p-5" style="background:#1D1E3D; border:1px solid #24254A;">
                    <h3 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-base mb-1">Captain & Vice-Captain</h3>
                    <p style="color:#8A8CB3;" class="text-xs mb-4">Captain gets 2x points, Vice-Captain gets 1.5x points. Select {{ $teamSize }} players first.</p>

                    <div class="mb-4">
                        <label style="color:#8A8CB3;" class="block text-xs mb-2">Captain</label>
                        <select name="captain_id" id="captainSelect" required style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-xl px-3 py-2 text-sm">
                            <option value="">Select captain from your team</option>
                        </select>
                    </div>
                    <div>
                        <label style="color:#8A8CB3;" class="block text-xs mb-2">Vice-Captain</label>
                        <select name="vice_captain_id" id="viceCaptainSelect" required style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-xl px-3 py-2 text-sm">
                            <option value="">Select vice-captain from your team</option>
                        </select>
                    </div>
                </div>

                <button type="submit" id="submitBtn" disabled
                        style="background-color:#24254A; color:#6B6D91; font-family:'Rajdhani',sans-serif; font-weight:700;"
                        class="w-full mt-8 rounded-full px-6 py-4 text-base transition">
                    Select {{ $teamSize }} players to continue
                </button>
            </form>
        </div>
    </div>
    <x-bottom-nav />

    <script>
        const TEAM_SIZE = {{ $teamSize }};
        const CREDIT_BUDGET = {{ $creditBudget }};

        const checkboxes = document.querySelectorAll('.player-checkbox');
        const playerCountEl = document.getElementById('playerCount');
        const creditsLeftEl = document.getElementById('creditsLeft');
        const submitBtn = document.getElementById('submitBtn');
        const captainSelect = document.getElementById('captainSelect');
        const viceCaptainSelect = document.getElementById('viceCaptainSelect');

        function updateState() {
            const checked = Array.from(checkboxes).filter(cb => cb.checked);
            const count = checked.length;
            const creditsUsed = checked.reduce((sum, cb) => sum + parseFloat(cb.dataset.credit), 0);
            const creditsLeft = (CREDIT_BUDGET - creditsUsed).toFixed(1);

            playerCountEl.textContent = count + ' / ' + TEAM_SIZE;
            creditsLeftEl.textContent = creditsLeft;
            creditsLeftEl.style.color = creditsLeft < 0 ? '#FF6B4A' : '#FFB627';

            checkboxes.forEach(cb => {
                if (!cb.checked) {
                    cb.disabled = (count >= TEAM_SIZE) || (creditsLeft - parseFloat(cb.dataset.credit) < 0);
                }
            });

            const prevCaptain = captainSelect.value;
            const prevVice = viceCaptainSelect.value;
            captainSelect.innerHTML = '<option value="">Select captain from your team</option>';
            viceCaptainSelect.innerHTML = '<option value="">Select vice-captain from your team</option>';
            checked.forEach(cb => {
                const label = cb.closest('.player-row').querySelector('p.text-sm').textContent;
                const opt1 = document.createElement('option');
                opt1.value = cb.value;
                opt1.textContent = label;
                if (cb.value === prevCaptain) opt1.selected = true;
                captainSelect.appendChild(opt1);

                const opt2 = document.createElement('option');
                opt2.value = cb.value;
                opt2.textContent = label;
                if (cb.value === prevVice) opt2.selected = true;
                viceCaptainSelect.appendChild(opt2);
            });

            const valid = count === TEAM_SIZE && creditsLeft >= 0;
            submitBtn.disabled = !valid;
            submitBtn.style.backgroundColor = valid ? '#FFB627' : '#24254A';
            submitBtn.style.color = valid ? '#14152B' : '#6B6D91';
            submitBtn.textContent = valid ? 'Confirm Team & Join Contest' : 'Select ' + TEAM_SIZE + ' players to continue';
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateState));
        updateState();
    </script>
</x-app-layout>
