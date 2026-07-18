const CACHE_PREFIX = 'kes-pwa-';
const CACHE_NAME = `${CACHE_PREFIX}v1`;
const OFFLINE_URL = '/offline.html';

const PRECACHE_URLS = [
    OFFLINE_URL,
    '/images/logo.png',
    '/images/pwa/icon-192x192.png',
    '/images/pwa/icon-512x512.png',
    '/images/pwa/icon-maskable-512x512.png',
    '/images/pwa/apple-touch-icon.png'
];

const EXCLUDED_PREFIXES = [
    '/admin',
    '/customer',
    '/login',
    '/register',
    '/forgot-password',
    '/reset-password',
    '/language',
    '/api',
    '/livewire',
    '/filament',
    '/umrah-vouchers',
    '/umrah-package-posters'
];

function isExcludedPath(pathname) {
    return EXCLUDED_PREFIXES.some((prefix) => (
        pathname === prefix
        || pathname.startsWith(`${prefix}/`)
    ));
}

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => (
                        key.startsWith(CACHE_PREFIX)
                        && key !== CACHE_NAME
                    ))
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (
        url.origin !== self.location.origin
        || isExcludedPath(url.pathname)
    ) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );

        return;
    }

    if (PRECACHE_URLS.includes(url.pathname)) {
        event.respondWith(
            caches.match(url.pathname).then((cachedResponse) => (
                cachedResponse || fetch(request)
            ))
        );
    }
});