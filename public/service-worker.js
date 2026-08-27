const CACHE_NAME = 'presensi-cak-v1';

const STATIC_ASSETS = [
    '/offline.html',
    '/manifest.json',
    '/css/m3-tokens.css',
    '/css/m3-components.css',
    '/css/m3-utilities.css',
    '/css/m3-layout-pwa.css',
    '/css/m3-layout-admin.css',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png'
];

// 1. Install Event - Cache Static Assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// 2. Activate Event - Clean Old Caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

// 3. Fetch Event - Strategy
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Static Assets: Cache-First
    if (STATIC_ASSETS.some(asset => url.pathname === asset)) {
        event.respondWith(
            caches.match(event.request).then(cached => {
                return cached || fetch(event.request);
            })
        );
        return;
    }

    // Dynamic Navigation: Network-First with Offline Fallback
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match('/offline.html');
            })
        );
        return;
    }

    // Default: Network with Cache Fallback
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});
