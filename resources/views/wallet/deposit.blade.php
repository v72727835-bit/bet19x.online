<x-app-layout>
    <div class="bx-wrap" style="max-width:560px;">
        <div class="bx-pagehead">
            <h1 class="bx-h1">Deposit</h1>
            <p class="bx-sub">Send the payment, then submit this form. Money is added to your wallet once our team confirms it.</p>
        </div>

        @if (session('error'))
            <div class="bx-alert bx-alert-err">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="bx-alert bx-alert-err">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('wallet.deposit.store') }}" method="POST" class="bx-panel" style="margin-top:12px;" x-data="{ amt: '{{ old('amount') }}' }">
            @csrf
            <label class="bx-field">
                <span>Amount (&#8377;)</span>
                <input class="bx-input bx-money" style="font-size:26px; padding:10px 14px;" type="number" name="amount" min="100" max="1000000" step="1" required x-model="amt" placeholder="Minimum 100" inputmode="numeric">
            </label>
            <div class="bx-amounts">
                @foreach ([500, 1000, 2000, 5000] as $a)
                    <button type="button" @click="amt = {{ $a }}">&#8377;{{ number_format($a) }}</button>
                @endforeach
            </div>

            <div class="bx-field"><span>Paid with</span></div>
            <div class="bx-methods">
                @foreach (['upi' => 'UPI', 'netbanking' => 'Net banking', 'card' => 'Card', 'wallet' => 'Paytm / PhonePe'] as $val => $name)
                    <label>
                        <input type="radio" name="payment_method" value="{{ $val }}" required @checked(old('payment_method', 'upi') === $val)>
                        <span>{{ $name }}</span>
                    </label>
                @endforeach
            </div>

            <label class="bx-field">
                <span>UTR / transaction ID</span>
                <input class="bx-input" type="text" name="reference_note" maxlength="100" value="{{ old('reference_note') }}" placeholder="12-digit UTR from your payment app">
            </label>

            <button type="submit" class="bx-btn bx-btn-gold bx-btn-block">Submit deposit request</button>
            <p class="bx-sub" style="margin-top:12px; text-align:center;">Minimum &#8377;100. You can track the request on your wallet page.</p>
        </form>
    </div>
</x-app-layout>
