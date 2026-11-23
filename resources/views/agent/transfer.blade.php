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
