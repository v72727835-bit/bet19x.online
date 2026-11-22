<section id="games" class="bx-section" aria-label="Casino games">
    @if (session('error'))
        <div class="bx-alert bx-alert-err">{{ session('error') }}</div>
    @endif
    <div id="ag-err" class="bx-alert bx-alert-err" hidden></div>

    <label class="bx-search">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/></svg>
        <input id="ag-q" type="search" placeholder="Search Aviator, Teen Patti, Roulette…" aria-label="Search games">
    </label>
    <div class="bx-chips" id="ag-chips" role="group" aria-label="Game types"></div>

    {{-- Home view: every provider; tapping one opens its games right below its row --}}
    <div id="ag-provview" class="bx-section">
        <div class="bx-section-head">
            <h2 class="bx-h2">Providers</h2>
            <span class="bx-sub" id="ag-provcount"></span>
        </div>
        <div class="bx-provgrid" id="ag-provs">
            <p class="bx-empty">Loading providers…</p>
        </div>
    </div>

    {{-- Results view: a game type or a search --}}
    <div id="ag-resview" class="bx-section" hidden>
        <div class="bx-section-head">
            <h2 class="bx-h2" id="ag-title">Games</h2>
            <button type="button" class="bx-link" id="ag-clear">All providers</button>
        </div>
        <p class="bx-sub" id="ag-count" style="margin:-6px 0 12px;"></p>
        <div class="bx-grid" id="ag-grid"></div>
        <button type="button" class="bx-btn bx-btn-ghost bx-btn-block" id="ag-more" style="margin-top:18px;" hidden>Show more games</button>
    </div>
</section>

