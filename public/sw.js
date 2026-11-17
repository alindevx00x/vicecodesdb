/**
 * ViceCodes Lightweight Offline Service Worker
 * Keeps cheat directory, printable sheet, CSS, and JS available offline next to the TV.
 */
const CACHE_NAME = 'vicecodes-v2.1';
const BASE_URL = new URL('./', self.location.href).pathname.replace(/\/$/, '');

const PRECACHE_URLS = [
  `${BASE_URL}/`,
  `${BASE_URL}/cheats`,
  `${BASE_URL}/cheats/printable`,
  `${BASE_URL}/assets/css/style.css?v=2.1`,
  `${BASE_URL}/assets/js/app.js?v=2.1`,
  `${BASE_URL}/assets/img/favicon.svg`
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS).catch(() => {}))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;
  if (url.pathname.includes('/admin') || url.pathname.includes('/search') || url.pathname.includes('/newsletter')) {
    return;
  }

  // Network-first for HTML pages, Cache-first for static assets
  if (req.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      fetch(req)
        .then((res) => {
          if (res.ok) {
            const clone = res.clone();
            caches.open(CACHE_NAME).then((c) => c.put(req, clone));
          }
          return res;
        })
        .catch(() => caches.match(req).then((r) => r || caches.match(`${BASE_URL}/cheats/printable`) || caches.match(`${BASE_URL}/cheats`)))
    );
    return;
  }

  event.respondWith(
    caches.match(req).then((cached) => {
      if (cached) return cached;
      return fetch(req).then((res) => {
        if (res.ok && (url.pathname.includes('/assets/') || url.pathname.includes('/og/'))) {
          const clone = res.clone();
          caches.open(CACHE_NAME).then((c) => c.put(req, clone));
        }
        return res;
      });
    })
  );
});
