// Bump when changing caching rules so old caches are dropped.
const VERSION = 'v1';
const STATIC_CACHE = `codecamp-static-${VERSION}`;
// Resolved against the scope so the app also works when served from a subfolder.
const SCOPE_PATH = new URL(self.registration.scope).pathname;
const OFFLINE_URL = new URL('offline', self.registration.scope).href;

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.add(new Request(OFFLINE_URL, { cache: 'reload' })))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key.startsWith('codecamp-') && key !== STATIC_CACHE)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

function isStaticAsset(url) {
    if (url.origin !== self.location.origin || !url.pathname.startsWith(SCOPE_PATH)) {
        return false;
    }

    const path = url.pathname.slice(SCOPE_PATH.length);

    return path.startsWith('build/')
        || path.startsWith('pwa/')
        || path.startsWith('images/')
        || path.startsWith('flux/')
        || /\.(?:woff2?|ttf|otf)$/.test(path);
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Pages are never cached: they contain per-user data and CSRF tokens.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Vite build files are content-hashed, so cache-first is safe.
    if (isStaticAsset(url)) {
        event.respondWith(
            caches.open(STATIC_CACHE).then(async (cache) => {
                const cached = await cache.match(request);
                if (cached) {
                    return cached;
                }

                const response = await fetch(request);
                if (response.ok) {
                    cache.put(request, response.clone());
                }
                return response;
            })
        );
    }
});
