<x-app-layout>
    <div class="bx-wrap" style="max-width:560px;">
        <div class="bx-pagehead">
            <h1 class="bx-h1">Deposit</h1>
            <p class="bx-sub">Pay using the details below, then enter the UTR number. Money is added to your wallet once our team confirms the payment.</p>
        </div>

        @if (session('error'))
            <div class="bx-alert bx-alert-err">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="bx-alert bx-alert-err">{{ $errors->first() }}</div>
        @endif

        @if ($methods->isEmpty())
            <div class="bx-panel" style="margin-top:12px; text-align:center;">
                <b style="font-size:17px;">Deposits are not available right now</b>
                <p class="bx-sub" style="margin:6px 0 14px;">Payment details have not been set up yet. Please try again later or ask live support.</p>
                <button type="button" class="bx-btn bx-btn-gold" onclick="window.dispatchEvent(new CustomEvent('open-support'))">Chat with support</button>
            </div>
        @else
            @php
                $firstId = (int) old('payment_method_id', $methods->first()->id);
            @endphp
            <form action="{{ route('wallet.deposit.store') }}" method="POST" enctype="multipart/form-data" class="bx-panel" style="margin-top:12px;"
                  x-data="{ id: {{ $firstId }}, amt: @js(old('amount', '')), copied: '',
                            copy(text, key) { navigator.clipboard.writeText(text).then(() => { this.copied = key; setTimeout(() => this.copied = '', 1400); }).catch(() => {}); } }">
                @csrf

                {{-- 1. choose how to pay --}}
                <div class="bx-field"><span>1. Choose how you will pay</span></div>
                <div class="bx-methods" style="grid-template-columns:repeat(auto-fit, minmax(150px, 1fr));">
                    @foreach ($methods as $m)
                        <label>
                            <input type="radio" name="payment_method_id" value="{{ $m->id }}" x-model.number="id" required>
                            <span>{{ $m->title }}<small style="display:block; font-size:11px; font-weight:600; color:var(--bx-muted); margin-top:2px;">{{ $m->summary() }}</small></span>
                        </label>
                    @endforeach
                </div>

                {{-- 2. details for the chosen method --}}
                <div class="bx-field"><span>2. Send the money here</span></div>
                @foreach ($methods as $m)
                    <div x-show="id === {{ $m->id }}" @if (! $loop->first) style="display:none" @endif class="bx-paybox">
                        @if ($m->qrUrl())
                            <div style="text-align:center;">
                                <img src="{{ $m->qrUrl() }}" alt="Scan to pay {{ $m->title }}" class="bx-qr">
                                <small style="display:block; color:var(--bx-muted); margin-top:6px;">Scan with any UPI app</small>
                            </div>
                        @endif

                        @if ($m->upi_id)
                            <div class="bx-payrow">
                                <div class="bx-grow"><small>UPI ID</small><b>{{ $m->upi_id }}</b></div>
                                <button type="button" class="bx-btn bx-btn-ghost bx-btn-sm" @click="copy(@js($m->upi_id), 'upi{{ $m->id }}')" x-text="copied === 'upi{{ $m->id }}' ? 'Copied' : 'Copy'">Copy</button>
                            </div>
                            <a class="bx-btn bx-btn-green bx-btn-block" style="margin:10px 0;" rel="noopener"
                               :href="@js($m->upiLink()) + (amt > 0 ? '&am=' + Number(amt).toFixed(2) : '')">Pay with UPI app</a>
                        @endif

                        @if ($m->hasBank())
                            @foreach ([['Bank', $m->bank_name, 'bank'], ['Account holder', $m->account_holder, 'holder'], ['Account number', $m->account_number, 'acc'], ['IFSC', $m->ifsc, 'ifsc']] as [$label, $value, $key])
                                @if ($value)
                                    <div class="bx-payrow">
                                        <div class="bx-grow"><small>{{ $label }}</small><b>{{ $value }}</b></div>
                                        @if (in_array($key, ['acc', 'ifsc', 'upi']))
                                            <button type="button" class="bx-btn bx-btn-ghost bx-btn-sm" @click="copy(@js($value), '{{ $key . $m->id }}')" x-text="copied === '{{ $key . $m->id }}' ? 'Copied' : 'Copy'">Copy</button>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        @if ($m->instructions)
                            <p class="bx-sub" style="margin:10px 0 0; white-space:pre-line;">{{ $m->instructions }}</p>
                        @endif
                        <p class="bx-sub" style="margin:8px 0 0;">
                            Minimum ₹{{ number_format($m->min_amount, 0) }}@if ($m->max_amount), maximum ₹{{ number_format($m->max_amount, 0) }}@endif.
                        </p>
                    </div>
                @endforeach

                {{-- 3. tell us what you paid --}}
                <div class="bx-field" style="margin-top:16px;"><span>3. After paying, tell us what you sent</span></div>
                <label class="bx-field">
                    <span>Amount you paid (&#8377;)</span>
                    <input class="bx-input bx-money" style="font-size:26px; padding:10px 14px;" type="number" name="amount" step="1" min="1" required x-model="amt" placeholder="e.g. 1000" inputmode="numeric">
                </label>
                <div class="bx-amounts">
                    @foreach ([500, 1000, 2000, 5000] as $a)
                        <button type="button" @click="amt = {{ $a }}">&#8377;{{ number_format($a) }}</button>
                    @endforeach
                </div>

                <label class="bx-field">
                    <span>UTR / transaction ID *</span>
                    <input class="bx-input" type="text" name="reference_note" required minlength="6" maxlength="40" value="{{ old('reference_note') }}" placeholder="12-digit UTR from your payment app" autocomplete="off">
                </label>
                <label class="bx-field">
                    <span>Payment screenshot (optional, helps us confirm faster)</span>
                    <input class="bx-input" type="file" name="screenshot" accept="image/png,image/jpeg,image/webp">
                </label>

                <button type="submit" class="bx-btn bx-btn-gold bx-btn-block">Submit deposit request</button>
                <p class="bx-sub" style="margin-top:12px; text-align:center;">Your wallet is credited only after our team approves the request. Track it on your wallet page.</p>
            </form>
        @endif
    </div>
</x-app-layout>
