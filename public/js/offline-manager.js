/**
 * Pustaka Digital Sekolah - Offline Storage & PWA Manager
 * Menggunakan IndexedDB untuk menyimpan buku dan Service Worker untuk caching aset.
 */

(function () {
    const DB_NAME = 'PustakaOfflineDB';
    const DB_VERSION = 1;
    const STORE_BOOKS = 'offline_books';

    class OfflineManager {
        constructor() {
            this.db = null;
            this.deferredPrompt = null;
            this.isOnline = navigator.onLine;
            this.init();
        }

        async init() {
            try {
                this.db = await this.openDatabase();
                this.setupNetworkListeners();
                this.setupPwaInstall();
                this.updateOfflineBadges();
            } catch (err) {
                console.warn('Gagal menginisialisasi IndexedDB Offline:', err);
            }
        }

        openDatabase() {
            return new Promise((resolve, reject) => {
                if (!window.indexedDB) {
                    reject('Browser tidak mendukung IndexedDB');
                    return;
                }

                const request = indexedDB.open(DB_NAME, DB_VERSION);

                request.onupgradeneeded = (e) => {
                    const db = e.target.result;
                    if (!db.objectStoreNames.contains(STORE_BOOKS)) {
                        const store = db.createObjectStore(STORE_BOOKS, { keyPath: 'id' });
                        store.createIndex('slug', 'slug', { unique: true });
                        store.createIndex('title', 'title', { unique: false });
                        store.createIndex('saved_at', 'saved_at', { unique: false });
                    }
                };

                request.onsuccess = (e) => resolve(e.target.result);
                request.onerror = (e) => reject(e.target.error);
            });
        }

        setupNetworkListeners() {
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.showNetworkToast('🌐 Koneksi internet kembali terhubung!', 'success');
                const banner = document.getElementById('offline-network-banner');
                if (banner) banner.style.display = 'none';
            });

            window.addEventListener('offline', () => {
                this.isOnline = false;
                this.showNetworkToast('📡 Mode Offline Aktif: Anda membaca dari memori lokal tablet/HP.', 'warning');
                const banner = document.getElementById('offline-network-banner');
                if (banner) banner.style.display = 'flex';
            });

            // Set initial banner if offline
            if (!navigator.onLine) {
                const banner = document.getElementById('offline-network-banner');
                if (banner) banner.style.display = 'flex';
            }
        }

        setupPwaInstall() {
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.deferredPrompt = e;

                // Tampilkan semua tombol / banner instalasi PWA
                document.querySelectorAll('.btn-pwa-install, #pwa-install-banner').forEach((el) => {
                    el.style.display = 'inline-flex';
                    if (el.tagName === 'DIV' || el.classList.contains('banner-block')) {
                        el.style.display = 'flex';
                    }
                });
            });

            window.addEventListener('appinstalled', () => {
                this.deferredPrompt = null;
                this.showNetworkToast('🎉 Aplikasi Pustaka Digital berhasil terpasang di perangkat Anda!', 'success');
                document.querySelectorAll('.btn-pwa-install, #pwa-install-banner').forEach((el) => {
                    el.style.display = 'none';
                });
            });
        }

        async promptInstall() {
            if (!this.deferredPrompt) {
                // Jika sudah dalam mode standalone atau browser tidak mendukung prompt langsung
                if (window.matchMedia('(display-mode: standalone)').matches) {
                    alert('Aplikasi Pustaka Digital sudah terpasang di perangkat Anda.');
                } else {
                    alert('Untuk memasang di tablet/HP:\n• Di Chrome/Edge: Ketuk ikon titik tiga (⋮) lalu pilih "Instal Aplikasi" atau "Tambahkan ke Layar Utama".\n• Di Safari (iOS): Ketuk tombol Share lalu pilih "Add to Home Screen".');
                }
                return;
            }

            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                this.deferredPrompt = null;
            }
        }

        /**
         * Simpan buku lengkap ke IndexedDB dan kirim cache request ke Service Worker.
         */
        async saveBook(bookData) {
            if (!this.db) {
                this.db = await this.openDatabase();
            }

            return new Promise((resolve, reject) => {
                const tx = this.db.transaction([STORE_BOOKS], 'readwrite');
                const store = tx.objectStore(STORE_BOOKS);

                bookData.saved_at = new Date().toISOString();
                const req = store.put(bookData);

                req.onsuccess = () => {
                    // Beri tahu Service Worker untuk mem-prefetch aset gambar/halaman
                    if (navigator.serviceWorker && navigator.serviceWorker.controller) {
                        const urlsToCache = [];
                        if (bookData.cover_url) urlsToCache.push(bookData.cover_url);
                        if (bookData.read_url) urlsToCache.push(bookData.read_url);

                        if (bookData.chapters) {
                            bookData.chapters.forEach((chap) => {
                                if (chap.pages) {
                                    chap.pages.forEach((p) => {
                                        if (p.featured_image_url) urlsToCache.push(p.featured_image_url);
                                    });
                                }
                            });
                        }

                        navigator.serviceWorker.controller.postMessage({
                            type: 'CACHE_OFFLINE_BOOK',
                            bookId: bookData.id,
                            urls: urlsToCache,
                        });
                    }

                    this.showNetworkToast(`✅ Buku "${bookData.title}" berhasil disimpan untuk dibaca offline!`, 'success');
                    this.updateOfflineButtonState(bookData.id, true);
                    resolve(true);
                };

                req.onerror = (e) => {
                    this.showNetworkToast('Gagal menyimpan buku ke memori lokal.', 'error');
                    reject(e.target.error);
                };
            });
        }

        async isBookSaved(bookId) {
            if (!this.db) {
                try {
                    this.db = await this.openDatabase();
                } catch (e) {
                    return false;
                }
            }

            return new Promise((resolve) => {
                const tx = this.db.transaction([STORE_BOOKS], 'readonly');
                const store = tx.objectStore(STORE_BOOKS);
                const req = store.get(Number(bookId));

                req.onsuccess = (e) => resolve(!!e.target.result);
                req.onerror = () => resolve(false);
            });
        }

        async removeBook(bookId) {
            if (!this.db) {
                this.db = await this.openDatabase();
            }

            return new Promise((resolve, reject) => {
                const tx = this.db.transaction([STORE_BOOKS], 'readwrite');
                const store = tx.objectStore(STORE_BOOKS);
                const req = store.delete(Number(bookId));

                req.onsuccess = () => {
                    this.showNetworkToast('Buku telah dihapus dari memori lokal.', 'info');
                    this.updateOfflineButtonState(bookId, false);
                    resolve(true);
                };
                req.onerror = (e) => reject(e.target.error);
            });
        }

        async getAllBooks() {
            if (!this.db) {
                this.db = await this.openDatabase();
            }

            return new Promise((resolve, reject) => {
                const tx = this.db.transaction([STORE_BOOKS], 'readonly');
                const store = tx.objectStore(STORE_BOOKS);
                const req = store.getAll();

                req.onsuccess = (e) => resolve(e.target.result || []);
                req.onerror = (e) => reject(e.target.error);
            });
        }

        async updateOfflineBadges() {
            const saveBtns = document.querySelectorAll('[data-offline-book-id]');
            for (const btn of saveBtns) {
                const bId = btn.dataset.offlineBookId;
                const isSaved = await this.isBookSaved(bId);
                this.updateOfflineButtonState(bId, isSaved);
            }
        }

        updateOfflineButtonState(bookId, isSaved) {
            document.querySelectorAll(`[data-offline-book-id="${bookId}"]`).forEach((btn) => {
                if (isSaved) {
                    btn.classList.add('is-saved');
                    btn.innerHTML = '<span>✅</span><span>Tersimpan Offline</span>';
                    btn.title = 'Buku ini tersimpan di memori perangkat. Klik untuk hapus.';
                } else {
                    btn.classList.remove('is-saved');
                    btn.innerHTML = '<span>📥</span><span>Simpan Offline</span>';
                    btn.title = 'Simpan buku ini ke tablet/HP agar bisa dibaca tanpa internet.';
                }
            });
        }

        showNetworkToast(msg, type = 'info') {
            let toast = document.getElementById('offline-status-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'offline-status-toast';
                document.body.appendChild(toast);
            }

            const bgColors = {
                success: '#10b981',
                warning: '#f59e0b',
                error: '#ef4444',
                info: '#4f46e5',
            };

            toast.style.cssText = `
                position: fixed;
                bottom: 24px;
                left: 50%;
                transform: translateX(-50%);
                background: ${bgColors[type] || '#1e293b'};
                color: #ffffff;
                padding: 10px 20px;
                border-radius: 9999px;
                font-size: 0.84rem;
                font-weight: 700;
                box-shadow: 0 10px 25px rgba(0,0,0,0.25);
                z-index: 99999999;
                display: flex;
                align-items: center;
                gap: 8px;
                animation: popIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            `;
            toast.innerText = msg;

            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => {
                if (toast) toast.remove();
            }, 4500);
        }
    }

    window.OfflineManager = new OfflineManager();

    // Registrasi Service Worker PWA
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker
                .register('/sw.js')
                .then((reg) => {
                    console.log('Pustaka Digital PWA ServiceWorker aktif:', reg.scope);
                })
                .catch((err) => {
                    console.warn('Gagal mendaftarkan ServiceWorker:', err);
                });
        });
    }
})();
