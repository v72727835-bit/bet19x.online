@extends('agent.layout')
@section('title', 'Transfer')
@section('content')
    <div class="bx-pagehead">
        <h1 class="bx-h1">Transfer between players</h1>
        <p class="bx-sub">Move money from one of your players to another. Both players must be in your network.</p>
    </div>

    <datalist id="ag-players">
        @foreach ($players as $p)
            <option value="{{ $p->id }}">{{ $p->name }} — {{ $p->email }} — ₹{{ number_format((float) ($p->wallet->deposit_balance ?? 0) + (float) ($p->wallet->winnings_balance ?? 0), 2) }}</option>
        @endforeach
    </datalist>

    <form method="POST" action="{{ route('agent.transfer.store') }}" class="bx-panel" style="margin-top:12px; max-width:620px;"
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

    @if ($agent->role === 'master_agent')
        <section class="bx-section">
            <div class="bx-section-head"><h2 class="bx-h2">Send to another master</h2></div>
            <form method="POST" action="{{ route('agent.transfer.master') }}" class="bx-panel" style="max-width:620px;"
                  onsubmit="return confirm('Send ₹' + this.amount.value + ' to this master now?');">
                @csrf
                <p class="bx-sub" style="margin:0 0 12px;">You have ₹{{ number_format(max(0, $agent->creditAvailable()), 2) }} unused credit and ₹{{ number_format($agent->wallet_balance, 2) }} commission balance.</p>
                @if ($masters->isEmpty())
                    <p class="bx-empty" style="padding:8px 0;">There are no other active master agents yet.</p>
                @else
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                        <label class="bx-field"><span>To master</span>
                            <select class="bx-input" name="to_id" required>
                                <option value="">Choose master</option>
                                @foreach ($masters as $m)
                                    <option value="{{ $m->id }}" @selected((string) old('to_id') === (string) $m->id)>{{ $m->name }} ({{ $m->referralCode() }})</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="bx-field"><span>What to send</span>
                            <select class="bx-input" name="kind">
                                @foreach (\App\Services\AgentTransfer::KINDS as $k => $label)
                                    <option value="{{ $k }}" @selected(old('kind', 'credit') === $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="bx-field"><span>Amount (₹)</span><input class="bx-input" type="number" step="1" min="{{ \App\Services\AgentTransfer::MIN }}" name="amount" value="{{ old('amount') }}" required></label>
                        <label class="bx-field"><span>Note (optional)</span><input class="bx-input" name="note" maxlength="120" value="{{ old('note') }}"></label>
                    </div>
                    <button type="submit" class="bx-btn bx-btn-gold">Send to master</button>
                @endif
            </form>
        </section>
    @endif

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
