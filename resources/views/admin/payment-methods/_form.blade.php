{{-- $method (existing PaymentMethod or null), $action, $submit --}}
@php
    $m = $method ?? null;
    $v = fn ($f, $default = '') => old($f, $m->$f ?? $default);
@endphp
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @csrf
    @if($m) @method('PUT') @endif

    <div class="sm:col-span-2">
        <label class="fx-muted text-xs block mb-1">Name shown to players *</label>
        <input type="text" name="title" value="{{ $v('title') }}" required maxlength="80" class="fx-input w-full" placeholder="e.g. PhonePe / Google Pay / HDFC Bank">
    </div>

    <div class="sm:col-span-2 fx-muted text-xs" style="border-top:1px solid var(--fx-border); padding-top:.75rem;">Fill any of these: UPI ID, QR image, bank account. Players see only what you fill.</div>

    <div>
        <label class="fx-muted text-xs block mb-1">UPI ID</label>
        <input type="text" name="upi_id" value="{{ $v('upi_id') }}" maxlength="100" class="fx-input w-full" placeholder="shop@okaxis" autocomplete="off">
    </div>
    <div>
        <label class="fx-muted text-xs block mb-1">Name shown in the UPI app (optional)</label>
        <input type="text" name="payee_name" value="{{ $v('payee_name') }}" maxlength="100" class="fx-input w-full" placeholder="BET19X">
    </div>

    <div class="sm:col-span-2">
        <label class="fx-muted text-xs block mb-1">QR image (PNG, JPG or WebP, up to 2 MB)</label>
        @if($m && $m->qr_image_path)
            <div class="flex items-center gap-3 mb-2">
                <img src="{{ $m->qrUrl() }}" alt="QR" style="height:96px; width:96px; object-fit:contain; background:#fff; border-radius:8px; padding:4px;">
                <label class="text-xs flex items-center gap-2"><input type="checkbox" name="remove_qr" value="1"> Remove this QR</label>
            </div>
        @endif
        <input type="file" name="qr_image" accept="image/png,image/jpeg,image/webp" class="fx-input w-full text-sm">
        <p class="fx-muted text-xs mt-1">{{ $m && $m->qr_image_path ? 'Choose a file only if you want to replace the QR.' : 'Upload the QR you receive money on.' }}</p>
    </div>

    <div>
        <label class="fx-muted text-xs block mb-1">Bank name</label>
        <input type="text" name="bank_name" value="{{ $v('bank_name') }}" maxlength="100" class="fx-input w-full">
    </div>
    <div>
        <label class="fx-muted text-xs block mb-1">Account holder name</label>
        <input type="text" name="account_holder" value="{{ $v('account_holder') }}" maxlength="100" class="fx-input w-full">
    </div>
    <div>
        <label class="fx-muted text-xs block mb-1">Account number</label>
        <input type="text" name="account_number" value="{{ $v('account_number') }}" maxlength="40" class="fx-input w-full" autocomplete="off">
    </div>
    <div>
        <label class="fx-muted text-xs block mb-1">IFSC code</label>
        <input type="text" name="ifsc" value="{{ $v('ifsc') }}" maxlength="20" class="fx-input w-full" style="text-transform:uppercase;" autocomplete="off">
    </div>

    <div class="sm:col-span-2">
        <label class="fx-muted text-xs block mb-1">Instructions for players (optional)</label>
        <textarea name="instructions" rows="2" maxlength="1000" class="fx-input w-full" placeholder="e.g. Send the exact amount, then enter the UTR number below.">{{ $v('instructions') }}</textarea>
    </div>

    <div>
        <label class="fx-muted text-xs block mb-1">Minimum deposit (₹)</label>
        <input type="number" step="1" min="1" name="min_amount" value="{{ $v('min_amount', 100) }}" required class="fx-input w-full">
    </div>
    <div>
        <label class="fx-muted text-xs block mb-1">Maximum deposit (₹, optional)</label>
        <input type="number" step="1" min="1" name="max_amount" value="{{ $v('max_amount') }}" class="fx-input w-full">
    </div>
    <div>
        <label class="fx-muted text-xs block mb-1">Order (smaller shows first)</label>
        <input type="number" min="0" name="sort_order" value="{{ $v('sort_order', 0) }}" class="fx-input w-full">
    </div>

    <div class="sm:col-span-2 flex gap-2">
        <button class="fx-btn text-sm">{{ $submit }}</button>
        @if($m)<a href="{{ route('admin.payment-methods.index') }}" class="fx-btn-outline text-sm">Cancel</a>@endif
    </div>
</form>
