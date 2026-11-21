<x-master-layout title="Tenants / White-Label Domains">

        <div class="mp-card" style="padding:24px; margin-bottom:24px;">
            <p style="font-size:14px; font-weight:600; margin:0 0 4px 0;">Naya Client Domain Add Karo</p>
            <p style="font-size:12px; color: var(--mp-muted); margin:0 0 16px 0;">Domain daalte hi is codebase ka poora naya isolated database provision hoga — us domain pe apna alag admin login, apne alag users/wallet/contests honge.</p>
            <form method="POST" action="{{ route('master.tenants.store') }}">
                @csrf
                <div class="mp-grid mp-grid-2">
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Domain (bina https://, bina www)</label>
                    <input type="text" name="domain" placeholder="clientbrand.com" class="mp-input" required value="{{ old('domain') }}">
                </div>
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Brand Name</label>
                    <input type="text" name="brand_name" placeholder="ClientBrand XI" class="mp-input" required value="{{ old('brand_name') }}">
                </div>
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Client Phone</label>
                    <input type="text" name="client_phone" placeholder="9876543210" class="mp-input" value="{{ old('client_phone') }}">
                </div>
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Client Contact Email</label>
                    <input type="email" name="client_email" placeholder="client@personal-email.com" class="mp-input" value="{{ old('client_email') }}">
                </div>
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Sale Type</label>
                    <select name="sale_type" class="mp-input">
                        <option value="one_time">One-Time Payment</option>
                        <option value="subscription">Subscription</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Price (₹)</label>
                    <input type="number" step="0.01" name="price" placeholder="25000" class="mp-input" value="{{ old('price') }}">
                </div>
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Billing Cycle (agar subscription hai)</label>
                    <select name="billing_cycle" class="mp-input">
                        <option value="">—</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="half_yearly">Half-Yearly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Next Billing Date</label>
                    <input type="date" name="next_billing_date" class="mp-input">
                </div>
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Database Name</label>
                    <input type="text" name="db_name" placeholder="cpaneluser_clientbrand" class="mp-input" required value="{{ old('db_name') }}">
                </div>
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Database Username</label>
                    <input type="text" name="db_username" placeholder="cpaneluser_dbuser" class="mp-input" required value="{{ old('db_username') }}">
                </div>
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Database Password</label>
                    <input type="password" name="db_password" class="mp-input">
                </div>
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">DB Host (usually 127.0.0.1)</label>
                    <input type="text" name="db_host" placeholder="127.0.0.1" class="mp-input" value="127.0.0.1">
                </div>
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Client's First Admin Email</label>
                    <input type="email" name="admin_email" placeholder="admin@clientbrand.com" class="mp-input" required>
                </div>
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Client's First Admin Password</label>
                    <input type="password" name="admin_password" class="mp-input" required>
                </div>
                <div>
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">License Expiry (optional)</label>
                    <input type="date" name="license_expires_at" class="mp-input">
                </div>
                </div>
                <div style="margin-top:16px;">
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Client Notes</label>
                    <textarea name="client_notes" rows="2" class="mp-input">{{ old('client_notes') }}</textarea>
                </div>
                <div style="margin-top:16px;">
                    <button class="mp-btn">+ Add Domain &amp; Provision</button>
                </div>
            </form>
        </div>

        <div class="mp-card" style="padding:24px; overflow-x:auto;">
            <p style="font-size:14px; font-weight:600; margin:0 0 16px 0;">Sab Tenants</p>
            <table class="mp-table">
                <thead><tr><th>Domain</th><th>Brand</th><th>Client</th><th>Sale</th><th>DB</th><th>Status</th><th>License</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($tenants as $t)
                        <tr>
                            <td><a href="https://{{ $t->domain }}" target="_blank" style="color: var(--mp-accent);">{{ $t->domain }}</a></td>
                            <td>{{ $t->brand_name }}</td>
                            <td style="color: var(--mp-muted); font-size:11px;">
                                {{ $t->client_phone }}<br>{{ $t->client_email }}
                            </td>
                            <td style="font-size:11px;">
                                @if($t->sale_type === 'subscription')
                                    <span class="mp-badge" style="background-color:#8B5CF622; color:#8B5CF6;">Subscription</span><br>
                                    <span style="color: var(--mp-muted);">₹{{ number_format($t->price ?? 0, 0) }} / {{ str_replace('_',' ', $t->billing_cycle ?? '—') }}</span><br>
                                    @if($t->isBillingOverdue())
                                        <span style="color: var(--mp-red);">⚠ Billing overdue ({{ $t->next_billing_date?->format('d M Y') }})</span>
                                    @else
                                        <span style="color: var(--mp-muted);">Next: {{ $t->next_billing_date?->format('d M Y') ?? '—' }}</span>
                                    @endif
                                @else
                                    <span class="mp-badge" style="background-color:#34D39922; color:#34D399;">One-Time</span><br>
                                    <span style="color: var(--mp-muted);">₹{{ number_format($t->price ?? 0, 0) }}</span>
                                @endif
                            </td>
                            <td style="color: var(--mp-muted);">{{ $t->db_name }}</td>
                            <td>
                                @php
                                    $color = match($t->status) { 'active' => '#34D399', 'provisioning' => '#FBBF24', 'failed' => '#F87171', default => '#8A8CB3' };
                                @endphp
                                <span class="mp-badge" style="background-color:{{ $color }}22; color:{{ $color }};">{{ $t->status }}</span>
                            </td>
                            <td style="color: var(--mp-muted);">{{ $t->license_expires_at?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <div style="display:flex; flex-direction:column; gap:8px;">
                                    @if($t->status === 'failed' || $t->status === 'provisioning')
                                        <form method="POST" action="{{ route('master.tenants.retry', $t) }}" style="display:flex; gap:4px; align-items:center;">
                                            @csrf
                                            <input type="email" name="admin_email" placeholder="admin email" class="mp-input" style="width:120px;" required>
                                            <input type="password" name="admin_password" placeholder="password" class="mp-input" style="width:100px;" required>
                                            <button class="mp-btn-outline">Retry</button>
                                        </form>
                                    @endif
                                    <div style="display:flex; gap:8px;">
                                        <form method="POST" action="{{ route('master.tenants.toggle', $t) }}">
                                            @csrf
                                            <button class="mp-btn-outline">{{ $t->status === 'active' ? 'Suspend' : 'Activate' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('master.tenants.destroy', $t) }}" onsubmit="return confirm('Sirf mapping hatega, database delete nahi hoga. Confirm?')">
                                            @csrf @method('DELETE')
                                            <button class="mp-btn-outline" style="color: var(--mp-red);">Remove</button>
                                        </form>
                                    </div>
                                </div>
                                @if($t->client_notes)
                                    <details style="margin-top:8px;">
                                        <summary style="color: var(--mp-muted); font-size:11px; cursor:pointer;">Client notes</summary>
                                        <p style="color: var(--mp-muted); font-size:10px;">{{ $t->client_notes }}</p>
                                    </details>
                                @endif
                                @if($t->provision_log)
                                    <details style="margin-top:8px;">
                                        <summary style="color: var(--mp-muted); font-size:11px; cursor:pointer;">Provision log</summary>
                                        <pre style="color: var(--mp-muted); font-size:10px; white-space:pre-wrap;">{{ $t->provision_log }}</pre>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center; padding:24px; color: var(--mp-muted);">Koi tenant nahi. Upar se ek add karo.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div style="margin-top:16px;">{{ $tenants->links() }}</div>
        </div>

        <div class="mp-card" style="padding:20px; margin-top:24px; font-size:12px; color: var(--mp-muted);">
            <p style="font-weight:600; color: var(--mp-text); margin:0 0 8px 0;">Domain live karne ke liye 2 manual steps (ek baar, per domain):</p>
            <p>1. cPanel &rarr; Domains &rarr; "Create A New Domain" mein yeh domain add karo, aur Document Root isi Laravel project ke <code>public</code> folder pe point karo (jaisa main domain ke liye already set hai).</p>
            <p>2. cPanel &rarr; MySQL Databases mein upar diya gaya Database Name + Username create karo (agar auto-create fail ho jaye — zyadatar shared hosting me hota hai), aur us user ko us DB ka "All Privileges" access do.</p>
            <p style="margin-top:8px;">Dono steps hone ke baad upar se "Add Domain & Provision" dabao (ya "Retry" agar pehle try kiya tha) — system migrations run karke us domain ko turant live kar dega, apne alag data ke saath.</p>
        </div>

</x-master-layout>
