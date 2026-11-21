<x-admin-layout title="White Label / Branding Settings">

    <div class="fx-card p-5 mb-6">
        <p class="fx-heading text-sm mb-1">Poora Platform White-Label Karo</p>
        <p class="fx-muted text-xs mb-4">Naam, logo, favicon, colors, currency, contact — yeh sab yahan se badlo. Save karte hi poore panel (admin + user side) me turant reflect ho jayega — koi code edit nahi karna.</p>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <p class="fx-heading text-xs uppercase fx-muted mb-2">Basic Identity</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="fx-muted text-xs block mb-1">Site / Brand Name</label>
                        <input type="text" name="site_name" value="{{ old('site_name', $setting->site_name) }}" class="fx-input w-full">
                        @error('site_name') <p class="text-xs mt-1" style="color: var(--fx-red);">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="fx-muted text-xs block mb-1">Tagline</label>
                        <input type="text" name="app_tagline" value="{{ old('app_tagline', $setting->app_tagline) }}" class="fx-input w-full" placeholder="e.g. India ka #1 Fantasy Platform">
                    </div>
                    <div>
                        <label class="fx-muted text-xs block mb-1">Live Ticker Text</label>
                        <input type="text" name="ticker_text" value="{{ old('ticker_text', $setting->ticker_text) }}" class="fx-input w-full">
                    </div>
                    <div>
                        <label class="fx-muted text-xs block mb-1">Currency Symbol</label>
                        <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $setting->currency_symbol ?? '₹') }}" class="fx-input w-full" maxlength="5">
                    </div>
                </div>
            </div>

            <div>
                <p class="fx-heading text-xs uppercase fx-muted mb-2">Logo &amp; Favicon</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="fx-muted text-xs block mb-1">Logo</label>
                        @if ($setting->logo_path)
                            <img src="{{ asset('storage/' . $setting->logo_path) }}" class="h-12 mb-2 rounded">
                        @endif
                        <input type="file" name="logo" class="fx-input w-full text-xs">
                        @error('logo') <p class="text-xs mt-1" style="color: var(--fx-red);">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="fx-muted text-xs block mb-1">Favicon</label>
                        @if ($setting->favicon_path)
                            <img src="{{ asset('storage/' . $setting->favicon_path) }}" class="h-8 mb-2 rounded">
                        @endif
                        <input type="file" name="favicon" class="fx-input w-full text-xs">
                    </div>
                </div>
            </div>

            <div>
                <p class="fx-heading text-xs uppercase fx-muted mb-2">Brand Colors</p>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div>
                        <label class="fx-muted text-xs block mb-1">Primary</label>
                        <input type="color" name="primary_color" value="{{ old('primary_color', $setting->primary_color ?? '#FFB627') }}" class="fx-input w-full h-10">
                    </div>
                    <div>
                        <label class="fx-muted text-xs block mb-1">Secondary</label>
                        <input type="color" name="secondary_color" value="{{ old('secondary_color', $setting->secondary_color ?? '#FF6B4A') }}" class="fx-input w-full h-10">
                    </div>
                    <div>
                        <label class="fx-muted text-xs block mb-1">Accent</label>
                        <input type="color" name="accent_color" value="{{ old('accent_color', $setting->accent_color ?? '#34D399') }}" class="fx-input w-full h-10">
                    </div>
                    <div>
                        <label class="fx-muted text-xs block mb-1">Dark Background</label>
                        <input type="color" name="bg_dark_color" value="{{ old('bg_dark_color', $setting->bg_dark_color ?? '#0F1023') }}" class="fx-input w-full h-10">
                    </div>
                    <div>
                        <label class="fx-muted text-xs block mb-1">Dark Surface</label>
                        <input type="color" name="surface_dark_color" value="{{ old('surface_dark_color', $setting->surface_dark_color ?? '#14152B') }}" class="fx-input w-full h-10">
                    </div>
                </div>
            </div>

            <div>
                <p class="fx-heading text-xs uppercase fx-muted mb-2">Contact &amp; Footer</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="fx-muted text-xs block mb-1">Support Email</label>
                        <input type="email" name="support_email" value="{{ old('support_email', $setting->support_email) }}" class="fx-input w-full">
                    </div>
                    <div>
                        <label class="fx-muted text-xs block mb-1">Support Phone</label>
                        <input type="text" name="support_phone" value="{{ old('support_phone', $setting->support_phone) }}" class="fx-input w-full">
                    </div>
                    <div class="md:col-span-2">
                        <label class="fx-muted text-xs block mb-1">Footer Text</label>
                        <textarea name="footer_text" rows="2" class="fx-input w-full">{{ old('footer_text', $setting->footer_text) }}</textarea>
                    </div>
                </div>
            </div>

            <div>
                <p class="fx-heading text-xs uppercase fx-muted mb-2">Social Links</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" name="social_facebook" value="{{ old('social_facebook', $setting->social_facebook) }}" class="fx-input w-full" placeholder="Facebook URL">
                    <input type="text" name="social_instagram" value="{{ old('social_instagram', $setting->social_instagram) }}" class="fx-input w-full" placeholder="Instagram URL">
                    <input type="text" name="social_twitter" value="{{ old('social_twitter', $setting->social_twitter) }}" class="fx-input w-full" placeholder="Twitter/X URL">
                    <input type="text" name="social_telegram" value="{{ old('social_telegram', $setting->social_telegram) }}" class="fx-input w-full" placeholder="Telegram URL">
                </div>
            </div>

            <button class="fx-btn text-sm">Save Branding</button>
        </form>
    </div>

    @if(auth('admin')->user() && auth('admin')->user()->isSuperAdmin())
    <div class="fx-card p-5 mb-6">
        <p class="fx-heading text-sm mb-1">License (Super Admin Only)</p>
        <p class="fx-muted text-xs mb-4">Jab yeh copy kisi client ko resell karo, to unka naam/domain aur expiry date yahan set karo. Agar expiry cross ho jaye to poora panel "License Expired" screen dikhayega, jab tak renew na ho.</p>
        <form method="POST" action="{{ route('admin.settings.license') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            @csrf
            <input type="text" name="license_key" value="{{ $setting->license_key }}" placeholder="License Key" class="fx-input">
            <input type="text" name="licensed_to" value="{{ $setting->licensed_to }}" placeholder="Licensed To (client name)" class="fx-input">
            <input type="text" name="licensed_domain" value="{{ $setting->licensed_domain }}" placeholder="Licensed Domain" class="fx-input">
            <input type="date" name="license_expires_at" value="{{ $setting->license_expires_at?->format('Y-m-d') }}" class="fx-input">
            <button class="fx-btn text-sm md:col-span-4 w-fit">Save License</button>
        </form>
        <p class="fx-muted text-xs mt-3">
            Status: @if(!$setting->license_key) <span style="color: var(--fx-muted);">Not enforced</span> @elseif($setting->isLicenseValid()) <span style="color: var(--fx-green);">Valid</span> @else <span style="color: var(--fx-red);">Expired</span> @endif
        </p>
    </div>
    @endif

    <a href="{{ route('admin.menu.index') }}" class="inline-block text-sm" style="color: var(--fx-amber);">Manage menu items &rarr;</a>

</x-admin-layout>
