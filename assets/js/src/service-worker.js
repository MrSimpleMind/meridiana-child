/**
 * Service Worker - Offline Detection and Caching
 * Meridiana Platform
 */

const CACHE_NAME = 'meridiana-v1';
const OFFLINE_URL = '/offline/';

// Files to cache on install
const STATIC_ASSETS = [
    OFFLINE_URL,
    '/',
];

// Install event - cache files
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch(() => {
                // If some files fail to cache, that's OK for now
                console.log('Some assets could not be cached');
            });
        })
    );
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch event - serve from cache, fallback to offline page
self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip non-http(s) requests
    if (!event.request.url.startsWith('http')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // If response is OK, cache it
                if (response.ok) {
                    const cache = caches.open(CACHE_NAME);
                    cache.then((cache) => {
                        // Only cache HTML pages and API responses
                        if (response.headers.get('content-type').includes('text/html')) {
                            cache.put(event.request, response.clone());
                        }
                    });
                }
                return response;
            })
            .catch(() => {
                // Network request failed, try to get from cache
                return caches.match(event.request).then((response) => {
                    if (response) {
                        return response;
                    }

                    // If not in cache and it's a navigation request, return offline page
                    if (event.request.mode === 'navigate') {
                        return caches.match(OFFLINE_URL).then((response) => {
                            return response || new Response('Offline', {
                                status: 503,
                                statusText: 'Service Unavailable',
                                headers: new Headers({
                                    'Content-Type': 'text/plain'
                                })
                            });
                        });
                    }

                    return null;
                });
            })
    );
});

// Listen for messages from clients
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
