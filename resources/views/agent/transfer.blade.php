@extends('agent.layout')
@section('title', 'Transfer')
@section('content')
    <div class="bx-pagehead">
        <h1 class="bx-h1">Transfer</h1>
        <p class="bx-sub">Send credit or commission balance to another agent with their agent code, or move money between two of your players.</p>
    </div>

    <datalist id="ag-players">
        @foreach ($players as $p)
            <option value="{{ $p->id }}">{{ $p->name }} — {{ $p->email }} — ₹{{ number_format((float) ($p->wallet->deposit_balance ?? 0) + (float) ($p->wallet->winnings_balance ?? 0), 2) }}</option>
        @endforeach
    </datalist>

    <div class="bx-section-head" style="margin-top:18px;"><h2 class="bx-h2">Between your players</h2></div>
    <form method="POST" action="{{ route('agent.transfer.store') }}" class="bx-panel" style="margin-top:0; max-width:620px;"
          onsubmit="return confirm('Transfer ₹' + this.amount.value + ' now?');">
        @csrf
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:10px;">
            <label class="bx-field"><span>From player (ID)</span><input class="bx-input" name="from" list="ag-players" value="{{ old('from') }}" required autocomplete="off" placeholder="Pick or type user ID"></label>
            <label class="bx-field"><span>To player (ID)</span><input class="bx-input" name="to" list="ag-players" value="{{ old('to') }}" required autocomplete="off" placeholder="Pick or type user ID"></label>
            <label class="bx-field"><span>Amount (₹)</span><input class="bx-input" type="number" step="1" min="{{ \App\Services\WalletTransfer::MIN }}" name="amount" value="{{ old('amount') }}" required></label>
            <label class="bx-field"><span>Note (optional)</span><input class="bx-input" name="note" maxlength="120" value="{{ old('note') }}"></label>
        </div>
        <button type="submit" class="bx-btn bx-btn-gold">Transfer</button>
        <p class="bx-sub" style="margin-top:8px;">Taken from the sender's deposit balance first, then winnings; the receiver gets it as deposit balance.</p>
    </form>

    {{-- Agent to agent: master, distributor and reseller can all send to any agent by its unique code --}}
    <section class="bx-section">
        <div class="bx-section-head"><h2 class="bx-h2">Send to an agent</h2></div>
        <div class="bx-panel" style="max-width:620px;" x-data="{ copied: false }">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <div class="bx-grow">
                    <small style="color:var(--bx-muted); font-size:13px;">Your agent code — share it so others can send to you</small>
                    <b style="font-size:24px; letter-spacing:2px; color:var(--bx-gold);">{{ $agent->referralCode() }}</b>
                </div>
                <button type="button" class="bx-btn bx-btn-ghost bx-btn-sm"
                        @click="navigator.clipboard.writeText(@js($agent->referralCode())); copied = true; setTimeout(() => copied = false, 1500)" x-text="copied ? 'Copied' : 'Copy code'">Copy code</button>
            </div>
            <p class="bx-sub" style="margin:10px 0 0;">You have ₹{{ number_format(max(0, $agent->creditAvailable()), 2) }} unused credit and ₹{{ number_format($agent->wallet_balance, 2) }} commission balance.</p>
        </div>

        <form method="POST" action="{{ route('agent.transfer.agent') }}" class="bx-panel" style="max-width:620px; margin-top:12px;"
              x-data="{ code: @js(old('code', '')), who: null, checking: false, timer: null,
                        look() { clearTimeout(this.timer); this.who = null; this.code = this.code.toUpperCase().trim(); if (this.code.length < 3) return;
                                 this.timer = setTimeout(() => { this.checking = true;
                                   fetch(@js(route('agent.transfer.agent.lookup')) + '?code=' + encodeURIComponent(this.code), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                                     .then(r => r.json()).then(d => { this.who = d; }).catch(() => {}).finally(() => { this.checking = false; }); }, 350); } }"
              x-init="if (code) look()"
              onsubmit="return confirm('Send ₹' + this.amount.value + ' now? This cannot be undone.');">
            @csrf
            <label class="bx-field">
                <span>Receiver's agent code</span>
                <input class="bx-input" style="text-transform:uppercase; letter-spacing:2px; font-weight:800;" name="code" x-model="code" @input="look()" required autocomplete="off" maxlength="20" placeholder="e.g. BXS6">
            </label>
            <p x-show="checking" style="display:none; margin:-6px 0 12px; color:var(--bx-muted); font-size:13px;">Checking…</p>
            <p x-show="who && who.found" style="display:none; margin:-6px 0 12px; color:var(--bx-win); font-size:14px; font-weight:700;">Sending to <span x-text="who && who.name"></span> — <span x-text="who && who.type"></span> (<span x-text="who && who.code"></span>)</p>
            <p x-show="who && !who.found && code.length >= 3" style="display:none; margin:-6px 0 12px; color:#ff8a9a; font-size:14px; font-weight:700;">No active agent with this code.</p>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                <label class="bx-field"><span>What to send</span>
                    <select class="bx-input" name="kind">
                        @foreach (\App\Services\AgentTransfer::KINDS as $k => $label)
                            <option value="{{ $k }}" @selected(old('kind', 'credit') === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="bx-field"><span>Amount (₹)</span><input class="bx-input" type="number" step="1" min="{{ \App\Services\AgentTransfer::MIN }}" name="amount" value="{{ old('amount') }}" required></label>
            </div>
            <label class="bx-field"><span>Note (optional)</span><input class="bx-input" name="note" maxlength="120" value="{{ old('note') }}"></label>
            <button type="submit" class="bx-btn bx-btn-gold" :disabled="!(who && who.found)">Send</button>
            <p class="bx-sub" style="margin-top:8px;">Credit: only your unused credit can move, and your credit limit goes down by the same amount.</p>
        </form>
    </section>

    @if ($agentHistory->isNotEmpty())
        <section class="bx-section">
            <div class="bx-section-head"><h2 class="bx-h2">Your agent transfers</h2></div>
            <div class="bx-panel ag-scroll" style="padding:4px;">
                <table class="ag-table">
                    <thead><tr><th>Time</th><th>From</th><th>To</th><th>What</th><th>Amount</th><th>By</th></tr></thead>
                    <tbody>
                        @foreach ($agentHistory as $h)
                            <tr>
                                <td class="ag-mut">{{ $h->at?->format('d M, h:i A') }}</td>
                                <td>{{ (int) $h->from === $agent->id ? 'You' : ($h->from_name ?? '') }}</td>
                                <td>{{ (int) $h->to === $agent->id ? 'You' : ($h->to_name ?? '') }}</td>
                                <td>{{ \App\Services\AgentTransfer::KINDS[$h->kind ?? ''] ?? '' }}</td>
                                <td class="{{ (int) $h->to === $agent->id ? 'ag-pos' : 'ag-neg' }}">{{ (int) $h->to === $agent->id ? '+' : '−' }}₹{{ number_format((float) $h->amount, 2) }}</td>
                                <td class="ag-mut">{{ $h->by ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="bx-section">
        <div class="bx-section-head"><h2 class="bx-h2">Recent transfers by your players</h2></div>
        <div class="bx-panel ag-scroll" style="padding:4px;">
            @if ($recent->isEmpty())
                <p class="bx-empty">No transfers yet.</p>
            @else
                <table class="ag-table">
                    <thead><tr><th>Time</th><th>From</th><th>Details</th><th>Amount</th></tr></thead>
                    <tbody>
                        @foreach ($recent as $t)
                            <tr>
                                <td class="ag-mut">{{ $t->created_at->format('d M, h:i A') }}</td>
                                <td><b>{{ $t->wallet->user->name ?? '—' }}</b></td>
                                <td style="white-space:normal;">{{ $t->description }}</td>
                                <td><b>₹{{ number_format($t->amount, 2) }}</b></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>
@endsection
