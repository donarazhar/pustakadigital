<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pustaka Digital Sekolah - Buku Interaktif 3D & Kuis Pembelajaran</title>
    <meta name="description" content="Perpustakaan digital sekolah berbasis Buku 3D Flipbook interaktif, narasi audio, dan kuis pemahaman materi untuk siswa dan guru.">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- PWA Web App Manifest & Mobile Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Pustaka Digital">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Lora:ital,wght@0,500;0,600;1,400&display=swap" rel="stylesheet">
    
    <!-- Modern Bookshelf Styles & Offline Manager -->
    <link rel="stylesheet" href="{{ asset('css/bookshelf.css') }}?v={{ file_exists(public_path('css/bookshelf.css')) ? filemtime(public_path('css/bookshelf.css')) : time() }}">
    <script src="{{ asset('js/offline-manager.js') }}"></script>
</head>
<body>
    <!-- Offline Connectivity Notice Banner -->
    <div id="offline-network-banner" style="display: none; background: #fef3c7; border-bottom: 1px solid #fde68a; padding: 10px 20px; font-size: 0.82rem; font-weight: 700; color: #92400e; justify-content: center; align-items: center; gap: 8px;">
        <span>📡</span>
        <span>Mode Offline Aktif: Koneksi internet terputus, Anda membaca dari memori lokal.</span>
        <a href="{{ route('offline') }}" style="color: #4f46e5; text-decoration: underline; margin-left: 8px;">Buka Rak Buku Offline →</a>
    </div>

    <!-- Top Navigation Bar -->
    <nav class="site-nav">
        <a href="{{ route('home') }}" class="brand-wrapper" title="Beranda Pustaka Digital">
            <img src="{{ asset('images/logo-hitam.png') }}" alt="Logo Sekolah" class="brand-logo-img">
            <div class="brand-info">
                <span class="brand-title">Pustaka Digital Sekolah</span>
                <span class="brand-subtitle">Direktorat Dikdasmen YPI Al Azhar</span>
            </div>
        </a>

        <div class="nav-actions">
            <!-- PWA Install Button -->
            <button type="button" class="btn-pwa-install" onclick="window.OfflineManager.promptInstall()" title="Pasang aplikasi di tablet atau HP siswa" style="display: none; align-items: center; gap: 6px; background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; padding: 7px 14px; border-radius: 9999px; font-weight: 700; font-size: 0.82rem; cursor: pointer; transition: all 0.2s ease;">
                <span>📱</span>
                <span>Pasang Aplikasi</span>
            </button>
            @auth
                @if(auth()->user()->isStudent())
                    <a href="{{ url('/siswa') }}" class="btn-nav-siswa">
                        <span>🎓</span>
                        <span>Panel Siswa ({{ auth()->user()->name }})</span>
                    </a>
                @else
                    <a href="{{ url('/admin') }}" class="btn-nav-admin">
                        <span>⚙️</span>
                        <span>Panel Admin / Guru</span>
                    </a>
                @endif
            @else
                <a href="{{ url('/siswa') }}" class="btn-nav-siswa">
                    <span>🎓</span>
                    <span>Ruang Siswa</span>
                </a>
                <a href="{{ url('/admin') }}" class="btn-nav-admin">
                    <span>🔐</span>
                    <span>Panel Guru / Admin</span>
                </a>
            @endauth
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="hero-badge-pill">
            <span class="hero-badge-dot"></span>
            <span>Platform Literasi Digital Berbasis Buku 3D & Kuis Interaktif</span>
        </div>

        <h1 class="hero-heading">
            Belajar Lebih Hidup dengan <br>
            <span class="gradient-text">Buku Digital Interaktif</span>
        </h1>

        <p class="hero-description">
            Jelajahi literasi sains, matematika, bahasa, dan pengetahuan umum dengan sensasi membalik lembaran Buku 3D yang nyata, narasi suara, serta evaluasi kuis pemahaman di setiap bab materi.
        </p>

        <!-- Search Bar Spotlight -->
        <div class="hero-search-wrapper">
            <form action="{{ route('home') }}" method="GET" class="search-box-card">
                @if(request('grade'))
                    <input type="hidden" name="grade" value="{{ request('grade') }}">
                @endif
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                @if(request('program'))
                    <input type="hidden" name="program" value="{{ request('program') }}">
                @endif

                <span style="font-size: 1.15rem; color: #94a3b8;">🔍</span>
                <input 
                    type="text" 
                    name="q" 
                    value="{{ request('q') }}" 
                    placeholder="Cari judul buku, topik materi, atau nama penulis..." 
                    class="search-input"
                    autocomplete="off"
                >
                <button type="submit" class="btn-search-submit">
                    <span>Cari</span>
                    <span>Buku</span>
                </button>
            </form>

            <!-- Quick Suggestions -->
            <div class="search-tags" style="margin-top: 14px;">
                <span>Pencarian Populer:</span>
                <a href="{{ route('home', ['q' => 'Tata Surya']) }}" class="search-tag-chip">🪐 Tata Surya</a>
                <a href="{{ route('home', ['q' => 'Sains']) }}" class="search-tag-chip">🔬 Sains</a>
                <a href="{{ route('home', ['type' => 'interactive']) }}" class="search-tag-chip">📖 Buku 3D</a>
                <a href="{{ route('home', ['type' => 'pdf']) }}" class="search-tag-chip">📄 Dokumen PDF</a>
            </div>
        </div>
    </header>

    <!-- Key Metrics & Features Ribbon -->
    <section class="metrics-ribbon">
        <div class="metric-card">
            <div class="metric-icon-wrap" style="background: #eef2ff; color: #4f46e5;">
                📖
            </div>
            <div class="metric-text">
                <h4>3D Flipbook & PDF</h4>
                <p>Pengalaman membaca realistis seperti buku cetak fisik.</p>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon-wrap" style="background: #ecfdf5; color: #10b981;">
                🎧
            </div>
            <div class="metric-text">
                <h4>Narasi Audio Jernih</h4>
                <p>Mendukung gaya belajar visual & auditori siswa secara mandiri.</p>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon-wrap" style="background: #fdf4ff; color: #c026d3;">
                🎯
            </div>
            <div class="metric-text">
                <h4>Kuis Evaluasi Bab</h4>
                <p>Uji pemahaman langsung di setiap akhir bab materi.</p>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon-wrap" style="background: #fffbeb; color: #d97706;">
                📊
            </div>
            <div class="metric-text">
                <h4>Pencatatan Otomatis</h4>
                <p>Riwayat durasi baca tersimpan ke portofolio akun siswa.</p>
            </div>
        </div>
    </section>

    <!-- Main Catalog Section -->
    <section class="catalog-section" id="katalog">
        <div class="filter-bar-card">
            <div style="display: flex; flex-direction: column; width: 100%; gap: 14px;">
                <!-- Baris 1: Jenjang & Format -->
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div class="filter-group-left">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin-right: 4px; text-transform: uppercase; letter-spacing: 0.05em;">
                            Jenjang:
                        </span>
                        
                        <a href="{{ route('home', array_filter(['program' => request('program'), 'type' => request('type'), 'q' => request('q')])) }}" 
                           class="filter-pill {{ !request('grade') ? 'active' : '' }}">
                            Semua Jenjang
                        </a>

                        @foreach($grades as $grade)
                            <a href="{{ route('home', array_filter(['grade' => $grade->id, 'program' => request('program'), 'type' => request('type'), 'q' => request('q')])) }}" 
                               class="filter-pill {{ request('grade') == $grade->id ? 'active' : '' }}">
                                {{ $grade->name }}
                            </a>
                        @endforeach
                    </div>

                    <div class="filter-group-right">
                        <div class="format-segmented-control">
                            <a href="{{ route('home', array_filter(['grade' => request('grade'), 'program' => request('program'), 'q' => request('q')])) }}" 
                               class="format-btn {{ !request('type') ? 'active' : '' }}">
                                Semua Format
                            </a>
                            <a href="{{ route('home', array_filter(['grade' => request('grade'), 'program' => request('program'), 'type' => 'interactive', 'q' => request('q')])) }}" 
                               class="format-btn {{ request('type') === 'interactive' ? 'active' : '' }}">
                                📖 Buku 3D
                            </a>
                            <a href="{{ route('home', array_filter(['grade' => request('grade'), 'program' => request('program'), 'type' => 'pdf', 'q' => request('q')])) }}" 
                               class="format-btn {{ request('type') === 'pdf' ? 'active' : '' }}">
                                📄 E-Book PDF
                            </a>
                        </div>

                        @if(request('q') || request('grade') || request('program') || request('type'))
                            <a href="{{ route('home') }}" class="filter-pill" style="color: #ef4444; border-color: #fecaca; background: #fef2f2;" title="Hapus Semua Filter">
                                ✕ Reset Filter
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Baris 2: Program Sekolah (Bilingual, Tahfizh, Reguler, STEM) -->
                @if(isset($programs) && $programs->isNotEmpty())
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding-top: 10px; border-top: 1px dashed #e2e8f0;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin-right: 4px; text-transform: uppercase; letter-spacing: 0.05em;">
                            Program:
                        </span>

                        <a href="{{ route('home', array_filter(['grade' => request('grade'), 'type' => request('type'), 'q' => request('q')])) }}" 
                           class="filter-pill {{ !request('program') ? 'active' : '' }}">
                            ✨ Semua Program
                        </a>

                        @foreach($programs as $prog)
                            @php
                                $isProgActive = request('program') == $prog->id;
                                $progActiveColor = $prog->color ?: '#2563eb';
                            @endphp
                            <a href="{{ route('home', array_filter(['program' => $prog->id, 'grade' => request('grade'), 'type' => request('type'), 'q' => request('q')])) }}" 
                               class="filter-pill {{ $isProgActive ? 'active' : '' }}"
                               {!! $isProgActive ? 'style="background: ' . e($progActiveColor) . '; color: #ffffff; border-color: ' . e($progActiveColor) . ';"' : 'style="border-color: #e2e8f0;"' !!}>
                                <span>{{ $prog->icon ?: '🎓' }}</span> {{ $prog->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Books Grid -->
        <main class="books-grid">
            @forelse($books as $book)
                <article class="book-card" data-book-id="{{ $book->id }}">
                    <!-- Cover Image Container -->
                    <div class="book-cover-container">
                        @if($book->cover_image)
                            <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="book-cover-img" loading="lazy">
                        @else
                            <div class="book-placeholder">
                                <span style="font-size: 3.2rem;">📖</span>
                                <span style="font-size: 0.85rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;">
                                    {{ $book->subject ? $book->subject->name : 'Pustaka Digital' }}
                                </span>
                            </div>
                        @endif

                        <!-- Floating Badges on Cover -->
                        <div class="cover-badges-top">
                            @if($book->isPdf())
                                <span class="badge-format pdf">📄 PDF E-Book</span>
                            @else
                                <span class="badge-format interactive">📖 3D Flipbook</span>
                            @endif

                            @if($book->grade)
                                <span class="badge-grade">{{ $book->grade->name }}</span>
                            @endif

                            @if($book->program)
                                @php
                                    $coverProgColor = $book->program->color ?: '#3b82f6';
                                @endphp
                                <span class="badge-grade" {!! 'style="background: ' . e($coverProgColor) . '; color: #ffffff; font-weight: 700;"' !!}>
                                    {{ $book->program->icon }} {{ $book->program->name }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Book Card Body -->
                    <div class="book-card-body">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 6px;">
                            @if($book->subject)
                                <div class="book-subject-tag">{{ $book->subject->name }}</div>
                            @else
                                <div class="book-subject-tag" style="color: #64748b;">Umum</div>
                            @endif

                            @if($book->program)
                                @php
                                    $bodyProgColor = $book->program->color ?: '#2563eb';
                                @endphp
                                <span {!! 'style="font-size: 0.75rem; font-weight: 600; color: ' . e($bodyProgColor) . '; background: ' . e($bodyProgColor) . '18; padding: 2px 8px; border-radius: 9999px;"' !!}>
                                    {{ $book->program->icon }} {{ $book->program->name }}
                                </span>
                            @endif
                        </div>


                        <h2 class="book-title" title="{{ $book->title }}">{{ $book->title }}</h2>

                        <p class="book-author">
                            Oleh: <strong>{{ $book->author ?: 'Tim Literasi Sekolah' }}</strong>
                            @if($book->publication_year) &bull; {{ $book->publication_year }} @endif
                        </p>

                        <div class="book-synopsis">
                            {{ strip_tags($book->description) ?: 'Buku materi pembelajaran digital interaktif yang dirancang untuk mendukung proses belajar mengajar secara mandiri dan komprehensif.' }}
                        </div>

                        <!-- Metadata Row -->
                        <div class="book-meta-row">
                            <span class="meta-item">
                                @if($book->isPdf())
                                    <span>📄 {{ $book->total_pages ? "{$book->total_pages} Hal" : 'Dokumen PDF' }}</span>
                                @else
                                    <span>📑 {{ $book->chapters->count() }} Bab Materi</span>
                                @endif
                            </span>

                            <span class="meta-item">
                                <span>⏱️ ~{{ $book->estimated_read_time ?: 15 }} menit</span>
                            </span>
                        </div>

                        <!-- Card Footer Action Buttons -->
                        <div class="book-card-footer">
                            <button 
                                type="button" 
                                class="btn-detail btn-open-synopsis" 
                                data-title="{{ $book->title }}"
                                data-author="{{ $book->author ?: 'Tim Literasi Sekolah' }}"
                                data-publisher="{{ $book->publisher ?: 'Pustaka Digital Sekolah' }}"
                                data-year="{{ $book->publication_year ?: '-' }}"
                                data-grade="{{ $book->grade ? $book->grade->name : 'Semua Jenjang' }}"
                                data-subject="{{ $book->subject ? $book->subject->name : 'Umum' }}"
                                data-format="{{ $book->isPdf() ? 'E-Book PDF' : 'Buku Interaktif 3D' }}"
                                data-pages="{{ $book->isPdf() ? ($book->total_pages . ' Halaman') : ($book->chapters->count() . ' Bab') }}"
                                data-duration="{{ $book->estimated_read_time ?: 15 }} Menit"
                                data-description="{{ strip_tags($book->description) }}"
                                data-read-url="{{ route('siswa.books.read', $book->slug) }}"
                            >
                                ℹ️ Sinopsis
                            </button>

                            <a href="{{ route('siswa.books.read', $book->slug) }}" class="btn-start-reading" title="Mulai membaca buku ini">
                                <span>Mulai Baca</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty-catalog-state">
                    <div class="empty-icon">🔍</div>
                    <h3>Tidak Menemukan Buku yang Sesuai</h3>
                    <p>Coba gunakan kata kunci pencarian lain atau pilih filter jenjang yang berbeda.</p>
                    <a href="{{ route('home') }}" class="filter-pill active" style="display: inline-flex;">
                        Lihat Semua Koleksi Buku
                    </a>
                </div>
            @endforelse
        </main>
    </section>

    <!-- Features Showcase Section -->
    <section class="features-section">
        <div class="features-container">
            <div class="section-header">
                <span class="section-tag">Keunggulan Sistem</span>
                <h2 class="section-title">Ekosistem Literasi Sekolah Berbasis Masa Depan</h2>
                <p class="section-desc">
                    Mengkombinasikan konten kurikulum standar dengan teknologi pembaca buku modern untuk meningkatkan minat dan efektivitas literasi siswa.
                </p>
            </div>

            <div class="features-grid">
                <div class="feature-item-card">
                    <div class="feature-item-icon" style="background: #eef2ff; color: #4f46e5;">
                        📖
                    </div>
                    <h3>Buku 3D Realistis (3D Flipbook)</h3>
                    <p>
                        Setiap buku interaktif dan dokumen PDF dapat dibaca dengan sensasi membalik halaman fisik, efek lengkungan kertas (*page-curl*), bayangan 3D, dan suara lembaran kertas yang alami.
                    </p>
                </div>

                <div class="feature-item-card">
                    <div class="feature-item-icon" style="background: #ecfdf5; color: #10b981;">
                        🎧
                    </div>
                    <h3>Audio Narasi Pembelajaran</h3>
                    <p>
                        Dilengkapi dengan pemutar audio narasi per bab yang memungkinkan siswa menyimak pelafalan kata, penjelasan materi, dan intonasi yang tepat saat membaca.
                    </p>
                </div>

                <div class="feature-item-card">
                    <div class="feature-item-icon" style="background: #fffbeb; color: #d97706;">
                        🎯
                    </div>
                    <h3>Evaluasi Kuis & Portofolio Siswa</h3>
                    <p>
                        Siswa dapat mengerjakan kuis pemahaman di setiap bab materi, melihat skor evaluasi seketika, serta memantau durasi waktu membaca di panel pribadi siswa.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Synopsis Modal -->
    <div id="synopsis-modal" class="modal-backdrop" onclick="if(event.target === this) closeSynopsisModal();">
        <div class="modal-container" role="dialog" aria-modal="true">
            <div class="modal-header">
                <div>
                    <span id="modal-format-badge" class="badge-format interactive" style="margin-bottom: 8px; display: inline-flex;">📖 3D Flipbook</span>
                    <h3 id="modal-title" style="font-size: 1.25rem; font-weight: 800; color: #0f172a; line-height: 1.3;">Judul Buku</h3>
                    <p id="modal-author" style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">Penulis</p>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeSynopsisModal()" title="Tutup">✕</button>
            </div>

            <div class="modal-body">
                <!-- Info Pills -->
                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px;">
                    <span id="modal-grade" class="filter-pill" style="font-size: 0.78rem;">Jenjang</span>
                    <span id="modal-subject" class="filter-pill" style="font-size: 0.78rem;">Mata Pelajaran</span>
                    <span id="modal-pages" class="filter-pill" style="font-size: 0.78rem;">Halaman</span>
                    <span id="modal-duration" class="filter-pill" style="font-size: 0.78rem;">Estimasi Baca</span>
                </div>

                <h4 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Sinopsis & Gambaran Materi</h4>
                <div id="modal-desc" style="font-size: 0.92rem; color: #334155; line-height: 1.7; background: #f8fafc; padding: 18px 20px; border-radius: 12px; border: 1px solid #e2e8f0; max-height: 250px; overflow-y: auto;">
                    Deskripsi buku lengkap...
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-detail" onclick="closeSynopsisModal()">Tutup</button>
                <a id="modal-read-btn" href="#" class="btn-start-reading">
                    <span>Mulai Membaca Sekarang</span>
                    <span>→</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <img src="{{ asset('images/logo-hitam.png') }}" alt="Logo" style="height: 38px; width: auto; object-fit: contain;">
                <div>
                    <strong style="display: block; font-size: 1rem; color: #0f172a;">Pustaka Digital Sekolah</strong>
                    <span style="font-size: 0.78rem; color: #64748b;">Inovasi Buku Digital & Multimedia Interaktif</span>
                </div>
            </div>

            <nav class="footer-nav">
                <a href="{{ route('home') }}" class="footer-link">Beranda</a>
                <a href="#katalog" class="footer-link">Katalog Buku</a>
                <a href="{{ url('/siswa') }}" class="footer-link">Ruang Siswa</a>
                <a href="{{ url('/admin') }}" class="footer-link">Panel Guru & Admin</a>
            </nav>
        </div>

        <div class="footer-copyright">
            <p>Sistem Informasi Pustaka Digital Sekolah &copy; {{ date('Y') }}. Dibuat oleh Dirat TITD YPI Al Azhar.</p>
        </div>
    </footer>

    <!-- Vanilla Modal Script -->
    <script>
        function openSynopsisModal(data) {
            document.getElementById('modal-title').textContent = data.title;
            document.getElementById('modal-author').textContent = 'Oleh: ' + data.author + ' • Penerbit: ' + data.publisher + ' (' + data.year + ')';
            document.getElementById('modal-grade').textContent = '🎓 ' + data.grade;
            document.getElementById('modal-subject').textContent = '📚 ' + data.subject;
            document.getElementById('modal-pages').textContent = '📑 ' + data.pagesOrChapters;
            document.getElementById('modal-duration').textContent = '⏱️ ~' + data.duration;
            document.getElementById('modal-desc').textContent = data.description || 'Tidak ada deskripsi tambahan.';
            document.getElementById('modal-read-btn').href = data.readUrl;

            const formatBadge = document.getElementById('modal-format-badge');
            formatBadge.textContent = data.format;
            if (data.format.includes('PDF')) {
                formatBadge.className = 'badge-format pdf';
            } else {
                formatBadge.className = 'badge-format interactive';
            }

            document.getElementById('synopsis-modal').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeSynopsisModal() {
            document.getElementById('synopsis-modal').classList.remove('open');
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-open-synopsis');
            if (!btn) return;
            openSynopsisModal({
                title: btn.dataset.title,
                author: btn.dataset.author,
                publisher: btn.dataset.publisher,
                year: btn.dataset.year,
                grade: btn.dataset.grade,
                subject: btn.dataset.subject,
                format: btn.dataset.format,
                pagesOrChapters: btn.dataset.pages,
                duration: btn.dataset.duration,
                description: btn.dataset.description,
                readUrl: btn.dataset.readUrl
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSynopsisModal();
            }
        });
    </script>
</body>
</html>
