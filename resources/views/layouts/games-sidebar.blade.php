{{-- Slide-in drawer: account shortcuts + every game category and provider. --}}
@php
    $dwWallet = auth()->check() ? \App\Models\Wallet::where('user_id', auth()->id())->first() : null;
    $dwBalance = $dwWallet ? (float) $dwWallet->deposit_balance + (float) $dwWallet->winnings_balance : 0;
@endphp
<div x-data="gamesSidebar()" @open-games-sidebar.window="show()" @keydown.escape.window="close()">
    <div class="bx-drawer-veil" x-show="open" style="display:none" @click="close()"></div>

    <aside class="bx-drawer" x-show="open" style="display:none" role="dialog" aria-label="Menu">
        <div class="bx-drawer-top">
            <label class="bx-search">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/></svg>
                <input type="search" x-model="q" placeholder="Find a game type or provider" aria-label="Search categories and providers">
            </label>
            <button type="button" class="bx-icon-btn" @click="close()" aria-label="Close menu">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        <div class="bx-drawer-body">
            @auth
                <div style="padding:14px 14px 0;">
                    <div class="bx-panel" style="display:flex; align-items:center; gap:12px;">
                        <div class="bx-grow">
                            <small style="color:var(--bx-muted); font-size:13px;">{{ Auth::user()->name }}</small>
                            <div class="bx-money" style="font-size:30px; color:var(--bx-gold); line-height:1.05;">&#8377;{{ number_format($dwBalance, 2) }}</div>
                        </div>
                        <a href="{{ route('wallet.deposit') }}" class="bx-btn bx-btn-gold bx-btn-sm">Deposit</a>
                    </div>
                </div>
                <div class="bx-drawer-group">Account</div>
                <a class="bx-drow" href="{{ route('wallet.index') }}"><span class="bx-dico">&#128091;</span><span class="bx-dname">Wallet &amp; history</span></a>
                <a class="bx-drow" href="{{ route('wallet.transfer') }}"><span class="bx-dico">&#128184;</span><span class="bx-dname">Send money to a player</span></a>
                <a class="bx-drow" href="{{ route('bets.index') }}"><span class="bx-dico">&#127922;</span><span class="bx-dname">Bet history</span></a>
                <a class="bx-drow" href="{{ route('wallet.withdraw') }}"><span class="bx-dico">&#127974;</span><span class="bx-dname">Withdraw winnings</span></a>
                <button type="button" class="bx-drow" @click="close(); window.dispatchEvent(new CustomEvent('open-support'))"><span class="bx-dico">&#128172;</span><span class="bx-dname">Live support</span></button>
                <a class="bx-drow" href="{{ route('profile.edit') }}"><span class="bx-dico">&#128100;</span><span class="bx-dname">Profile</span></a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bx-drow"><span class="bx-dico">&#128682;</span><span class="bx-dname">Log out</span></button>
                </form>
            @endauth

            <div class="bx-drawer-group">Casino</div>
            <button type="button" class="bx-drow" :class="!sel.cat && !sel.prov ? 'is-on' : ''" @click="pick('', '')">
                <span class="bx-dico">&#127920;</span><span class="bx-dname">All games</span>
            </button>
            <p x-show="loading" style="padding:10px 16px; color:var(--bx-muted); margin:0;">Loading games…</p>
            <p x-show="failed" style="display:none; padding:10px 16px; color:#ffb3bd; margin:0;">Game list didn't load. Pull down to refresh and try again.</p>

            <template x-for="c in filteredCats()" :key="'c' + c[0]">
                <button type="button" class="bx-drow" :class="sel.cat === c[0] ? 'is-on' : ''" @click="pick(c[0], '')">
                    <span class="bx-dico" x-text="icon(c[0])"></span>
                    <span class="bx-dname" x-text="label(c[0])"></span>
                    <span class="bx-dcount" x-text="c[1].toLocaleString('en-IN')"></span>
                </button>
            </template>

            <div class="bx-drawer-group" x-show="filteredProvs().length" style="display:none">Providers</div>
            <template x-for="p in filteredProvs()" :key="'p' + p[0]">
                <button type="button" class="bx-drow" :class="sel.prov === p[0] ? 'is-on' : ''" @click="pick('', p[0])">
                    <span class="bx-dico is-mono" x-text="pname(p[0]).substring(0, 2).toUpperCase()"></span>
                    <span class="bx-dname" x-text="pname(p[0])"></span>
                    <span class="bx-dcount" x-text="p[1].toLocaleString('en-IN')"></span>
                </button>
            </template>

            <p x-show="loaded && q && !filteredCats().length && !filteredProvs().length" style="display:none; padding:10px 16px; color:var(--bx-muted); margin:0;">Nothing matches "<span x-text="q"></span>".</p>
        </div>
    </aside>
</div>

<script>
function gamesSidebar() {
    return {
        open: false, loading: false, failed: false, loaded: false, q: '', cats: [], provs: [], info: {}, sel: { cat: '', prov: '' },
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
                    const pv = d.providers || {};
                    this.provs = (d.provider_order || Object.keys(pv)).filter(k => k in pv).map(k => [k, pv[k]]);
                    this.info = d.provider_info || {};
                    this.loaded = true;
                })
                .catch(() => { this.failed = true; })
                .finally(() => { this.loading = false; });
        },
        close() { this.open = false; },
        label(k) { return String(k).replace(/_/g, ' ').replace(/\b\w/g, m => m.toUpperCase()); },
        pname(k) { return (this.info[k] && this.info[k].name) || this.label(String(k).replace(/^hd_/i, '')); },
        icon(k) { return window.bxCatIcon ? window.bxCatIcon(k) : '\u{1F3AE}'; },
        match(k) { return !this.q || this.label(k).toLowerCase().includes(this.q.toLowerCase()); },
        filteredCats() { return this.cats.filter(c => this.match(c[0])); },
        filteredProvs() { return this.provs.filter(p => !this.q || this.pname(p[0]).toLowerCase().includes(this.q.toLowerCase())); },
        pick(cat, prov) {
            this.sel = { cat: cat, prov: prov };
            this.close();
            if (window.agSetFilter) { window.agSetFilter(cat, prov); return; }
            const u = new URLSearchParams();
            if (cat) u.set('category', cat);
            if (prov) u.set('provider', prov);
            window.location.href = @json(url('/dashboard')) + (u.toString() ? '?' + u.toString() : '') + '#games';
        }
    };
}
window.bxCatIcon = function (k) {
    k = String(k).toLowerCase();
    if (k.includes('slot')) return '\u{1F3B0}';
    if (k.includes('live')) return '\u{1F3B2}';
    if (k.includes('card') || k.includes('poker') || k.includes('teen')) return '\u{1F0CF}';
    if (k.includes('crash')) return '\u{1F680}';
    if (k.includes('sport')) return '⚽';
    if (k.includes('fish')) return '\u{1F41F}';
    if (k.includes('roulette') || k.includes('table')) return '\u{1F3A1}';
    if (k.includes('lotto') || k.includes('lottery') || k.includes('keno') || k.includes('bingo')) return '\u{1F3B1}';
    if (k.includes('mini') || k.includes('marble')) return '\u{1F3AF}';
    return '\u{1F3AE}';
};
</script>
