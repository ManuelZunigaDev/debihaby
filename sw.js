const CACHE_NAME = 'debihaby-v1';
const ASSETS = [
    'dashboard.php',
    'css/styles.css',
    'css/dashboard.css',
    'assets/logo.png'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => response || fetch(event.request))
    );
});
