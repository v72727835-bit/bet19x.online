/* BET19X service worker.
 *
 * Deliberately tiny and safe for a money site:
 *  - Pages are ALWAYS fetched from the network. They are never stored, so a
 *    balance, wallet or admin page can never be shown out of date.
 *  - Only the build assets, fonts and icons are cached, so the app opens fast.
 *  - When the network is down, a page navigation shows /offline.html.
 */
const VERSION = 'bx-v1';
const STATIC = VERSION + '-static';
const PRECACHE = ['/offline.html', '/icons/icon-192.png', '/icons/icon-512.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(STATIC).then((c) => c.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => !k.startsWith(VERSION)).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

function isCacheableAsset(url) {
    return url.origin === self.location.origin &&
        (url.pathname.startsWith('/build/assets/') || url.pathname.startsWith('/icons/') ||
         url.pathname === '/favicon.ico' || url.pathname.startsWith('/storage/logos/'));
}

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;                       // never touch forms / API writes
    const url = new URL(req.url);

    // Page navigations: network only, offline page as the fallback.
    if (req.mode === 'navigate') {
        event.respondWith(fetch(req).catch(() => caches.match('/offline.html')));
        return;
    }

    // Static assets: serve from cache, refresh in the background.
    if (isCacheableAsset(url)) {
        event.respondWith(
            caches.open(STATIC).then((cache) =>
                cache.match(req).then((hit) => {
                    const refresh = fetch(req).then((res) => { if (res && res.ok) cache.put(req, res.clone()); return res; }).catch(() => hit);
                    return hit || refresh;
                })
            )
        );
    }
    // Everything else (JSON, wallet, games, admin, agent...) goes straight to the network.
});
