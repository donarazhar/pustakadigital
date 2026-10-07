<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mode Offline - Pustaka Digital Sekolah</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Lora:ital,wght@0,500;0,600;1,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/bookshelf.css') }}">
    <script src="{{ asset('js/offline-manager.js') }}"></script>

    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .offline-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .offline-badge {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .offline-hero {
            max-width: 800px;
            margin: 40px auto 20px;
            text-align: center;
            padding: 0 20px;
        }

        .offline-icon {
            font-size: 64px;
            margin-bottom: 16px;
            display: inline-block;
            animation: pulseWave 2s infinite ease-in-out;
        }

        @keyframes pulseWave {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }

        .offline-hero h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 10px;
        }

        .offline-hero p {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
            max-width: 620px;
            margin: 0 auto 24px;
        }

        .offline-books-section {
            max-width: 1000px;
            margin: 0 auto;
            width: 100%;
            padding: 0 20px 60px;
            flex: 1;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
        }

        .section-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .offline-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .offline-book-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.05);
            display: flex;
            flex-direction: column;
            transition: all 0.2s ease;
        }

        .offline-book-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -3px rgba(15, 23, 42, 0.1);
            border-color: #cbd5e1;
        }

        .offline-card-cover {
            width: 100%;
            height: 160px;
            object-fit: cover;
            background: #e2e8f0;
        }

        .offline-card-body {
            padding: 14px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .offline-card-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
            line-height: 1.4;
        }

        .offline-card-author {
            font-size: 0.78rem;
            color: #64748b;
            margin-bottom: 12px;
        }

        .btn-read-offline {
            display: block;
            text-align: center;
            background: #4f46e5;
            color: #ffffff;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.15s ease;
        }

        .btn-read-offline:hover {
            background: #4338ca;
        }

        .empty-offline-state {
            background: #ffffff;
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 40px 20px;
            text-align: center;
            color: #64748b;
        }

        .btn-retry {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-retry:hover {
            background: #e2e8f0;
        }
    </style>
</head>
<body>
    <header class="offline-header">
        <div style="display: flex; align-items: center; gap: 12px;">
            <img src="{{ asset('images/logo-hitam.png') }}" alt="Logo" style="height: 32px; width: auto; object-fit: contain;">
            <span style="font-weight: 800; font-size: 1rem; color: #0f172a;">Pustaka Digital Sekolah</span>
        </div>
        <div class="offline-badge">
            <span>📡</span>
            <span>Mode Tanpa Internet (Offline)</span>
        </div>
    </header>

    <div class="offline-hero">
        <span class="offline-icon">📡</span>
        <h1>Koneksi Internet Terputus</h1>
        <p>
            Anda tetap dapat belajar dan membaca buku yang telah disimpan ke memori lokal tablet atau HP Anda. Saat internet kembali terhubung, halaman akan otomatis tersinkronisasi.
        </p>
        <button class="btn-retry" onclick="window.location.reload()">
            🔄 Periksa Ulang Koneksi
        </button>
    </div>

    <section class="offline-books-section">
        <div class="section-header">
            <h2 class="section-title">
                <span>💾</span>
                <span>Buku Tersimpan di Memori Lokal</span>
            </h2>
            <span id="saved-books-count" style="font-size: 0.82rem; font-weight: 700; color: #64748b;">Memuat...</span>
        </div>

        <div id="offline-books-container" class="offline-grid">
            <!-- Diisi secara dinamis oleh JavaScript IndexedDB -->
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const container = document.getElementById('offline-books-container');
            const countLabel = document.getElementById('saved-books-count');

            if (!window.OfflineManager) {
                container.innerHTML = '<div class="empty-offline-state">Modul offline tidak didukung browser ini.</div>';
                return;
            }

            try {
                const books = await window.OfflineManager.getAllBooks();
                if (!books || books.length === 0) {
                    countLabel.innerText = '0 Buku Tersimpan';
                    container.innerHTML = `
                        <div class="empty-offline-state" style="grid-column: 1 / -1;">
                            <div style="font-size: 40px; margin-bottom: 12px;">📥</div>
                            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Belum Ada Buku Tersimpan Offline</h3>
                            <p style="font-size: 0.85rem; max-width: 480px; margin: 0 auto;">
                                Saat Anda sedang online, buka buku dan ketuk tombol <strong>"📥 Simpan Offline"</strong> agar materi tersimpan ke tablet/HP dan bisa dibaca kapan saja tanpa kuota internet.
                            </p>
                        </div>
                    `;
                } else {
                    countLabel.innerText = `${books.length} Buku Siap Dibaca`;
                    container.innerHTML = books.map(b => `
                        <div class="offline-book-card">
                            <img src="${b.cover_url || '/images/default-book-cover.png'}" alt="${b.title}" class="offline-card-cover" onerror="this.src='/images/default-book-cover.png'">
                            <div class="offline-card-body">
                                <div>
                                    <h4 class="offline-card-title">${b.title}</h4>
                                    <div class="offline-card-author">Oleh: ${b.author || 'Pendidik'}</div>
                                </div>
                                <div style="display: flex; gap: 6px; margin-top: 10px;">
                                    <a href="${b.read_url || ('/baca/' + b.slug)}" class="btn-read-offline" style="flex: 1;">
                                        📖 Buka & Baca
                                    </a>
                                    <button onclick="deleteOfflineBook(${b.id})" title="Hapus dari memori lokal" style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 8px; padding: 0 10px; cursor: pointer;">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('');
                }
            } catch(e) {
                container.innerHTML = '<div class="empty-offline-state">Gagal membaca data memori offline.</div>';
            }
        });

        async function deleteOfflineBook(bookId) {
            if (confirm('Hapus buku ini dari memori lokal perangkat?')) {
                await window.OfflineManager.removeBook(bookId);
                window.location.reload();
            }
        }

        // Jika koneksi kembali online, otomatis redirect ke beranda
        window.addEventListener('online', () => {
            alert('Koneksi internet Anda telah kembali online! Mengarahkan ke halaman utama...');
            window.location.href = '/';
        });
    </script>
</body>
</html>
