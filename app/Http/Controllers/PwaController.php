<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PwaController extends Controller
{
    /**
     * Generate dynamic Web App Manifest
     */
    public function manifest(Request $request)
    {
        $siteTitle = get_option('site_title', config('app.name', 'Smart Banking'));
        $shortName = mb_substr($siteTitle, 0, 12);
        
        $manifest = [
            'name' => $siteTitle,
            'short_name' => $shortName,
            'description' => $siteTitle . ' - Financial Management Portal',
            'start_url' => url('/'),
            'scope' => url('/') . '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#ffffff',
            'theme_color' => '#1e3a8a',
            'icons' => [
                [
                    'src' => asset('public/images/icons/icon-72x72.png'),
                    'sizes' => '72x72',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('public/images/icons/icon-96x96.png'),
                    'sizes' => '96x96',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('public/images/icons/icon-128x128.png'),
                    'sizes' => '128x128',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('public/images/icons/icon-144x144.png'),
                    'sizes' => '144x144',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('public/images/icons/icon-152x152.png'),
                    'sizes' => '152x152',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('public/images/icons/icon-192x192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('public/images/icons/icon-384x384.png'),
                    'sizes' => '384x384',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('public/images/icons/icon-512x512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('public/images/icons/maskable-icon-512x512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json; charset=utf-8',
        ]);
    }

    /**
     * Generate dynamic Service Worker
     */
    public function serviceWorker(Request $request)
    {
        $offlineUrl = asset('public/offline.html');
        $baseUrl = url('/');
        $manifestUrl = route('pwa.manifest');
        $icon192 = asset('public/images/icons/icon-192x192.png');
        $icon512 = asset('public/images/icons/icon-512x512.png');
        $appleIcon = asset('public/images/icons/apple-touch-icon.png');

        $js = <<<JS
const CACHE_NAME = 'creditlite-pwa-v2.0';
const OFFLINE_URL = '{$offlineUrl}';
const BASE_URL = '{$baseUrl}';

const STATIC_ASSETS = [
  OFFLINE_URL,
  '{$manifestUrl}',
  '{$icon192}',
  '{$icon512}',
  '{$appleIcon}'
];

// Install Event
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

// Activate Event
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

  // Static Assets (Images, Fonts, CSS, JS)
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
JS;

        return response($js, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Service-Worker-Allowed' => '/',
        ]);
    }
}
