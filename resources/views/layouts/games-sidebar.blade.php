{{-- Mobile slide-in games sidebar. Styled inline on purpose (does not depend on the compiled Tailwind build). --}}
<style>
    @keyframes gsSlideIn { from { transform: translateX(-100%); } to { transform: translateX(0); } }
    @keyframes gsFade { from { opacity: 0; } to { opacity: 1; } }
    .gs-aside { animation: gsSlideIn .22s ease-out; }
    .gs-overlay { animation: gsFade .2s ease-out; }
    .gs-row { display:flex; align-items:center; gap:12px; width:100%; padding:11px 16px; background:transparent; border:0;
              border-bottom:1px solid rgba(255,255,255,.05); color:#e5e7eb; font-size:17px; text-align:left; cursor:pointer; }
    .gs-row:active { background:rgba(255,255,255,.08); }
    .gs-row.gs-on { background:rgba(230,0,18,.22); color:#fff; box-shadow:inset 3px 0 0 #f0a81a; }
    .gs-ico { width:34px; height:34px; border-radius:10px; background:linear-gradient(180deg,#2a2a33,#1a1a21); border:1px solid rgba(240,168,26,.25); display:flex; align-items:center;
              justify-content:center; font-size:18px; flex:none; }
    .gs-name { flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .gs-count { color:#9ca3af; font-size:13px; background:#232a34; border-radius:999px; padding:2px 9px; flex:none; }
    .gs-head { padding:9px 16px; font-size:19px; font-weight:700; color:#fff; background:linear-gradient(90deg,#8b0010,#3a0a10); border-left:4px solid #f0a81a; letter-spacing:.3px; }
    @media (min-width: 640px) { #gs-root { display:none !important; } }
</style>

<div id="gs-root" x-data="gamesSidebar()" @open-games-sidebar.window="show()" @keydown.escape.window="close()">
    <div class="gs-overlay" x-show="open" style="display:none; position:fixed; inset:0; z-index:9998; background:rgba(0,0,0,.65);" @click="close()"></div>

    <aside class="gs-aside" x-show="open" style="display:none; position:fixed; top:0; left:0; bottom:0; width:86%; max-width:340px; z-index:9999;
           background:#0f0f14; color:#e5e7eb; font-family:'Rajdhani',Inter,sans-serif; flex-direction:column; box-shadow:6px 0 24px rgba(0,0,0,.5);"
           :style="open ? 'display:flex' : 'display:none'">

        <div style="flex:none; display:flex; align-items:center; gap:10px; padding:12px; background:#0f0f14; border-bottom:1px solid rgba(240,168,26,.25);">
            <div style="flex:1; display:flex; align-items:center; background:#3a3d42; border-radius:8px; padding:0 12px;">
                <input type="search" x-model="q" placeholder="Search games, providers..."
                       style="flex:1; min-width:0; background:transparent; border:0; outline:none; box-shadow:none; color:#fff; font-size:16px; padding:10px 0;">
                <span style="color:#9ca3af; font-size:16px;">&#128269;</span>
            </div>
            <button type="button" @click="close()" aria-label="Close menu"
                    style="flex:none; width:40px; height:40px; border-radius:10px; background:#232a34; border:0; color:#fff; font-size:22px; line-height:1; cursor:pointer;">&times;</button>
        </div>

        <div style="flex:1; min-height:0; overflow-y:auto; -webkit-overflow-scrolling:touch; overscroll-behavior:contain; padding-bottom:110px;">
            @auth
                @php
                    $gsW = \App\Models\Wallet::where('user_id', Auth::id())->first();
                    $gsBal = (float) ($gsW->deposit_balance ?? 0) + (float) ($gsW->winnings_balance ?? 0);
                @endphp
                <div style="margin:12px; padding:14px; border-radius:16px; border:1px solid #5a3a10; background:linear-gradient(135deg,#3a0a10,#1a0a0c 65%,#2a1a05);">
                    <div style="color:#c9a86a; font-size:12px; letter-spacing:1px; text-transform:uppercase; font-weight:600;">{{ Auth::user()->name }}</div>
                    <div style="color:#ffd76a; font-size:26px; font-weight:800; line-height:1.15;">&#8377;{{ number_format($gsBal, 2) }}</div>
                    <div style="display:flex; gap:8px; margin-top:10px;">
                        <a href="{{ route('wallet.deposit') }}" style="flex:1; text-align:center; padding:9px 0; border-radius:10px; background:linear-gradient(180deg,#34d399,#059669); color:#fff; font-weight:800; text-decoration:none;">Deposit</a>
                        <a href="{{ route('wallet.withdraw') }}" style="flex:1; text-align:center; padding:9px 0; border-radius:10px; background:linear-gradient(180deg,#ff2d3d,#b30010); color:#fff; font-weight:800; text-decoration:none;">Withdraw</a>
                    </div>
                </div>
            @endauth

            <button type="button" class="gs-row" :class="!sel.cat && !sel.prov ? 'gs-on' : ''" @click="pick('', '')">
                <span class="gs-ico">&#127918;</span><span class="gs-name" style="font-weight:700;">All Games</span>
            </button>

            <p x-show="loading" style="padding:16px; color:#9ca3af; font-size:14px;">Loading...</p>
            <p x-show="failed" style="display:none; padding:16px; color:#fca5a5; font-size:14px;">Games list load nahi hui. Login karke dobara try karein.</p>

            <div x-show="filteredCats().length" style="display:none;">
                <div class="gs-head">Categories</div>
                <template x-for="c in filteredCats()" :key="'c' + c[0]">
                    <button type="button" class="gs-row" :class="sel.cat === c[0] ? 'gs-on' : ''" @click="pick(c[0], '')">
                        <span class="gs-ico" x-text="icon(c[0])"></span>
                        <span class="gs-name" x-text="label(c[0])"></span>
                        <span class="gs-count" x-text="c[1]"></span>
                    </button>
                </template>
            </div>

            <div x-show="filteredProvs().length" style="display:none;">
                <div class="gs-head">Providers</div>
                <template x-for="p in filteredProvs()" :key="'p' + p[0]">
                    <button type="button" class="gs-row" :class="sel.prov === p[0] ? 'gs-on' : ''" @click="pick('', p[0])">
                        <span class="gs-ico" style="font-size:14px; font-weight:700; color:#f0a81a;" x-text="label(p[0]).substring(0, 2).toUpperCase()"></span>
                        <span class="gs-name" x-text="label(p[0])"></span>
                        <span class="gs-count" x-text="p[1]"></span>
                    </button>
                </template>
            </div>

            <p x-show="loaded && q && !filteredCats().length && !filteredProvs().length" style="display:none; padding:16px; color:#9ca3af; font-size:14px;">Kuch nahi mila.</p>
        </div>
    </aside>
</div>

<script>
function gamesSidebar() {
    return {
        open: false, loading: false, failed: false, loaded: false, q: '', cats: [], provs: [], sel: { cat: '', prov: '' },
        init() {
            this.$watch('open', v => { document.body.style.overflow = v ? 'hidden' : ''; });
        },
        show() {
            this.open = true;
            if (this.loaded || this.loading) return;
            this.loading = true; this.failed = false;
            fetch(@json(route('astech.meta')), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(r => r.json())
                .then(d => {
                    this.cats = Object.entries(d.categories || {});
                    this.provs = Object.entries(d.providers || {});
                    this.loaded = true;
                })
                .catch(() => { this.failed = true; })
                .finally(() => { this.loading = false; });
        },
        close() { this.open = false; },
        label(k) { return String(k).replace(/_/g, ' ').replace(/\b\w/g, m => m.toUpperCase()); },
        icon(k) {
            k = String(k).toLowerCase();
            if (k.includes('slot')) return '\u{1F3B0}';
            if (k.includes('live')) return '\u{1F4FA}';
            if (k.includes('card') || k.includes('poker') || k.includes('teen')) return '\u{1F0CF}';
            if (k.includes('crash')) return '\u{1F680}';
            if (k.includes('sport')) return '⚽';
            if (k.includes('fish')) return '\u{1F41F}';
            if (k.includes('roulette') || k.includes('table')) return '\u{1F3A1}';
            if (k.includes('lotto') || k.includes('lottery') || k.includes('keno') || k.includes('bingo')) return '\u{1F3B1}';
            if (k.includes('mini') || k.includes('marble')) return '\u{1F3AF}';
            if (k.includes('hot')) return '\u{1F525}';
            return '\u{1F3AE}';
        },
        match(k) { return !this.q || this.label(k).toLowerCase().includes(this.q.toLowerCase()); },
        filteredCats() { return this.cats.filter(c => this.match(c[0])); },
        filteredProvs() { return this.provs.filter(p => this.match(p[0])); },
        pick(cat, prov) {
            this.sel = { cat: cat, prov: prov };
            this.close();
            if (window.agSetFilter) { window.agSetFilter(cat, prov); return; }
            const u = new URLSearchParams();
            if (cat) u.set('category', cat);
            if (prov) u.set('provider', prov);
            window.location.href = @json(url('/dashboard')) + (u.toString() ? '?' + u.toString() : '') + '#ag-root';
        }
    };
}
</script>
