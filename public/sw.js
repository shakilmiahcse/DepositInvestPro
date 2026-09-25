const CACHE_NAME = 'creditlite-pwa-v1.0';
const OFFLINE_URL = './offline.html';

const STATIC_ASSETS = [
  './offline.html',
  './manifest.json',
  './images/icons/icon-192x192.png',
  './images/icons/icon-512x512.png',
  './images/icons/apple-touch-icon.png'
];

// Install Event - Pre-cache offline fallback
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

// Activate Event - Clean up stale caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cache) => {
          if (cache !== CACHE_NAME) {
            return caches.delete(cache);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// Fetch Event
self.addEventListener('fetch', (event) => {
  // Only handle GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Handle navigation (HTML pages) -> Network First, Fallback to offline.html
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(() => {
        return caches.match(OFFLINE_URL);
      })
    );
    return;
  }

  // Static Assets (Images, Fonts, CSS, JS) -> Stale-While-Revalidate or Network First
  const url = new URL(event.request.url);
  const isStatic = url.pathname.match(/\.(css|js|woff2?|ttf|png|jpe?g|svg|ico)$/i);

  if (isStatic) {
    event.respondWith(
      caches.match(event.request).then((cachedResponse) => {
        const fetchPromise = fetch(event.request).then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
            const responseToCache = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });
          }
          return networkResponse;
        }).catch(() => cachedResponse);

        return cachedResponse || fetchPromise;
      })
    );
    return;
  }

  // Default: Network First
  event.respondWith(
    fetch(event.request).catch(() => {
      return caches.match(event.request);
    })
  );
});
