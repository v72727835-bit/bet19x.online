<x-app-layout>
    @php($wdWallet = \App\Models\Wallet::where('user_id', auth()->id())->first())
    <div class="bx-wrap" style="max-width:560px;">
        <div class="bx-pagehead">
            <h1 class="bx-h1">Withdraw</h1>
            <p class="bx-sub">Only winnings can be withdrawn. The amount is held as soon as you submit and is paid after our team approves it.</p>
        </div>

        @if (session('error'))
            <div class="bx-alert bx-alert-err">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="bx-alert bx-alert-err">{{ $errors->first() }}</div>
        @endif

        <div class="bx-panel" style="margin-top:12px; display:flex; align-items:center; gap:12px;">
            <div class="bx-grow"><b>Winnings available</b><small>Deposits and bonus stay in your wallet for play</small></div>
            <span class="bx-money bx-plus" style="font-size:28px;">&#8377;{{ number_format((float) ($wdWallet->winnings_balance ?? 0), 2) }}</span>
        </div>

        <form action="{{ route('wallet.withdraw.store') }}" method="POST" class="bx-panel" style="margin-top:12px;">
            @csrf
            <label class="bx-field">
                <span>Amount (&#8377;)</span>
                <input class="bx-input bx-money" style="font-size:26px; padding:10px 14px;" type="number" name="amount" min="100" step="1" required value="{{ old('amount') }}" placeholder="Minimum 100" inputmode="numeric">
            </label>
            <label class="bx-field">
                <span>Bank account</span>
                <textarea class="bx-input" name="bank_details" rows="4" required minlength="10" maxlength="500" placeholder="Account holder name, account number, IFSC and bank name — or your UPI ID">{{ old('bank_details') }}</textarea>
            </label>
            <button type="submit" class="bx-btn bx-btn-red bx-btn-block">Request withdrawal</button>
            <p class="bx-sub" style="margin-top:12px; text-align:center;">If a request is rejected, the full amount returns to your winnings.</p>
        </form>
    </div>
</x-app-layout>