<script>
(function () {
    var L = @json(route('astech.list')), M = @json(route('astech.meta')), LA = @json(url('/games/launch')) + '/';
    var qs = new URLSearchParams(location.search);
    var st = { q: '', category: qs.get('category') || '', page: 1 };
    var wantProvider = qs.get('provider') || '';
    var $ = function (id) { return document.getElementById(id); };
    var grid = $('ag-grid'), more = $('ag-more'), err = $('ag-err'), chips = $('ag-chips'), provs = $('ag-provs');
    var busy = false, timer, info = {}, openKey = null, panel = null;

    function label(k) { return String(k).replace(/^hd_/i, '').replace(/_/g, ' ').replace(/\b\w/g, function (m) { return m.toUpperCase(); }); }
    function pname(k) { return (info[k] && info[k].name) || label(k); }
    function icon(k) { return window.bxCatIcon ? window.bxCatIcon(k) : ''; }
    function getJson(u) { return fetch(u, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }).then(function (r) { return r.json(); }); }
    function showErr(on) { err.hidden = !on; if (on) err.textContent = 'Games are not loading right now. Refresh the page in a minute.'; }

    function tile(x) {
        var a = document.createElement('a');
        a.className = 'bx-tile';
        a.href = LA + encodeURIComponent(x.id);
        a.setAttribute('aria-label', 'Play ' + (x.title || 'game'));
        var mono = document.createElement('span');
        mono.className = 'bx-tile-mono'; mono.textContent = (x.title || '?').charAt(0);
        a.appendChild(mono);
        if (x.img) {
            var im = document.createElement('img');
            im.loading = 'lazy'; im.alt = ''; im.src = x.img;
            im.onerror = function () { im.remove(); };
            a.appendChild(im);
        }
        var cap = document.createElement('span');
        cap.className = 'bx-tile-cap';
        var b = document.createElement('b'); b.textContent = x.title || '';
        var s = document.createElement('small'); s.textContent = pname(x.provider || '');
        cap.appendChild(b); cap.appendChild(s); a.appendChild(cap);
        return a;
    }

    /* ---------- results view (game type / search) ---------- */
    function setView() {
        var results = !!(st.q || st.category);
        $('ag-resview').hidden = !results;
        $('ag-provview').hidden = results;
        $('ag-title').textContent = st.category ? label(st.category) : 'Results';
        Array.prototype.forEach.call(chips.children, function (c) {
            c.classList.toggle('is-on', c.getAttribute('data-cat') === st.category && !st.q);
        });
    }

    function loadResults(reset) {
        if (busy) return;
        busy = true;
        if (reset) { st.page = 1; grid.innerHTML = ''; }
        getJson(L + '?q=' + encodeURIComponent(st.q) + '&category=' + encodeURIComponent(st.category) + '&page=' + st.page)
            .then(function (d) {
                showErr(!!d.error);
                (d.data || []).forEach(function (x) { grid.appendChild(tile(x)); });
                more.hidden = !(st.page * d.per < d.total);
                $('ag-count').textContent = d.total ? d.total.toLocaleString('en-IN') + ' games' : '';
                if (!d.total && !d.error) grid.innerHTML = '<p class="bx-empty">No games match this search. Try another name, or pick a provider.</p>';
            })
            .catch(function () { showErr(true); })
            .finally(function () { busy = false; });
    }

    /* ---------- provider accordion ---------- */
    function closePanel() {
        if (panel) panel.remove();
        panel = null;
        if (openKey) {
            var t = provs.querySelector('[data-prov="' + CSS.escape(openKey) + '"]');
            if (t) { t.classList.remove('is-open'); t.setAttribute('aria-expanded', 'false'); }
        }
        openKey = null;
    }

    function rowEnd(tileEl) {
        // last provider tile on the same visual row as tileEl
        var top = tileEl.offsetTop, last = tileEl, n = tileEl.nextElementSibling;
        while (n && n.classList.contains('bx-prov') && n.offsetTop === top) { last = n; n = n.nextElementSibling; }
        return last;
    }

    function openProvider(key, scroll) {
        var t = provs.querySelector('[data-prov="' + CSS.escape(key) + '"]');
        if (!t) return;
        if (openKey === key) { closePanel(); return; }
        closePanel();
        openKey = key;
        t.classList.add('is-open'); t.setAttribute('aria-expanded', 'true');

        panel = document.createElement('div');
        panel.className = 'bx-provpanel';
        panel.id = 'ag-panel';
        var head = document.createElement('div');
        head.className = 'bx-provpanel-head';
        var h = document.createElement('h3'); h.className = 'bx-h2'; h.textContent = pname(key);
        var cnt = document.createElement('span'); cnt.className = 'bx-sub'; cnt.style.margin = '0';
        var x = document.createElement('button'); x.type = 'button'; x.className = 'bx-btn bx-btn-ghost bx-btn-sm'; x.textContent = 'Close';
        x.onclick = function () { var tt = t; closePanel(); tt.focus(); };
        var titles = document.createElement('div'); titles.appendChild(h); titles.appendChild(cnt);
        head.appendChild(titles); head.appendChild(x);
        var g = document.createElement('div'); g.className = 'bx-grid';
        var mb = document.createElement('button'); mb.type = 'button'; mb.className = 'bx-btn bx-btn-ghost bx-btn-block'; mb.style.marginTop = '14px'; mb.hidden = true; mb.textContent = 'Show more games';
        panel.appendChild(head); panel.appendChild(g); panel.appendChild(mb);
        rowEnd(t).after(panel);

        var page = 1, loading = false;
        function loadMore() {
            if (loading) return;
            loading = true; mb.disabled = true;
            if (page === 1) g.innerHTML = '<p class="bx-empty">Loading games…</p>';
            getJson(L + '?provider=' + encodeURIComponent(key) + '&page=' + page)
                .then(function (d) {
                    if (openKey !== key) return;
                    if (page === 1) g.innerHTML = '';
                    showErr(!!d.error);
                    (d.data || []).forEach(function (row) { g.appendChild(tile(row)); });
                    cnt.textContent = d.total ? d.total.toLocaleString('en-IN') + ' games' : '';
                    mb.hidden = !(page * d.per < d.total);
                    if (!d.total && !d.error) g.innerHTML = '<p class="bx-empty">This provider has no games available in INR right now.</p>';
                    page++;
                })
                .catch(function () { showErr(true); })
                .finally(function () { loading = false; mb.disabled = false; });
        }
        mb.onclick = loadMore;
        loadMore();
        if (scroll) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        else t.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function providerTile(key, count) {
        var b = document.createElement('button');
        b.type = 'button'; b.className = 'bx-prov';
        b.setAttribute('data-prov', key);
        b.setAttribute('aria-expanded', 'false');
        b.setAttribute('aria-controls', 'ag-panel');
        var art = document.createElement('span'); art.className = 'bx-prov-art';
        var mono = document.createElement('span'); mono.className = 'bx-prov-mono'; mono.textContent = pname(key).substring(0, 2).toUpperCase();
        art.appendChild(mono);
        var logo = info[key] && info[key].logo;
        if (logo) {
            var im = document.createElement('img'); im.loading = 'lazy'; im.alt = ''; im.src = logo;
            im.onload = function () { mono.hidden = true; };
            im.onerror = function () { im.remove(); };
            art.appendChild(im);
        }
        var nm = document.createElement('b'); nm.textContent = pname(key);
        var c = document.createElement('small'); c.textContent = count.toLocaleString('en-IN') + ' games';
        b.appendChild(art); b.appendChild(nm); b.appendChild(c);
        b.onclick = function () { openProvider(key, false); };
        return b;
    }

    /* ---------- public hook used by the drawer ---------- */
    window.agSetFilter = function (cat, prov) {
        st.q = ''; $('ag-q').value = '';
        st.category = cat || '';
        setView();
        if (prov) { openProvider(prov, true); return; }
        if (st.category) loadResults(true); else closePanel();
        $('games').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    more.onclick = function () { st.page++; loadResults(false); };
    $('ag-clear').onclick = function () { window.agSetFilter('', ''); };
    $('ag-q').oninput = function (e) {
        clearTimeout(timer);
        var v = e.target.value.trim();
        timer = setTimeout(function () { st.q = v; if (v) st.category = ''; setView(); if (st.q || st.category) loadResults(true); }, 300);
    };

    function chip(text, cat) {
        var b = document.createElement('button');
        b.type = 'button'; b.className = 'bx-chipbtn'; b.textContent = text;
        b.setAttribute('data-cat', cat);
        b.onclick = function () { window.agSetFilter(cat, ''); };
        chips.appendChild(b);
    }

    getJson(M).then(function (d) {
        info = d.provider_info || {};
        var cats = d.categories || {}, pv = d.providers || {};
        chip('Providers', '');
        Object.keys(cats).forEach(function (k) { chip(icon(k) + ' ' + label(k), k); });

        provs.innerHTML = '';
        var keys = Object.keys(pv);
        keys.forEach(function (k) { provs.appendChild(providerTile(k, pv[k])); });
        $('ag-provcount').textContent = keys.length ? keys.length + ' providers' : '';
        if (!keys.length) provs.innerHTML = '<p class="bx-empty">Providers are not loading right now. Refresh the page in a minute.</p>';

        var total = Object.keys(cats).reduce(function (n, k) { return n + cats[k]; }, 0);
        var gc = document.getElementById('bx-gamecount');
        if (gc && total) gc.textContent = total.toLocaleString('en-IN');

        setView();
        if (st.category) loadResults(true);
        else if (wantProvider) openProvider(wantProvider, true);
    }).catch(function () {
        provs.innerHTML = '<p class="bx-empty">Providers are not loading right now. Refresh the page in a minute.</p>';
    });

    // re-anchor an open panel when the grid reflows (rotation / resize)
    var rt;
    window.addEventListener('resize', function () {
        clearTimeout(rt);
        rt = setTimeout(function () {
            if (!panel || !openKey) return;
            var t = provs.querySelector('[data-prov="' + CSS.escape(openKey) + '"]');
            if (t) { panel.remove(); rowEnd(t).after(panel); }
        }, 150);
    });
})();
</script>
