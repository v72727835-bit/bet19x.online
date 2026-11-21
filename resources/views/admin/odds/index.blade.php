<x-admin-layout title="Odds Management">

    <div class="fx-card p-5 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="fx-muted text-xs block mb-1">Match</label>
                <select name="match_event_id" onchange="this.form.submit()" class="fx-input">
                    @foreach($matches as $m)
                        <option value="{{ $m->id }}" {{ (string)$matchEventId === (string)$m->id ? 'selected' : '' }}>
                            {{ $m->team_a_name }} vs {{ $m->team_b_name }} — {{ $m->start_time?->format('d M, H:i') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="fx-muted text-xs block mb-1">Market Type</label>
                <select name="market_type" onchange="this.form.submit()" class="fx-input">
                    <option value="">Sab</option>
                    @foreach(['match_winner' => 'Match Winner', 'toss' => 'Toss', 'fancy' => 'Fancy', 'session' => 'Session', 'custom' => 'Custom'] as $val => $label)
                        <option value="{{ $val }}" {{ $marketType === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($matchEventId)
    <div class="fx-card p-5 mb-6">
        <p class="fx-heading text-sm mb-3">Naya Market / Odds Add Karo</p>
        <form method="POST" action="{{ route('admin.odds.store') }}" class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @csrf
            <input type="hidden" name="match_event_id" value="{{ $matchEventId }}">
            <select name="market_type" class="fx-input" required>
                <option value="match_winner">Match Winner</option>
                <option value="toss">Toss</option>
                <option value="fancy">Fancy</option>
                <option value="session">Session</option>
                <option value="custom">Custom</option>
            </select>
            <input type="text" name="name" placeholder="Market Name (e.g. Match Winner)" class="fx-input" required>
            <input type="text" name="selection_a" placeholder="Selection A (optional)" class="fx-input">
            <input type="text" name="selection_b" placeholder="Selection B (optional)" class="fx-input">
            <input type="number" step="0.01" name="odds_back" placeholder="Back Odds" class="fx-input">
            <input type="number" step="0.01" name="odds_lay" placeholder="Lay Odds" class="fx-input">
            <input type="number" name="session_target" placeholder="Session Target (runs)" class="fx-input">
            <input type="number" step="0.01" name="session_yes_rate" placeholder="Yes Rate" class="fx-input">
            <input type="number" step="0.01" name="session_no_rate" placeholder="No Rate" class="fx-input">
            <button class="fx-btn text-sm">+ Add Market</button>
        </form>
    </div>

    <div class="fx-card p-5 overflow-x-auto">
        <div class="flex items-center justify-between mb-3">
            <p class="fx-heading text-sm">Markets &amp; Odds — Bulk Update</p>
            <span class="fx-muted text-xs">Manually overridden markets are marked ⚡</span>
        </div>
        <form method="POST" action="{{ route('admin.odds.bulk-update') }}">
            @csrf
            <table class="w-full fx-table">
                <thead>
                    <tr><th>Market</th><th>Type</th><th>Back</th><th>Lay</th><th>Session Yes</th><th>Session No</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($markets as $m)
                        <tr>
                            <td>{{ $m->name }} {{ $m->is_manual_override ? '⚡' : '' }}<br>
                                <span class="fx-muted text-xs">{{ $m->selection_a }} {{ $m->selection_b ? 'vs '.$m->selection_b : '' }}</span>
                            </td>
                            <td><span class="fx-badge" style="background-color: var(--fx-card-hover); color: var(--fx-amber);">{{ str_replace('_',' ', $m->market_type) }}</span></td>
                            <td><input type="number" step="0.01" name="markets[{{ $m->id }}][odds_back]" value="{{ $m->odds_back }}" class="fx-input text-xs w-20"></td>
                            <td><input type="number" step="0.01" name="markets[{{ $m->id }}][odds_lay]" value="{{ $m->odds_lay }}" class="fx-input text-xs w-20"></td>
                            <td><input type="number" step="0.01" name="markets[{{ $m->id }}][session_yes_rate]" value="{{ $m->session_yes_rate }}" class="fx-input text-xs w-20"></td>
                            <td><input type="number" step="0.01" name="markets[{{ $m->id }}][session_no_rate]" value="{{ $m->session_no_rate }}" class="fx-input text-xs w-20"></td>
                            <td>
                                <span class="fx-badge" style="background-color: {{ $m->status === 'active' ? 'rgba(52,211,153,.15)' : 'rgba(248,113,113,.15)' }}; color: {{ $m->status === 'active' ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ $m->status }}</span>
                            </td>
                            <td class="flex gap-1">
                                <button type="submit" form="suspend-{{ $m->id }}" class="fx-btn-outline text-xs">{{ $m->status === 'suspended' ? 'Resume' : 'Suspend' }}</button>
                                <button type="submit" form="delete-{{ $m->id }}" class="fx-btn-danger text-xs" onclick="return confirm('Delete this market?')">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center fx-muted py-6">Is match ke liye koi market nahi bana. Upar se add karo.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($markets->isNotEmpty())
                <button class="fx-btn text-sm mt-4">Bulk Save All Odds</button>
            @endif
        </form>

        @foreach($markets as $m)
            <form id="suspend-{{ $m->id }}" method="POST" action="{{ route('admin.odds.toggle-suspend', $m) }}">@csrf</form>
            <form id="delete-{{ $m->id }}" method="POST" action="{{ route('admin.odds.destroy', $m) }}">@csrf @method('DELETE')</form>
        @endforeach
    </div>
    @else
        <div class="fx-card p-8 text-center fx-muted">Koi upcoming/live match nahi hai. Pehle Match Management se match add karo.</div>
    @endif

</x-admin-layout>
