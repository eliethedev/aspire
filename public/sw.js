/* ASPIRE minimal service worker (offline encoding workflow).
 * Goal: keep /supervisor/observations/offline usable with zero connectivity.
 * Strategy: cache-first ONLY for the offline page shells + required CSS/JS assets;
 * everything else is network-first so authenticated Blade pages never go stale.
 *
 * Offline-package workspace pages (/offline-workspace/...) are cached at
 * RUNTIME on first online visit (cache-first below), so the exact per-observation
 * URL works later with zero connectivity. The JSON bundle itself lives in
 * IndexedDB (see public/js/aspire-offline-package.js), not the HTTP cache.
 */
const CACHE = 'aspire-offline-v4';
const PRECACHE = [
    '/js/aspire-offline.js',
    '/js/offline-encode.js',
    '/js/aspire-offline-package.js',
    '/supervisor/observations/offline',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE)
            // Add individually so one 404 (e.g. sub-path hosting) never kills the install.
            .then((cache) => Promise.all(PRECACHE.map((u) => cache.add(u).catch(() => null))))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    const p = url.pathname;
    const isStatic = p.includes('/js/') || p.includes('/css/') || p.includes('/build/')
        || p.endsWith('.css') || p.endsWith('.js');
    const isOfflinePage = p.endsWith('/observations/offline');
    // Per-observation offline workspace shell (opened once online, reused offline).
    const isPackageWorkspace = p.includes('/offline-workspace');

    if (isStatic || isOfflinePage || isPackageWorkspace) {
        // Cache-first with network fallback + background refresh.
        event.respondWith(
            caches.match(request).then((hit) => {
                const network = fetch(request).then((res) => {
                    if (res && res.ok) {
                        const copy = res.clone();
                        caches.open(CACHE).then((cache) => cache.put(request, copy));
                    }
                    return res;
                }).catch(() => hit);
                return hit || network;
            })
        );
        return;
    }

    // Default: network-first, no caching of authenticated pages.
    event.respondWith(fetch(request).catch(() => caches.match(request)));
});
