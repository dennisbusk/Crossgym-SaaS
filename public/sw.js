const CACHE_NAME = 'crossgym-cache-v2';
const urlsToCache = [
  '/manifest.json',
  '/favicon.ico',
  '/favicon.svg',
];

self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.filter(cacheName => {
          return cacheName.startsWith('crossgym-cache-') && cacheName !== CACHE_NAME;
        }).map(cacheName => {
          return caches.delete(cacheName);
        })
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  // Only handle GET requests for potential cache matches
  if (event.request.method !== 'GET') {
    return;
  }

  // Do not intercept HTML requests to avoid caching old CSRF tokens
  if (event.request.headers.get('accept')?.includes('text/html')) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(response => {
        return response || fetch(event.request);
      })
  );
});
