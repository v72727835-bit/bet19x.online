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

    <div id="ag-hotwrap" class="bx-section" hidden>
        <div class="bx-section-head"><h2 class="bx-h2">Trending now</h2></div>
        <div class="bx-row" id="ag-hot"></div>
    </div>

    <div class="bx-section">
        <div class="bx-section-head">
            <h2 class="bx-h2" id="ag-title">All games</h2>
            <button type="button" class="bx-link" id="ag-clear" hidden>Show all</button>
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
    var st = { q: '', category: qs.get('category') || '', provider: qs.get('provider') || '', page: 1 };
    var $ = function (id) { return document.getElementById(id); };
    var grid = $('ag-grid'), more = $('ag-more'), err = $('ag-err'), chips = $('ag-chips'), hot = $('ag-hot'), hotWrap = $('ag-hotwrap');
    var busy = false, timer, cats = {};

    function label(k) { return String(k).replace(/_/g, ' ').replace(/\b\w/g, function (m) { return m.toUpperCase(); }); }
    function icon(k) { return window.bxCatIcon ? window.bxCatIcon(k) : ''; }

    function tile(x) {
        var a = document.createElement('a');
        a.className = 'bx-tile';
        a.href = LA + encodeURIComponent(x.id);
        a.setAttribute('aria-label', 'Play ' + (x.title || 'game'));
        var mono = document.createElement('span');
        mono.className = 'bx-tile-mono';
        mono.textContent = (x.title || '?').charAt(0);
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
        var s = document.createElement('small'); s.textContent = label(x.provider || '');
        cap.appendChild(b); cap.appendChild(s); a.appendChild(cap);
        return a;
    }

    function filtered() { return !!(st.q || st.category || st.provider); }

    function syncUi() {
        hotWrap.hidden = filtered() || !hot.children.length;
        $('ag-clear').hidden = !(st.category || st.provider);
        $('ag-title').textContent = st.provider ? label(st.provider) : st.category ? label(st.category) : (st.q ? 'Results' : 'All games');
        Array.prototype.forEach.call(chips.children, function (c) {
            c.classList.toggle('is-on', !st.provider && c.getAttribute('data-cat') === st.category);
        });
    }

    function load(reset) {
        if (busy) return;
        busy = true;
        if (reset) { st.page = 1; grid.innerHTML = ''; }
        syncUi();
        var u = L + '?q=' + encodeURIComponent(st.q) + '&category=' + encodeURIComponent(st.category) +
            '&provider=' + encodeURIComponent(st.provider) + '&page=' + st.page;
        fetch(u, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                err.hidden = !d.error;
                if (d.error) err.textContent = 'Games are not loading right now. Refresh the page in a minute.';
                (d.data || []).forEach(function (x) { grid.appendChild(tile(x)); });
                more.hidden = !(st.page * d.per < d.total);
                $('ag-count').textContent = d.total ? d.total.toLocaleString('en-IN') + ' games' : '';
                if (!d.total && !d.error) {
                    grid.innerHTML = '<p class="bx-empty">No games match this search. Try another name or pick a game type above.</p>';
                }
            })
            .catch(function () { err.textContent = 'Games are not loading right now. Check your connection and refresh.'; err.hidden = false; })
            .finally(function () { busy = false; });
    }

    window.agSetFilter = function (cat, prov) {
        st.category = cat || ''; st.provider = prov || ''; st.q = ''; $('ag-q').value = '';
        load(true);
        $('games').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    more.onclick = function () { st.page++; load(false); };
    $('ag-clear').onclick = function () { window.agSetFilter('', ''); };
    $('ag-q').oninput = function (e) {
        clearTimeout(timer);
        var v = e.target.value;
        timer = setTimeout(function () { st.q = v; load(true); }, 300);
    };

    function chip(text, cat) {
        var b = document.createElement('button');
        b.type = 'button'; b.className = 'bx-chipbtn'; b.textContent = text;
        b.setAttribute('data-cat', cat);
        b.onclick = function () { window.agSetFilter(cat, ''); };
        chips.appendChild(b);
    }

    fetch(M, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            cats = d.categories || {};
            chip('All', '');
            Object.keys(cats).forEach(function (k) { chip(icon(k) + ' ' + label(k), k); });
            var total = Object.keys(cats).reduce(function (n, k) { return n + cats[k]; }, 0);
            var gc = document.getElementById('bx-gamecount');
            if (gc && total) gc.textContent = total.toLocaleString('en-IN');
            syncUi();
        })
        .catch(function () {});

    load(true);

    fetch(L + '?page=1', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) { (d.data || []).slice(0, 14).forEach(function (x) { hot.appendChild(tile(x)); }); syncUi(); })
        .catch(function () {});
})();
</script>
