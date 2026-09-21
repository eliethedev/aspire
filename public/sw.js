/* ASPIRE minimal service worker (Architecture B v1).
 * Goal: keep the offline capture shell usable with zero connectivity.
 * Strategy: cache-first ONLY for versioned static assets + offline page shell;
 * everything else is network-first so authenticated Blade pages never go stale.
 */
const CACHE = 'aspire-offline-v2';
const PRECACHE = [
    '/js/aspire-offline.js',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
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
    const isStatic = url.pathname.startsWith('/js/') || url.pathname.startsWith('/css/') || url.pathname.startsWith('/build/');
    const isOfflinePage = url.pathname.endsWith('/observations/offline');

    if (isStatic || isOfflinePage) {
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
