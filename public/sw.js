/**
 * Pustaka Digital Sekolah - Service Worker
 * Menyediakan Caching Aset Statis, Offline Navigation, dan Penyimpanan Buku Lokal.
 */

const CACHE_VERSION = 'v1';
const STATIC_CACHE = `pustaka-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `pustaka-dynamic-${CACHE_VERSION}`;
const OFFLINE_BOOKS_CACHE = `pustaka-offline-books-${CACHE_VERSION}`;

const CORE_ASSETS = [
    '/',
    '/offline',
    '/manifest.json',
    '/css/bookshelf.css',
    '/css/reader.css',
    '/js/page-flip.browser.min.js',
    '/js/pdf.min.js',
    '/js/offline-manager.js',
    '/js/book-search.js',
    '/images/logo.png',
    '/images/logo-hitam.png',
    '/images/default-book-cover.png',
    '/images/icons/icon-72x72.png',
    '/images/icons/icon-96x96.png',
    '/images/icons/icon-128x128.png',
    '/images/icons/icon-144x144.png',
    '/images/icons/icon-152x152.png',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-384x384.png',
    '/images/icons/icon-512x512.png',
];

// 1. Install Event: Pre-cache aset pokok
self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            console.log('[ServiceWorker] Pre-caching core assets');
            return cache.addAll(CORE_ASSETS).catch((err) => {
                console.warn('[ServiceWorker] Some assets could not be pre-cached:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// 2. Activate Event: Bersihkan cache versi lama
self.addEventListener('activate', (e) => {
    const expectedCaches = [STATIC_CACHE, DYNAMIC_CACHE, OFFLINE_BOOKS_CACHE];
    e.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((k) => {
                    if (!expectedCaches.includes(k)) {
                        console.log('[ServiceWorker] Menghapus cache usang:', k);
                        return caches.delete(k);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. Message Event: Cache data buku offline dari OfflineManager
self.addEventListener('message', (e) => {
    if (e.data && e.data.type === 'CACHE_OFFLINE_BOOK') {
        const urls = e.data.urls || [];
        if (urls.length > 0) {
            caches.open(OFFLINE_BOOKS_CACHE).then((cache) => {
                urls.forEach((url) => {
                    fetch(url, { mode: 'cors' })
                        .then((res) => {
                            if (res.ok) cache.put(url, res);
                        })
                        .catch(() => {});
                });
            });
        }
    }
});

// 4. Fetch Event: Strategi Network-First dengan Fallback Offline
self.addEventListener('fetch', (e) => {
    const req = e.request;
    const url = new URL(req.url);

    // Jangan cache permintaan POST / PUT / Livewire actions atau Filament admin sync
    if (req.method !== 'GET') return;
    if (url.pathname.includes('/livewire/') || url.pathname.includes('/admin/')) return;

    // Strategi untuk Halaman HTML (Navigation)
    if (req.mode === 'navigate') {
        e.respondWith(
            fetch(req)
                .then((networkRes) => {
                    // Simpan salinan ke dynamic cache jika sukses
                    if (networkRes.ok) {
                        const copy = networkRes.clone();
                        caches.open(DYNAMIC_CACHE).then((cache) => cache.put(req, copy));
                    }
                    return networkRes;
                })
                .catch(async () => {
                    // Coba cari di Cache (baik dynamic maupun offline books)
                    const cachedMatch = await caches.match(req);
                    if (cachedMatch) return cachedMatch;

                    // Jika tidak ada di cache sama sekali, sajikan halaman fallback offline
                    const offlinePage = await caches.match('/offline');
                    if (offlinePage) return offlinePage;

                    return new Response(
                        `<!DOCTYPE html>
                        <html lang="id">
                        <head><meta charset="utf-8"><title>Mode Offline - Pustaka Digital</title></head>
                        <body style="font-family: sans-serif; text-align: center; padding: 40px; background: #f8fafc; color: #1e293b;">
                            <h2>📡 Sedang Dalam Mode Offline</h2>
                            <p>Koneksi internet tidak tersedia dan halaman ini belum tersimpan di memori perangkat.</p>
                            <a href="/" style="display: inline-block; padding: 10px 20px; background: #4f46e5; color: #fff; text-decoration: none; border-radius: 8px;">Coba Buka Beranda</a>
                        </body>
                        </html>`,
                        { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                    );
                })
        );
        return;
    }

    // Strategi untuk Aset Statis (CSS, JS, Gambar, Font)
    e.respondWith(
        caches.match(req).then((cachedRes) => {
            if (cachedRes) {
                // Return cached version dan perbarui di background jika online (Stale-While-Revalidate)
                fetch(req)
                    .then((networkRes) => {
                        if (networkRes.ok) {
                            caches.open(DYNAMIC_CACHE).then((cache) => cache.put(req, networkRes));
                        }
                    })
                    .catch(() => {});
                return cachedRes;
            }

            // Jika belum ada di cache, ambil dari network dan simpan ke cache
            return fetch(req)
                .then((networkRes) => {
                    if (networkRes.ok && (url.pathname.startsWith('/css/') || url.pathname.startsWith('/js/') || url.pathname.startsWith('/images/'))) {
                        const copy = networkRes.clone();
                        caches.open(DYNAMIC_CACHE).then((cache) => cache.put(req, copy));
                    }
                    return networkRes;
                })
                .catch(() => {
                    // Placeholder jika gambar gagal dimuat saat offline
                    if (req.destination === 'image') {
                        return caches.match('/images/default-book-cover.png');
                    }
                });
        })
    );
});
