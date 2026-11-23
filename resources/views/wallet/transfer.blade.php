<x-app-layout>
    <div class="bx-wrap" style="max-width:560px;">
        <div class="bx-pagehead">
            <h1 class="bx-h1">Send money</h1>
            <p class="bx-sub">Move money from your wallet to another player. It arrives in their wallet straight away.</p>
        </div>

        @if (session('error'))
            <div class="bx-alert bx-alert-err">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="bx-alert bx-alert-err">{{ $errors->first() }}</div>
        @endif

        <div class="bx-panel" style="margin-top:12px; display:flex; align-items:center; gap:12px;">
            <div class="bx-grow"><b>You can send up to</b><small>Deposit balance is used first, then winnings</small></div>
            <span class="bx-money" style="font-size:28px; color:var(--bx-gold);">&#8377;{{ number_format((float) $wallet->deposit_balance + (float) $wallet->winnings_balance, 2) }}</span>
        </div>

        <form method="POST" action="{{ route('wallet.transfer.store') }}" class="bx-panel" style="margin-top:12px;"
              x-data="{ to: @js(old('to', '')), who: null, checking: false, timer: null,
                        look() { clearTimeout(this.timer); this.who = null; if (this.to.trim().length < 1) return;
                                 this.timer = setTimeout(() => { this.checking = true;
                                   fetch(@js(route('wallet.transfer.lookup')) + '?to=' + encodeURIComponent(this.to.trim()), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                                     .then(r => r.json()).then(d => { this.who = d; }).catch(() => {}).finally(() => { this.checking = false; }); }, 400); } }"
              x-init="if (to) look()"
              onsubmit="return confirm('Send ₹' + this.amount.value + ' now? This cannot be undone.');">
            @csrf
            <label class="bx-field">
                <span>Send to (user ID, email or phone)</span>
                <input class="bx-input" type="text" name="to" x-model="to" @input="look()" required autocomplete="off" placeholder="e.g. 27 or friend@email.com">
            </label>
            <p x-show="checking" style="display:none; margin:-6px 0 12px; color:var(--bx-muted); font-size:13px;">Checking…</p>
            <p x-show="who && who.found && !who.blocked" style="display:none; margin:-6px 0 12px; color:var(--bx-win); font-size:14px; font-weight:700;">Sending to <span x-text="who && who.name"></span> (#<span x-text="who && who.id"></span>)</p>
            <p x-show="who && who.found && who.blocked" style="display:none; margin:-6px 0 12px; color:#ff8a9a; font-size:14px; font-weight:700;">This account is blocked and can't receive money.</p>
            <p x-show="who && !who.found" style="display:none; margin:-6px 0 12px; color:#ff8a9a; font-size:14px; font-weight:700;">No player found. Check the ID, email or phone.</p>

            <label class="bx-field">
                <span>Amount (&#8377;)</span>
                <input class="bx-input bx-money" style="font-size:26px; padding:10px 14px;" type="number" name="amount" min="{{ \App\Services\WalletTransfer::MIN }}" step="1" required value="{{ old('amount') }}" placeholder="Minimum {{ \App\Services\WalletTransfer::MIN }}" inputmode="numeric">
            </label>
            <label class="bx-field">
                <span>Note (optional)</span>
                <input class="bx-input" type="text" name="note" maxlength="120" value="{{ old('note') }}" placeholder="What is it for?">
            </label>
            <button type="submit" class="bx-btn bx-btn-gold bx-btn-block">Send money</button>
            <p class="bx-sub" style="margin-top:12px; text-align:center;">The player receives it as deposit balance. Transfers can't be reversed by you — contact support if you sent to the wrong person.</p>
        </form>
    </div>
</x-app-layout>
