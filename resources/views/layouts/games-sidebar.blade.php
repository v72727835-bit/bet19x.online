{{-- Mobile slide-in sidebar listing all game categories & providers (opened via "open-games-sidebar" event) --}}
<div x-data="gamesSidebar()" x-cloak
     @open-games-sidebar.window="show()"
     @keydown.escape.window="open = false"
     class="sm:hidden">
    <div x-show="open" x-transition.opacity @click="open = false"
         class="fixed inset-0 z-[70]" style="background:rgba(0,0,0,.6);"></div>

    <aside x-show="open"
           x-transition:enter="transition transform duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
           x-transition:leave="transition transform duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
           class="fixed top-0 left-0 bottom-0 z-[80] w-[82%] max-w-[340px] overflow-y-auto"
           style="background:#161b22; color:#e5e7eb; font-family:'Rajdhani',sans-serif; padding-bottom:96px;">

        <div class="flex items-center gap-3 p-3" style="position:sticky; top:0; background:#161b22; z-index:1;">
            <div class="flex flex-1 items-center rounded-md px-3" style="background:#3a3d42;">
                <input type="search" x-model="q" placeholder="search" class="w-full bg-transparent border-0 focus:ring-0 text-white text-base py-2 px-0" style="outline:none;">
                <svg class="h-5 w-5 shrink-0" style="color:#9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button @click="open = false" class="p-1" aria-label="Close">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#e5e7eb;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <a href="#" @click.prevent="pick('', '')" class="block px-4 py-2 text-xl font-bold text-white" style="background:#14775a;">All Games</a>

        <p x-show="loading" class="px-4 py-4 text-sm" style="color:#9ca3af;">Loading...</p>
        <p x-show="failed" class="px-4 py-4 text-sm" style="color:#fca5a5;">Games list load nahi hui. Login karke dobara try karein.</p>

        <div x-show="filteredCats().length">
            <div class="px-4 py-2 text-xl font-bold text-white" style="background:#14775a;">Categories</div>
            <template x-for="c in filteredCats()" :key="c[0]">
                <a href="#" @click.prevent="pick(c[0], '')" class="flex items-center justify-between px-4 py-3 text-lg hover:bg-white/5" style="border-bottom:1px solid rgba(255,255,255,.04);">
                    <span x-text="label(c[0])"></span><span style="color:#9ca3af;" x-text="'(' + c[1] + ')'"></span>
                </a>
            </template>
        </div>

        <div x-show="filteredProvs().length">
            <div class="px-4 py-2 text-xl font-bold text-white" style="background:#14775a;">Providers</div>
            <template x-for="p in filteredProvs()" :key="p[0]">
                <a href="#" @click.prevent="pick('', p[0])" class="flex items-center justify-between px-4 py-3 text-lg hover:bg-white/5" style="border-bottom:1px solid rgba(255,255,255,.04);">
                    <span x-text="label(p[0])"></span><span style="color:#9ca3af;" x-text="'(' + p[1] + ')'"></span>
                </a>
            </template>
        </div>
    </aside>
</div>

<script>
function gamesSidebar() {
    return {
        open: false, loading: false, failed: false, loaded: false, q: '', cats: [], provs: [],
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
        label(k) { return k.replace(/_/g, ' ').replace(/\b\w/g, m => m.toUpperCase()); },
        match(k) { return !this.q || this.label(k).toLowerCase().includes(this.q.toLowerCase()); },
        filteredCats() { return this.cats.filter(c => this.match(c[0])); },
        filteredProvs() { return this.provs.filter(p => this.match(p[0])); },
        pick(cat, prov) {
            this.open = false;
            if (window.agSetFilter) { window.agSetFilter(cat, prov); return; }
            const u = new URLSearchParams();
            if (cat) u.set('category', cat);
            if (prov) u.set('provider', prov);
            window.location.href = @json(url('/dashboard')) + (u.toString() ? '?' + u.toString() : '') + '#ag-root';
        }
    };
}
</script>
