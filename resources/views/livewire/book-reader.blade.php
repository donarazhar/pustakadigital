<div>
    @php
        // Kumpulkan semua halaman dari semua bab secara berurutan
        $allPagesFlat = collect();
        $chapterStartIndices = []; // [chapter_id => flipbook_page_index]
        $pageIndices = []; // [page_id => flipbook_page_index]

        // Indeks 0: Cover Depan
        // Indeks 1: Halaman Judul & Info Penerbit
        // Indeks 2: Daftar Isi Interaktif
        $currentIndexTracker = 3;

        foreach ($book->chapters as $chap) {
            $chapterStartIndices[$chap->id] = $currentIndexTracker;
            foreach ($chap->pages as $p) {
                $pageIndices[$p->id] = $currentIndexTracker;
                $allPagesFlat->push([
                    'page' => $p,
                    'chapter' => $chap,
                    'flipIndex' => $currentIndexTracker,
                ]);
                $currentIndexTracker++;
            }
            if ($chap->quiz && $chap->quiz->is_active) {
                // Halaman Kuis Interaktif Bab
                $allPagesFlat->push([
                    'is_quiz_page' => true,
                    'chapter' => $chap,
                    'quiz' => $chap->quiz,
                    'flipIndex' => $currentIndexTracker,
                ]);
                $currentIndexTracker++;
            }
        }

        // Halaman Catatan / Refleksi
        $currentIndexTracker++;

        // Pastikan total halaman genap agar cover belakang tertutup sempurna di sebelah kiri
        // Total sekarang = $currentIndexTracker + 1 (untuk cover belakang)
        $totalExpected = $currentIndexTracker + 1;
        $needsFillerPage = ($totalExpected % 2 !== 0);
    @endphp

    <!-- Sticky Top Header -->
    <header class="reader-header">
        <div class="reader-header-left">
            <a href="{{ route('home') }}" class="btn-icon" title="Kembali ke Rak Buku">
                ←
            </a>
            <button wire:click="toggleSidebar" class="btn-icon" title="Buka Daftar Isi">
                ☰
            </button>
            <img src="{{ asset('images/logo-hitam.png') }}" alt="Logo Sekolah" style="height: 30px; width: auto; object-fit: contain;">
            <div>
                <h1 class="reader-header-title">{{ $book->title }}</h1>
                <div class="book-badge-meta">
                    @if($book->grade)
                        <span class="badge badge-primary">{{ $book->grade->name }}</span>
                    @endif
                    @if($book->subject)
                        <span class="badge badge-success">{{ $book->subject->name }}</span>
                    @endif
                    @if($this->activeAssignment)
                        <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a;" title="{{ $this->activeAssignment->title }}">
                            📌 Tugas: {{ \Illuminate\Support\Str::limit($this->activeAssignment->title, 24) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <!-- Mode Switcher: 3D Flipbook vs Focus Mode -->
            <div style="background: #f1f5f9; padding: 4px; border-radius: 10px; display: flex; gap: 4px; border: 1px solid var(--border-color);">
                <button 
                    wire:click="switchMode('flipbook')" 
                    class="btn-icon btn-mode-toggle {{ $viewMode === 'flipbook' ? 'active' : '' }}" 
                    title="Sensasi Membalik Lembaran Buku Fisik 3D"
                >
                    📖 Buku 3D
                </button>
                <button 
                    wire:click="switchMode('focus')" 
                    class="btn-icon btn-mode-toggle {{ $viewMode === 'focus' ? 'active' : '' }}" 
                    title="Mode Baca Lembar Tunggal / Fokus"
                >
                    📄 Baca Fokus
                </button>
            </div>

            <!-- Stabilo & Catatan Toggle Button -->
            <button wire:click="toggleNotesDrawer" class="btn-icon btn-catatan-toggle {{ $this->annotations->count() > 0 ? 'has-notes' : '' }}" title="Buka Catatan & Stabilo Saya">
                <span>📝</span>
                <span>Catatan ({{ $this->annotations->count() }})</span>
            </button>

            @if($currentChapter && $currentChapter->quiz && $currentChapter->quiz->is_active)
                <button wire:click="{{ $showQuiz ? 'closeQuiz' : 'openQuiz' }}" class="btn-icon" style="width: auto; padding: 0 14px; gap: 6px; font-size: 0.82rem;" title="Kuis Pemahaman Bab">
                    <span>💡</span>
                    <span>{{ $showQuiz ? 'Kembali ke Buku' : 'Kuis Bab' }}</span>
                </button>
            @endif

            @auth
                @if(auth()->user()->isStudent())
                    <a href="{{ url('/siswa') }}" class="btn-icon" style="width: auto; padding: 0 12px; font-size: 0.82rem; background: #ecfdf5; border-color: #a7f3d0; color: #065f46;" title="Kembali ke Panel Siswa">
                        🎓 Panel Siswa
                    </a>
                @else
                    <a href="{{ url('/admin') }}" class="btn-icon" style="width: auto; padding: 0 12px; font-size: 0.82rem;" title="Panel Guru / Admin">
                        ⚙️ Admin
                    </a>
                @endif
            @else
                <a href="{{ url('/siswa/login') }}" class="btn-icon" style="width: auto; padding: 0 12px; font-size: 0.82rem;" title="Masuk ke Akun Siswa">
                    🔑 Masuk
                </a>
            @endauth
        </div>
    </header>

    <!-- Reading Container -->
    <div class="reader-container {{ $viewMode === 'flipbook' ? 'flipbook-layout' : '' }}">
        <!-- Sidebar Navigation Drawer -->
        <aside class="reader-sidebar {{ $isSidebarOpen ? '' : 'collapsed' }}" style="z-index: 60;">
            <div class="sidebar-header">
                <span>Daftar Isi & Navigasi</span>
                <button wire:click="toggleSidebar" class="btn-icon" style="width: 28px; height: 28px;">✕</button>
            </div>
            <div class="sidebar-chapters-list">
                @if($viewMode === 'flipbook')
                    <!-- Quick Jump Links in 3D Mode -->
                    <div style="padding: 0 0 10px 0; border-bottom: 1px solid #f1f5f9; margin-bottom: 10px;">
                        <button onclick="window.flipToPage(0);" class="chapter-nav-button" style="color: var(--primary); font-weight: 700;">
                            <span>📕 Sampul Depan</span>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Hal 1</span>
                        </button>
                        <button onclick="window.flipToPage(2);" class="chapter-nav-button" style="color: var(--text-secondary);">
                            <span>📑 Daftar Isi Lengkap</span>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Hal 3</span>
                        </button>
                    </div>
                @endif

                @foreach($book->chapters as $chap)
                    <div class="chapter-nav-item {{ $currentChapter && $currentChapter->id === $chap->id ? 'active' : '' }}">
                        @if($viewMode === 'flipbook')
                            <button data-target-page="{{ $chapterStartIndices[$chap->id] ?? 0 }}" onclick="window.flipToPage(Number(this.dataset.targetPage));" class="chapter-nav-button">
                                <span>{{ $chap->title }}</span>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $chap->pages->count() }} Hal</span>
                            </button>
                        @else
                            <button wire:click="selectChapter({{ $chap->id }})" class="chapter-nav-button">
                                <span>{{ $chap->title }}</span>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $chap->pages->count() }} Hal</span>
                            </button>
                        @endif

                        <div class="page-sublist">
                            @foreach($chap->pages as $p)
                                @if($viewMode === 'flipbook')
                                    <button data-target-page="{{ $pageIndices[$p->id] ?? 0 }}" onclick="window.flipToPage(Number(this.dataset.targetPage));" class="page-nav-pill">
                                        Hal {{ $p->page_number }}: {{ $p->title ?: 'Halaman ' . $p->page_number }}
                                    </button>
                                @else
                                    <button wire:click="selectPage({{ $p->id }})" class="page-nav-pill {{ $currentPage && $currentPage->id === $p->id && !$showQuiz ? 'active' : '' }}">
                                        Hal {{ $p->page_number }}: {{ $p->title ?: 'Halaman ' . $p->page_number }}
                                    </button>
                                @endif
                            @endforeach

                            @if($chap->quiz && $chap->quiz->is_active)
                                <button wire:click="openQuizForChapter({{ $chap->id }})" class="page-nav-pill {{ $showQuiz && $currentChapter && $currentChapter->id === $chap->id ? 'active' : '' }}" style="color: #7c3aed; font-weight: 700;">
                                    ⭐ Kuis Bab: {{ $chap->title }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if($viewMode === 'flipbook')
                    <div style="padding: 10px 0 0 0; border-top: 1px solid #f1f5f9; margin-top: 10px;">
                        <button onclick="window.flipToPage(window.totalPagesCount - 1);" class="chapter-nav-button" style="color: var(--text-secondary);">
                            <span>📘 Sampul Belakang</span>
                        </button>
                    </div>
                @endif
            </div>
        </aside>

        <!-- Main Stage Area -->
        <main class="reader-main" style="width: 100%;">
            @if($showQuiz && $currentChapter && $currentChapter->quiz)
                <!-- Interactive Quiz View -->
                <div class="quiz-wrapper" style="width: 100%; max-width: 860px; margin: 0 auto;">
                    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                        <button wire:click="closeQuiz" class="btn-icon" style="width: auto; padding: 0 16px; gap: 8px;">
                            <span>←</span>
                            <span>Kembali ke Membaca Buku</span>
                        </button>
                        <span class="badge badge-primary">Evaluasi Pemahaman {{ $currentChapter->title }}</span>
                    </div>

                    <livewire:interactive-quiz :quiz="$currentChapter->quiz" :key="'quiz-' . $currentChapter->quiz->id" />
                </div>

            @elseif($viewMode === 'flipbook')
                <!-- ============================================================= -->
                <!-- 3D FLIPBOOK VIEWER MODE (StPageFlip Canvas & Realistic DOM)  -->
                <!-- ============================================================= -->
                <div wire:ignore class="flipbook-theater" id="flipbook-theater">
                    <!-- Floating Navigation Arrows -->
                    <button class="flip-nav-arrow flip-nav-left" id="btn-flip-prev" title="Halaman Sebelumnya (Panah Kiri)">
                        ‹
                    </button>
                    <button class="flip-nav-arrow flip-nav-right" id="btn-flip-next" title="Halaman Selanjutnya (Panah Kanan)">
                        ›
                    </button>

                    <!-- Outer Flipbook Canvas Container -->
                    <div class="flipbook-container-outer">
                        <div id="flipbook" class="flip-book">
                            <!-- ============================================== -->
                            <!-- PAGE 0: HARDCOVER SAMPUL DEPAN                 -->
                            <!-- ============================================== -->
                            <div class="flip-page page-hard page-cover-front" data-density="hard">
                                <div class="cover-front-inner">
                                    <div>
                                        <div style="display: flex; gap: 8px; margin-bottom: 20px;">
                                            @if($book->grade)
                                                <span class="badge" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.3);">
                                                    {{ $book->grade->name }}
                                                </span>
                                            @endif
                                            @if($book->subject)
                                                <span class="badge" style="background: #10b981; color: #fff;">
                                                    {{ $book->subject->name }}
                                                </span>
                                            @endif
                                        </div>

                                        <h1 style="font-family: 'Cinzel', serif; font-size: 2.1rem; font-weight: 800; color: #fbbf24; text-shadow: 0 2px 10px rgba(0,0,0,0.5); line-height: 1.25; margin-bottom: 14px;">
                                            {{ $book->title }}
                                        </h1>

                                        @if($book->cover_image)
                                            <div style="margin: 20px auto; max-height: 220px; overflow: hidden; border-radius: 8px; box-shadow: 0 10px 20px rgba(0,0,0,0.4); border: 2px solid rgba(251, 191, 36, 0.4);">
                                                <img src="{{ asset('storage/' . $book->cover_image) }}" alt="Cover" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                        @else
                                            <div style="background: rgba(255,255,255,0.06); border: 1px dashed rgba(255,255,255,0.25); border-radius: 12px; padding: 36px 20px; text-align: center; margin: 24px 0;">
                                                <div style="font-size: 4rem; margin-bottom: 8px;">🌌</div>
                                                <div style="font-size: 0.85rem; letter-spacing: 0.1em; color: #e2e8f0; text-transform: uppercase;">Edisi Interaktif Sekolah</div>
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <div style="font-size: 0.95rem; color: #cbd5e1; margin-bottom: 6px;">
                                            Penulis: <strong style="color: #fff;">{{ $book->author ?: 'Pustaka Literasi Sekolah' }}</strong>
                                        </div>
                                        <div style="font-size: 0.8rem; color: #94a3b8;">
                                            {{ $book->publisher ?: 'Pustaka Digital Edukasi' }} &bull; {{ $book->publication_year ?: date('Y') }}
                                        </div>
                                        <div style="margin-top: 18px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: space-between;">
                                            <span style="font-size: 0.8rem; color: #fef08a; display: flex; align-items: center; gap: 6px;">
                                                <span>👉</span> Geser pojok buku atau klik tanda panah
                                            </span>
                                            <span style="font-size: 1.2rem;">📖</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- PAGE 1: SAMPUL DALAM / HALAMAN PRAKATA         -->
                            <!-- ============================================== -->
                            <div class="flip-page page-hard" data-density="hard" style="background: #fafaf9; color: #1c1917;">
                                <div class="page-layout" style="border-right: 1px solid #e7e5e4;">
                                    <div class="page-header-strip">
                                        <span>Pustaka Digital Sekolah</span>
                                        <span>Halaman Judul</span>
                                    </div>

                                    <div class="page-body-scrollable">
                                        <div style="text-align: center; margin-top: 20px; margin-bottom: 30px;">
                                            <div style="font-size: 2.2rem; margin-bottom: 8px;">🏛️</div>
                                            <h2 style="font-family: 'Cinzel', serif; font-size: 1.5rem; color: #0f172a; margin-bottom: 6px;">
                                                {{ $book->title }}
                                            </h2>
                                            <p style="font-size: 0.85rem; color: #64748b;">
                                                Buku Digital Interaktif & Evaluasi Belajar Siswa
                                            </p>
                                        </div>

                                        <div style="background: #f1f5f9; border-radius: 8px; padding: 18px; font-size: 0.85rem; color: #334155; line-height: 1.6; margin-bottom: 24px;">
                                            <div style="font-weight: 700; margin-bottom: 8px; color: #0f172a;">Informasi Penerbitan:</div>
                                            <div><strong>Penulis:</strong> {{ $book->author ?: 'Tim Pengembang Literasi' }}</div>
                                            <div><strong>Penerbit:</strong> {{ $book->publisher ?: 'Pustaka Digital Edukasi Indonesia' }}</div>
                                            <div><strong>Tahun Terbit:</strong> {{ $book->publication_year ?: date('Y') }}</div>
                                            @if($book->isbn)
                                                <div><strong>ISBN:</strong> {{ $book->isbn }}</div>
                                            @endif
                                            <div><strong>Jenjang:</strong> {{ $book->grade ? $book->grade->name : 'Umum' }}</div>
                                        </div>

                                        <div style="font-family: 'Lora', serif; font-size: 0.92rem; color: #475569; line-height: 1.7; font-style: italic;">
                                            "Buku ini dirancang khusus untuk memicu rasa ingin tahu, daya kritis, dan kecintaan membaca siswa melalui perpaduan teks interaktif, narasi audio, dan tantangan kuis pemahaman di setiap akhir bab."
                                        </div>
                                    </div>

                                    <div class="page-footer-strip">
                                        <span>Hak Cipta Dilindungi</span>
                                        <span>Hal i</span>
                                    </div>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- PAGE 2: DAFTAR ISI INTERAKTIF                  -->
                            <!-- ============================================== -->
                            <div class="flip-page page-soft" data-density="soft">
                                <div class="page-layout">
                                    <div class="page-header-strip">
                                        <span>{{ $book->title }}</span>
                                        <span>Daftar Isi</span>
                                    </div>

                                    <div class="page-body-scrollable">
                                        <h2 style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
                                            📑 Daftar Isi Buku
                                        </h2>

                                        <div style="display: flex; flex-direction: column; gap: 14px;">
                                            @foreach($book->chapters as $chap)
                                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px;">
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <a href="javascript:void(0)" data-target-page="{{ $chapterStartIndices[$chap->id] ?? 3 }}" onclick="window.flipToPage(Number(this.dataset.targetPage))" style="font-weight: 700; color: #1e293b; text-decoration: none; font-size: 0.95rem;">
                                                            {{ $chap->title }}
                                                        </a>
                                                        <span style="font-weight: 700; color: var(--primary); font-size: 0.85rem;">
                                                            Hal {{ $chapterStartIndices[$chap->id] ?? 3 }}
                                                        </span>
                                                    </div>

                                                    @if($chap->summary)
                                                        <p style="font-size: 0.8rem; color: #64748b; margin-top: 4px; line-height: 1.4;">
                                                            {{ $chap->summary }}
                                                        </p>
                                                    @endif

                                                    <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px;">
                                                        @foreach($chap->pages as $p)
                                                            <a href="javascript:void(0)" data-target-page="{{ $pageIndices[$p->id] ?? 3 }}" onclick="window.flipToPage(Number(this.dataset.targetPage))" style="font-size: 0.75rem; background: #e0e7ff; color: #3730a3; padding: 2px 8px; border-radius: 4px; text-decoration: none; font-weight: 600;">
                                                                Hal {{ $p->page_number }}
                                                            </a>
                                                        @endforeach
                                                        @if($chap->quiz && $chap->quiz->is_active)
                                                            <span style="font-size: 0.75rem; background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 4px; font-weight: 600;">
                                                                ⭐ Kuis Bab
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="page-footer-strip">
                                        <span>Daftar Isi</span>
                                        <span>Hal ii</span>
                                    </div>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- LEMBARAN-LEMBARAN ISI BUKU (BERURUTAN)        -->
                            <!-- ============================================== -->
                            @foreach($allPagesFlat as $item)
                                @if(isset($item['is_quiz_page']))
                                    <!-- Halaman Evaluasi & Peluncur Kuis Bab -->
                                    <div class="flip-page page-soft" data-density="soft">
                                        <div class="page-layout" style="background: linear-gradient(180deg, #fdfcf9 0%, #f5f3ff 100%);">
                                            <div class="page-header-strip">
                                                <span>{{ $item['chapter']->title }}</span>
                                                <span>Uji Pemahaman</span>
                                            </div>

                                            <div class="page-body-scrollable" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 20px 10px;">
                                                <div style="font-size: 3.5rem; margin-bottom: 16px;">🏆</div>
                                                <h2 style="font-size: 1.45rem; font-weight: 800; color: #1e1b4b; margin-bottom: 12px;">
                                                    {{ $item['quiz']->title }}
                                                </h2>
                                                <p style="font-family: 'Lora', serif; font-size: 0.95rem; color: #475569; line-height: 1.6; max-width: 380px; margin-bottom: 24px;">
                                                    {{ $item['quiz']->description ?: 'Kamu telah menyelesaikan materi bacaan pada bab ini! Uji pemahamanmu dengan kuis interaktif seru berikut.' }}
                                                </p>

                                                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 24px; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); width: 100%; max-width: 320px;">
                                                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.85rem; color: #64748b;">
                                                        <span>Jumlah Pertanyaan:</span>
                                                        <strong style="color: #0f172a;">{{ $item['quiz']->questions->count() }} Soal</strong>
                                                    </div>
                                                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #64748b;">
                                                        <span>Nilai Kelulusan:</span>
                                                        <strong style="color: #10b981;">Min. {{ $item['quiz']->passing_score }}%</strong>
                                                    </div>
                                                </div>

                                                <button wire:click="openQuizForChapter({{ $item['chapter']->id }})" class="btn-quiz-cta" style="padding: 14px 28px; font-size: 1rem; border-radius: 9999px; box-shadow: 0 10px 25px rgba(124, 58, 237, 0.35); cursor: pointer;">
                                                    <span>Mulai Kuis Sekarang 🚀</span>
                                                </button>
                                            </div>

                                            <div class="page-footer-strip">
                                                <span>{{ $item['chapter']->title }}</span>
                                                <span>Kuis Evaluasi</span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <!-- Halaman Teks Materi Standar -->
                                    @php
                                        $pg = $item['page'];
                                        $chap = $item['chapter'];
                                    @endphp
                                    <div class="flip-page page-soft" data-density="soft" id="page-dom-{{ $pg->id }}">
                                        <div class="page-layout">
                                            <div class="page-header-strip">
                                                <span>{{ $chap->title }}</span>
                                                <span>Bab {{ $chap->chapter_number }} &bull; Hal {{ $pg->page_number }}</span>
                                            </div>

                                            <div class="page-body-scrollable">
                                                <h2 class="page-title">{{ $pg->title ?: 'Halaman ' . $pg->page_number }}</h2>

                                                <!-- Audio Player Narasi (Berkas atau Text-To-Speech) -->
                                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                                                    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.82rem; font-weight: 600; color: #475569;">
                                                        <span>🔊</span>
                                                        <span>Narator Suara:</span>
                                                    </div>

                                                    @if($pg->audio_narration_url)
                                                        <audio controls src="{{ $pg->audio_narration_url }}" style="height: 32px; max-width: 200px;">
                                                            Browser tidak mendukung audio.
                                                        </audio>
                                                    @else
                                                        <button 
                                                            onclick="window.speakText(this, 'page-dom-{{ $pg->id }}')" 
                                                            class="dock-btn" 
                                                            style="color: var(--primary); background: #e0e7ff; padding: 4px 10px; font-size: 0.75rem;"
                                                            title="Dengarkan pembacaan teks otomatis (Bahasa Indonesia)"
                                                        >
                                                            <span>▶ Bacakan Teks (TTS)</span>
                                                        </button>
                                                    @endif
                                                </div>

                                                <!-- Gambar Ilustrasi Jika Ada -->
                                                @if($pg->featured_image)
                                                    <div style="margin-bottom: 14px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.08);">
                                                        <img src="{{ asset('storage/' . $pg->featured_image) }}" alt="{{ $pg->title }}" style="width: 100%; height: auto; display: block;">
                                                    </div>
                                                @endif

                                                <!-- Konten Teks Buku Kaya Format -->
                                                <div class="page-text-content" data-page-id="{{ $pg->id }}" data-chapter-id="{{ $chap->id }}">
                                                    {!! $pg->content !!}
                                                </div>

                                                <!-- Video Embed Jika Ada -->
                                                @if($pg->video_embed_url)
                                                    <div style="margin-top: 14px; border-radius: 8px; overflow: hidden; aspect-ratio: 16/9;">
                                                        @php
                                                            $embedUrl = $pg->video_embed_url;
                                                            if (str_contains($embedUrl, 'watch?v=')) {
                                                                $embedUrl = str_replace('watch?v=', 'embed/', $embedUrl);
                                                            }
                                                        @endphp
                                                        <iframe src="{{ $embedUrl }}" style="width: 100%; height: 100%; border: none;" allowfullscreen></iframe>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="page-footer-strip">
                                                <span>{{ $book->title }}</span>
                                                <span>Hal {{ $pg->page_number }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            <!-- ============================================== -->
                            <!-- HALAMAN REFLEKSI & CATATAN MEMBACA             -->
                            <!-- ============================================== -->
                            <div class="flip-page page-soft" data-density="soft">
                                <div class="page-layout" style="background: #fafaf9;">
                                    <div class="page-header-strip">
                                        <span>Catatan Literasi Siswa</span>
                                        <span>Refleksi</span>
                                    </div>

                                    <div class="page-body-scrollable">
                                        <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-bottom: 12px;">
                                            🌟 Catatan & Refleksi Membaca
                                        </h2>
                                        <p style="font-family: 'Lora', serif; font-size: 0.95rem; color: #475569; line-height: 1.6; margin-bottom: 18px;">
                                            Selamat! Kamu telah membaca materi buku ini hingga tuntas. Gunakan ruang ini untuk mengingat kembali konsep utama yang paling berkesan bagimu.
                                        </p>

                                        <div style="background: #ffffff; border: 1.5px dashed #cbd5e1; border-radius: 10px; padding: 18px; margin-bottom: 20px;">
                                            <div style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                                ✍️ Hal menarik yang baru aku ketahui hari ini:
                                            </div>
                                            <div style="border-bottom: 1px dotted #94a3b8; height: 26px;"></div>
                                            <div style="border-bottom: 1px dotted #94a3b8; height: 26px;"></div>
                                            <div style="border-bottom: 1px dotted #94a3b8; height: 26px;"></div>
                                        </div>

                                        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px; font-size: 0.82rem; color: #065f46; display: flex; align-items: center; gap: 8px;">
                                            <span>✅</span>
                                            <span>Progress membaca buku otomatis tersimpan di akun belajarmu!</span>
                                        </div>
                                    </div>

                                    <div class="page-footer-strip">
                                        <span>Pustaka Digital Edukasi</span>
                                        <span>Refleksi</span>
                                    </div>
                                </div>
                            </div>

                            @if($needsFillerPage)
                                <!-- Filler Page to guarantee Even Spreads -->
                                <div class="flip-page page-soft" data-density="soft">
                                    <div class="page-layout" style="background: #fafaf9; display: flex; align-items: center; justify-content: center; text-align: center;">
                                        <div class="page-header-strip" style="width: 100%;">
                                            <span>Catatan</span>
                                            <span>Pustaka Sekolah</span>
                                        </div>
                                        <div class="page-body-scrollable" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                            <div style="font-size: 3rem; margin-bottom: 12px;">🌱</div>
                                            <div style="font-family: 'Lora', serif; font-style: italic; color: #64748b; font-size: 0.95rem; max-width: 320px; line-height: 1.6;">
                                                "Membaca adalah jendela dunia, dan rasa ingin tahu adalah kuncinya."
                                            </div>
                                        </div>
                                        <div class="page-footer-strip" style="width: 100%;">
                                            <span>Pustaka Digital</span>
                                            <span>Penutup</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- ============================================== -->
                            <!-- LAST PAGE: HARDCOVER BELAKANG BUKU             -->
                            <!-- ============================================== -->
                            <div class="flip-page page-hard page-cover-back" data-density="hard">
                                <div class="cover-back-inner">
                                    <div>
                                        <div style="font-family: 'Cinzel', serif; font-size: 1.3rem; font-weight: 800; color: #fbbf24; margin-bottom: 12px; border-bottom: 1px solid rgba(251, 191, 36, 0.3); padding-bottom: 8px;">
                                            Sinopsis Buku
                                        </div>
                                        <p style="font-family: 'Lora', serif; font-size: 0.92rem; color: #cbd5e1; line-height: 1.7; margin-bottom: 16px;">
                                            {{ $book->description ?: 'Buku digital interaktif sekolah yang memadukan materi terstruktur, narasi pendukung, ilustrasi menarik, dan evaluasi kuis untuk mendukung ekosistem Kurikulum Merdeka.' }}
                                        </p>
                                    </div>

                                    <div>
                                        <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 14px; text-align: center; margin-bottom: 16px;">
                                            <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.05em;">
                                                Identitas Terbitan
                                            </div>
                                            <div style="font-family: monospace; font-size: 0.95rem; color: #f8fafc; font-weight: 700;">
                                                {{ $book->isbn ?: 'ISBN 978-602-0000-00-0' }}
                                            </div>
                                        </div>

                                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.15); padding-top: 14px;">
                                            <a href="{{ route('home') }}" style="color: #60a5fa; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                                                <span>←</span> Rak Buku Lainnya
                                            </a>
                                            <button onclick="window.flipToPage(0)" class="dock-btn" style="background: rgba(255,255,255,0.1); font-size: 0.8rem; color: #fff;">
                                                ↺ Buka Ulang
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- FLOATING BOTTOM CONTROL DOCK                   -->
                    <!-- ============================================== -->
                    <div class="flip-control-dock">
                        <button wire:click="toggleSidebar" class="dock-btn" title="Buka Daftar Isi">
                            <span>☰</span>
                            <span>Daftar Isi</span>
                        </button>

                        <div class="dock-divider"></div>

                        <!-- Scrubber Slider -->
                        <div class="dock-slider-wrap">
                            <input type="range" min="0" max="10" value="0" id="flip-scrubber" class="dock-slider" title="Geser Halaman Cepat">
                            <span class="dock-page-badge" id="flip-page-indicator">Sampul</span>
                        </div>

                        <div class="dock-divider"></div>

                        <!-- Sound FX Toggle -->
                        <button id="btn-sound-toggle" onclick="window.togglePaperSound(this)" class="dock-btn active" title="Suara Lembaran Kertas Realistis">
                            <span id="sound-icon">🔔</span>
                            <span>Suara</span>
                        </button>

                        <!-- Audio TTS Narrator Toggle -->
                        <button id="btn-tts-toggle" onclick="window.speakActiveSpread(this)" class="dock-btn" title="Bacakan Teks Halaman Ini Secara Otomatis">
                            <span id="tts-icon">🔊</span>
                            <span>Dengarkan</span>
                        </button>

                        <div class="dock-divider"></div>

                        <!-- Fullscreen Toggle -->
                        <button onclick="window.toggleFullscreen()" class="dock-btn" title="Layar Penuh (F)">
                            <span>⛶</span>
                            <span>Layar Penuh</span>
                        </button>
                    </div>
                </div>

                <!-- 3D Flipbook Script Initialization -->
                <script>
                    (function() {
                        let isInitialized = false;

                        function initStPageFlip() {
                            if (isInitialized && window.pageFlipInstance) {
                                try {
                                    window.pageFlipInstance.update();
                                } catch(e) {}
                                return;
                            }

                            const bookEl = document.getElementById('flipbook');
                            if (!bookEl) return;
                            if (bookEl.classList.contains('stf__parent')) return;

                            // Check if St library exists
                            if (typeof St === 'undefined' || typeof St.PageFlip === 'undefined') {
                                console.warn('StPageFlip belum termuat, mencoba lagi...');
                                setTimeout(initStPageFlip, 200);
                                return;
                            }

                            const pages = bookEl.querySelectorAll('.flip-page');
                            if (!pages || pages.length === 0) {
                                console.warn('Halaman flip-page belum tersedia di DOM.');
                                return;
                            }

                            try {
                                const isMobile = window.innerWidth < 768;
                                const availableHeight = window.innerHeight - 170;
                                const baseHeight = isMobile ? Math.min(window.innerHeight - 170, 440) : Math.min(Math.max(availableHeight, 430), 485);
                                const baseWidth = Math.round(baseHeight * 0.72);

                                const pageFlip = new St.PageFlip(bookEl, {
                                    width: baseWidth,
                                    height: baseHeight,
                                    size: 'stretch',
                                    minWidth: 260,
                                    maxWidth: 450,
                                    minHeight: 340,
                                    maxHeight: 640,
                                    maxShadowOpacity: 0.5,
                                    showCover: true,
                                    mobileScrollSupport: false,
                                    useMouseEvents: true,
                                    flippingTime: 650,
                                    drawShadow: true
                                });

                                pageFlip.loadFromHTML(pages);
                                window.pageFlipInstance = pageFlip;
                                window.totalPagesCount = pages.length;
                                isInitialized = true;

                                // Setup Scrubber Range Slider
                                const scrubber = document.getElementById('flip-scrubber');
                                const pageIndicator = document.getElementById('flip-page-indicator');

                                if (scrubber) {
                                    scrubber.max = pages.length - 1;
                                    scrubber.value = 0;
                                    scrubber.addEventListener('input', function () {
                                        const targetIdx = parseInt(this.value, 10);
                                        pageFlip.turnToPage(targetIdx);
                                    });
                                }

                                // Prev / Next button listeners
                                const prevBtn = document.getElementById('btn-flip-prev');
                                const nextBtn = document.getElementById('btn-flip-next');

                                if (prevBtn) {
                                    prevBtn.onclick = (e) => {
                                        e.preventDefault();
                                        pageFlip.flipPrev();
                                    };
                                }
                                if (nextBtn) {
                                    nextBtn.onclick = (e) => {
                                        e.preventDefault();
                                        pageFlip.flipNext();
                                    };
                                }

                                // On Flip Event: Play paper sound & update indicator
                                pageFlip.on('flip', (e) => {
                                    const pageIdx = e.data;
                                    if (window.playPaperSound) {
                                        window.playPaperSound();
                                    }

                                    if (scrubber) {
                                        scrubber.value = pageIdx;
                                    }

                                    if (pageIndicator) {
                                        if (pageIdx === 0) {
                                            pageIndicator.innerText = 'Sampul Depan';
                                        } else if (pageIdx === pages.length - 1) {
                                            pageIndicator.innerText = 'Sampul Belakang';
                                        } else {
                                            pageIndicator.innerText = `Hal ${pageIdx + 1} / ${pages.length}`;
                                        }
                                    }

                                    // Update reading progress safely via Livewire
                                    try {
                                        if (window.Livewire && Livewire.all().length > 0) {
                                            const comp = Livewire.all()[0];
                                            comp.call('recordFlipbookProgress', pageIdx + 1, pages.length);
                                        }
                                    } catch(err) {
                                        // Ignore progress error if Livewire is busy
                                    }
                                });

                                // Global jump function for Table of Contents & buttons
                                window.flipToPage = function (targetIdx) {
                                    if (window.pageFlipInstance) {
                                        const clamped = Math.max(0, Math.min(targetIdx, pages.length - 1));
                                        window.pageFlipInstance.turnToPage(clamped);
                                    }
                                };

                                // Keyboard Navigation
                                window.onkeydown = function (e) {
                                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                                    if (e.key === 'ArrowLeft') {
                                        pageFlip.flipPrev();
                                    } else if (e.key === 'ArrowRight') {
                                        pageFlip.flipNext();
                                    } else if (e.key === 'f' || e.key === 'F') {
                                        window.toggleFullscreen();
                                    } else if (e.key === 'm' || e.key === 'M') {
                                        const btn = document.getElementById('btn-sound-toggle');
                                        if (btn) window.togglePaperSound(btn);
                                    }
                                };
                            } catch(err) {
                                console.error('Gagal inisialisasi StPageFlip:', err);
                            }
                        }

                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', initStPageFlip);
                        } else {
                            setTimeout(initStPageFlip, 100);
                        }
                        window.addEventListener('load', () => setTimeout(initStPageFlip, 100));
                        document.addEventListener('livewire:navigated', () => setTimeout(initStPageFlip, 100));
                    })();

                    // Procedural Paper Turning Sound with Web Audio API
                    window.isPaperSoundEnabled = true;
                    window.playPaperSound = function () {
                        if (!window.isPaperSoundEnabled) return;
                        try {
                            const AudioCtx = window.AudioContext || window.webkitAudioContext;
                            if (!AudioCtx) return;
                            const ctx = new AudioCtx();
                            const bufferSize = ctx.sampleRate * 0.16; // 160ms sound
                            const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
                            const data = buffer.getChannelData(0);
                            for (let i = 0; i < bufferSize; i++) {
                                const decay = Math.pow(1 - i / bufferSize, 2.5);
                                data[i] = (Math.random() * 2 - 1) * decay * 0.3;
                            }
                            const noise = ctx.createBufferSource();
                            noise.buffer = buffer;

                            const filter = ctx.createBiquadFilter();
                            filter.type = 'bandpass';
                            filter.frequency.setValueAtTime(1200, ctx.currentTime);
                            filter.frequency.exponentialRampToValueAtTime(320, ctx.currentTime + 0.14);
                            filter.Q.value = 1.0;

                            const gain = ctx.createGain();
                            gain.gain.setValueAtTime(0.5, ctx.currentTime);
                            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.16);

                            noise.connect(filter);
                            filter.connect(gain);
                            gain.connect(ctx.destination);
                            noise.start();
                        } catch (e) {
                            // Audio context silent ignore
                        }
                    };

                    window.togglePaperSound = function (btn) {
                        window.isPaperSoundEnabled = !window.isPaperSoundEnabled;
                        const icon = document.getElementById('sound-icon');
                        if (window.isPaperSoundEnabled) {
                            btn.classList.add('active');
                            icon.innerText = '🔔';
                        } else {
                            btn.classList.remove('active');
                            icon.innerText = '🔕';
                        }
                    };

                    // Fullscreen Toggle
                    window.toggleFullscreen = function () {
                        if (!document.fullscreenElement) {
                            document.documentElement.requestFullscreen().catch(() => {});
                        } else {
                            if (document.exitFullscreen) {
                                document.exitFullscreen().catch(() => {});
                            }
                        }
                    };

                    // Text-To-Speech (TTS) Narrator
                    let ttsSpeaking = false;
                    window.speakActiveSpread = function (btn) {
                        if (!('speechSynthesis' in window)) {
                            alert('Browser Anda belum mendukung Text-to-Speech.');
                            return;
                        }

                        if (ttsSpeaking) {
                            window.speechSynthesis.cancel();
                            ttsSpeaking = false;
                            btn.classList.remove('active');
                            document.getElementById('tts-icon').innerText = '🔊';
                            return;
                        }

                        // Collect text from visible pages
                        const pageFlip = window.pageFlipInstance;
                        const currentIdx = pageFlip ? pageFlip.getCurrentPageIndex() : 0;
                        const pages = document.querySelectorAll('.flip-page');
                        const activePageEl = pages[currentIdx];

                        if (!activePageEl) return;
                        const textContent = activePageEl.querySelector('.page-text-content');
                        const textToRead = textContent ? textContent.innerText : activePageEl.innerText;

                        if (!textToRead.trim()) return;

                        window.speechSynthesis.cancel();
                        const utterance = new SpeechSynthesisUtterance(textToRead);
                        utterance.lang = 'id-ID';
                        utterance.rate = 0.95;

                        utterance.onstart = () => {
                            ttsSpeaking = true;
                            btn.classList.add('active');
                            document.getElementById('tts-icon').innerText = '⏹';
                        };

                        utterance.onend = utterance.onerror = () => {
                            ttsSpeaking = false;
                            btn.classList.remove('active');
                            document.getElementById('tts-icon').innerText = '🔊';
                        };

                        window.speechSynthesis.speak(utterance);
                    };

                    window.speakText = function (btn, domId) {
                        if (!('speechSynthesis' in window)) {
                            alert('Browser Anda belum mendukung Text-to-Speech.');
                            return;
                        }
                        const el = document.getElementById(domId);
                        if (!el) return;
                        const textContent = el.querySelector('.page-text-content');
                        const text = textContent ? textContent.innerText : el.innerText;

                        window.speechSynthesis.cancel();
                        const utterance = new SpeechSynthesisUtterance(text);
                        utterance.lang = 'id-ID';
                        utterance.rate = 0.95;
                        window.speechSynthesis.speak(utterance);
                    };
                </script>

            @else
                <!-- ============================================================= -->
                <!-- FOCUS MODE (Single Sheet Classic Reading Layout)              -->
                <!-- ============================================================= -->
                @if($currentPage)
                    <article class="book-sheet">
                        <div class="sheet-header">
                            <div>
                                <div class="sheet-chapter-title">{{ $currentChapter->title }}</div>
                                <h2 class="sheet-page-title">{{ $currentPage->title ?: 'Halaman ' . $currentPage->page_number }}</h2>
                            </div>
                            <div class="page-indicator-pill">
                                Hal {{ $currentPage->page_number }} / {{ $totalPagesInChapter }}
                            </div>
                        </div>

                        <!-- Audio Narration Bar -->
                        <div class="audio-narration-bar">
                            <div class="audio-label">
                                <span class="sound-wave-icon">🔊</span>
                                <span>Dengarkan Narasi:</span>
                            </div>
                            @if($currentPage->audio_narration_url)
                                <audio controls src="{{ $currentPage->audio_narration_url }}">
                                    Browser Anda tidak mendukung audio player.
                                </audio>
                            @else
                                <button 
                                    onclick="window.speakText(this, 'focus-content-area')" 
                                    class="dock-btn" 
                                    style="color: var(--primary); background: #e0e7ff; padding: 6px 14px;"
                                >
                                    <span>▶ Bacakan Teks (TTS)</span>
                                </button>
                            @endif
                        </div>

                        <!-- Featured Illustration Image -->
                        @if($currentPage->featured_image)
                            <img src="{{ asset('storage/' . $currentPage->featured_image) }}" alt="{{ $currentPage->title }}" class="sheet-featured-image">
                        @endif

                        <!-- Rich Content -->
                        <div class="sheet-content page-text-content" id="focus-content-area" data-page-id="{{ $currentPage->id }}" data-chapter-id="{{ $currentChapter->id }}">
                            {!! $currentPage->content !!}
                        </div>

                        <!-- Video Embed -->
                        @if($currentPage->video_embed_url)
                            <div class="sheet-video-wrapper">
                                @php
                                    $embedUrl = $currentPage->video_embed_url;
                                    if (str_contains($embedUrl, 'watch?v=')) {
                                        $embedUrl = str_replace('watch?v=', 'embed/', $embedUrl);
                                    }
                                @endphp
                                <iframe src="{{ $embedUrl }}" allowfullscreen></iframe>
                            </div>
                        @endif
                    </article>

                    <!-- Navigation Controls -->
                    <div class="reader-bottom-nav">
                        <button wire:click="previousPage" class="btn-nav-page" {{ $currentPageIndex === 0 ? 'disabled' : '' }}>
                            ← Halaman Sebelumnya
                        </button>

                        <div style="font-weight: 600; color: var(--text-secondary); font-size: 0.9rem;">
                            Bab {{ $currentChapter->chapter_number }} &bull; Hal {{ $currentPageIndex + 1 }} dari {{ $totalPagesInChapter }}
                        </div>

                        @if($currentPageIndex < $totalPagesInChapter - 1)
                            <button wire:click="nextPage" class="btn-nav-page">
                                Halaman Selanjutnya →
                            </button>
                        @elseif($currentChapter->quiz && $currentChapter->quiz->is_active)
                            <button wire:click="openQuiz" class="btn-quiz-cta">
                                <span>Selesaikan Kuis Bab! 🚀</span>
                            </button>
                        @else
                            <button wire:click="nextChapter" class="btn-nav-page">
                                Bab Selanjutnya →
                            </button>
                        @endif
                    </div>
                @else
                    <div class="book-sheet" style="justify-content: center; align-items: center; text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 16px;">📖</div>
                        <h2>Belum ada halaman pada buku ini</h2>
                        <p style="color: var(--text-secondary); margin-top: 8px;">Silakan tambahkan halaman interaktif melalui Panel Admin.</p>
                    </div>
                @endif
            @endif
        </main>
    </div>

    <!-- Slide-over Notes Drawer -->
    @if($isNotesDrawerOpen)
        <div class="notes-drawer-overlay" wire:click="toggleNotesDrawer"></div>
    @endif
    <aside class="notes-drawer {{ $isNotesDrawerOpen ? 'open' : '' }}" id="notes-drawer">
        <div class="notes-drawer-header">
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0;">📝 Catatan & Stabilo Saya</h3>
                <span style="font-size: 0.78rem; color: #64748b;">{{ $this->annotations->count() }} catatan tersimpan</span>
            </div>
            <button wire:click="toggleNotesDrawer" class="btn-icon" style="width: 32px; height: 32px;">✕</button>
        </div>

        <div style="padding: 12px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; gap: 8px;">
            <button onclick="window.openFreeNoteModal()" class="btn-toolbar-action" style="flex: 1; justify-content: center; background: #4f46e5; color: #fff; border: none; padding: 7px 12px; font-size: 0.8rem;">
                <span>+ Catatan Halaman</span>
            </button>
            <button onclick="window.copySummaryToClipboard()" class="btn-toolbar-action" style="background: #ffffff; color: #334155; border: 1px solid #cbd5e1; padding: 7px 12px; font-size: 0.8rem;" title="Salin semua rangkuman buku ini ke clipboard">
                <span>📋 Salin Rangkuman</span>
            </button>
        </div>

        <div class="notes-drawer-body">
            @forelse($this->annotations as $item)
                <div class="annotation-card" id="drawer-card-{{ $item->id }}">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span class="color-dot-swatch" {!! 'style="background-color: ' . $item->getColorHex() . ';"' !!}></span>
                            <span style="font-size: 0.75rem; font-weight: 700; color: #475569;">
                                @if($item->chapter)
                                    Bab {{ $item->chapter->chapter_number }}
                                @endif
                                @if($item->page)
                                    • Hal {{ $item->page->page_number }}
                                @endif
                            </span>
                        </div>
                        <span style="font-size: 0.7rem; color: #94a3b8;">
                            {{ $item->created_at->diffForHumans() }}
                        </span>
                    </div>

                    @if($item->highlighted_text)
                        <blockquote class="annotation-quote" {!! 'style="border-left: 3px solid ' . $item->getColorHex() . '; background: rgba(0,0,0,0.02);"' !!}>
                            "{{ $item->highlighted_text }}"
                        </blockquote>
                    @endif

                    @if($item->note)
                        <div class="annotation-note-box">
                            📌 {{ $item->note }}
                        </div>
                    @endif

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 8px; border-top: 1px solid #f1f5f9;">
                        @if($item->page)
                            <button type="button" data-jump-page="{{ $item->page_id }}" onclick="window.jumpToPage(Number(this.dataset.jumpPage))" style="background: none; border: none; color: #4f46e5; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">
                                Buka Halaman ↗
                            </button>
                        @else
                            <span></span>
                        @endif

                        <button wire:click="deleteAnnotation({{ $item->id }})" wire:confirm="Hapus catatan ini?" style="background: none; border: none; color: #ef4444; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;">
                            Hapus 🗑️
                        </button>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 40px 20px; color: #94a3b8;">
                    <div style="font-size: 2.5rem; margin-bottom: 12px;">🖍️</div>
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: #475569; margin-bottom: 6px;">Belum Ada Catatan</h4>
                    <p style="font-size: 0.8rem; line-height: 1.5;">
                        Sorot kalimat penting di halaman buku untuk menandainya dengan stabilo, atau klik tombol <strong>+ Catatan Halaman</strong> di atas!
                    </p>
                </div>
            @endforelse
        </div>
    </aside>

    <!-- Floating Selection Toolbar (Highlighter) -->
    <div id="annotation-toolbar">
        <button class="color-swatch-btn" style="background-color: #fef08a;" onclick="window.applyHighlight('yellow')" title="Stabilo Kuning"></button>
        <button class="color-swatch-btn" style="background-color: #86efac;" onclick="window.applyHighlight('green')" title="Stabilo Hijau"></button>
        <button class="color-swatch-btn" style="background-color: #93c5fd;" onclick="window.applyHighlight('blue')" title="Stabilo Biru"></button>
        <button class="color-swatch-btn" style="background-color: #fdba74;" onclick="window.applyHighlight('orange')" title="Stabilo Oranye"></button>
        <button class="color-swatch-btn" style="background-color: #d8b4fe;" onclick="window.applyHighlight('purple')" title="Stabilo Ungu"></button>
        <button class="color-swatch-btn" style="background-color: #f9a8d4;" onclick="window.applyHighlight('pink')" title="Stabilo Pink"></button>
        <div style="width: 1px; height: 16px; background: rgba(255,255,255,0.2);"></div>
        <button class="btn-toolbar-action" onclick="window.openNoteModalForSelection()" title="Beri Catatan Rangkuman">
            <span>📝</span>
            <span>+ Catatan</span>
        </button>
    </div>

    <!-- Highlight Click Popup -->
    <div id="highlight-popup" style="position: fixed; z-index: 99999; display: none; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 260px; padding: 12px; font-size: 0.82rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
            <span style="font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Kutipan Stabilo</span>
            <button onclick="window.closeHighlightPopup()" style="background: none; border: none; font-size: 0.8rem; cursor: pointer; color: #94a3b8;">✕</button>
        </div>
        <div id="popup-quote" style="font-family: 'Lora', serif; font-style: italic; color: #334155; margin-bottom: 8px; line-height: 1.4; max-height: 80px; overflow-y: auto;"></div>
        <div id="popup-note" style="display: none; background: #fefce8; border: 1px solid #fef08a; border-radius: 6px; padding: 6px 8px; font-size: 0.78rem; color: #713f12; margin-bottom: 8px;"></div>
        <div style="display: flex; justify-content: flex-end; gap: 8px;">
            <button onclick="window.deleteActiveHighlight()" style="background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; border-radius: 6px; padding: 4px 10px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                Hapus Stabilo 🗑️
            </button>
        </div>
    </div>

    <!-- Quick Sticky Note Modal Composer -->
    <div id="note-modal" class="note-modal-backdrop" data-current-page-id="{{ optional($currentPage)->id ?? '' }}" data-current-chapter-id="{{ optional($currentChapter)->id ?? '' }}" style="display: none;">
        <div class="note-modal-card">
            <div style="padding: 18px 22px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0;">📌 Catatan & Rangkuman Siswa</h3>
                <button onclick="window.closeNoteModal()" style="background: none; border: none; font-size: 1.1rem; cursor: pointer; color: #64748b;">✕</button>
            </div>
            <div style="padding: 20px;">
                <div id="note-modal-quote-wrapper" style="margin-bottom: 14px;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 4px;">Kutipan Terpilih:</span>
                    <blockquote id="note-modal-quote" style="font-family: 'Lora', serif; font-style: italic; font-size: 0.85rem; color: #334155; background: #f1f5f9; padding: 8px 12px; border-radius: 8px; border-left: 3px solid #6366f1; margin: 0; max-height: 90px; overflow-y: auto;">
                    </blockquote>
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="note-modal-text" style="font-size: 0.8rem; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Catatan atau Rangkuman Kamu:</label>
                    <textarea id="note-modal-text" rows="4" placeholder="Tuliskan pemahaman penting, kesimpulan, atau catatan pengingat di sini..." style="width: 100%; border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px 12px; font-size: 0.88rem; outline: none; font-family: inherit; resize: vertical; box-sizing: border-box;"></textarea>
                </div>

                <div style="margin-bottom: 16px;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 8px;">Pilihan Warna Catatan / Stabilo:</span>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <label style="cursor: pointer;"><input type="radio" name="modal-note-color" value="yellow" checked style="display: none;"><span class="color-swatch-btn" style="display: inline-block; background: #fef08a;"></span></label>
                        <label style="cursor: pointer;"><input type="radio" name="modal-note-color" value="green" style="display: none;"><span class="color-swatch-btn" style="display: inline-block; background: #86efac;"></span></label>
                        <label style="cursor: pointer;"><input type="radio" name="modal-note-color" value="blue" style="display: none;"><span class="color-swatch-btn" style="display: inline-block; background: #93c5fd;"></span></label>
                        <label style="cursor: pointer;"><input type="radio" name="modal-note-color" value="orange" style="display: none;"><span class="color-swatch-btn" style="display: inline-block; background: #fdba74;"></span></label>
                        <label style="cursor: pointer;"><input type="radio" name="modal-note-color" value="purple" style="display: none;"><span class="color-swatch-btn" style="display: inline-block; background: #d8b4fe;"></span></label>
                        <label style="cursor: pointer;"><input type="radio" name="modal-note-color" value="pink" style="display: none;"><span class="color-swatch-btn" style="display: inline-block; background: #f9a8d4;"></span></label>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button onclick="window.closeNoteModal()" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; color: #475569; font-size: 0.85rem; font-weight: 600; cursor: pointer;">
                        Batal
                    </button>
                    <button onclick="window.submitNoteModal()" style="padding: 8px 20px; border-radius: 8px; border: none; background: #4f46e5; color: #ffffff; font-size: 0.85rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);">
                        Simpan Catatan ✨
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="reader-toast" class="reader-toast"></div>

    <!-- Raw Annotations Payload for Client-side Rendering -->
    <script id="book-annotations-data" type="application/json">
        {!! json_encode($this->annotations->map(fn($item) => [
            'id' => $item->id,
            'page_id' => $item->page_id,
            'chapter_id' => $item->chapter_id,
            'type' => $item->type,
            'highlighted_text' => $item->highlighted_text,
            'color' => $item->color,
            'color_hex' => $item->getColorHex(),
            'note' => $item->note,
            'created_human' => $item->created_at->diffForHumans(),
        ])) !!}
    </script>

    <script id="flip-indices-data" type="application/json">
        {!! json_encode($pageIndices ?? []) !!}
    </script>

    <!-- Script Engine for Annotations & Interactive Reader -->
    <script>
        (function () {
            let selectedText = '';
            let selectedPageId = null;
            let selectedChapterId = null;
            window.activeHighlightId = null;

            const toolbar = document.getElementById('annotation-toolbar');
            const highlightPopup = document.getElementById('highlight-popup');
            const noteModal = document.getElementById('note-modal');

            const flipDataEl = document.getElementById('flip-indices-data');
            window.pageFlipIndexMapping = flipDataEl ? JSON.parse(flipDataEl.textContent || '{}') : {};

            function getLivewire() {
                if (window.Livewire) {
                    const readerWrap = document.querySelector('.reader-container');
                    const compEl = readerWrap ? readerWrap.closest('[wire\\:id]') : document.querySelector('[wire\\:id]');
                    if (compEl) {
                        return window.Livewire.find(compEl.getAttribute('wire:id'));
                    }
                    if (window.Livewire.all && window.Livewire.all().length > 0) {
                        return window.Livewire.all()[0];
                    }
                }
                return null;
            }

            window.jumpToPage = function (pageId) {
                if (window.pageFlipInstance && window.pageFlipIndexMapping && window.pageFlipIndexMapping[pageId] !== undefined) {
                    window.pageFlipInstance.turnToPage(window.pageFlipIndexMapping[pageId]);
                } else {
                    const wire = getLivewire();
                    if (wire) {
                        wire.call('selectPage', pageId);
                    }
                }
            };

            function checkSelection(e) {
                if (e && (e.target.closest('#annotation-toolbar') || e.target.closest('#note-modal') || e.target.closest('#highlight-popup'))) {
                    return;
                }

                setTimeout(() => {
                    const sel = window.getSelection();
                    if (!sel || sel.isCollapsed || !sel.toString().trim()) {
                        hideToolbar();
                        return;
                    }

                    const text = sel.toString().trim();
                    if (text.length < 2) {
                        hideToolbar();
                        return;
                    }

                    const range = sel.getRangeAt(0);
                    const container = range.commonAncestorContainer;
                    const textEl = (container.nodeType === 1 ? container : container.parentElement).closest('.page-text-content');

                    if (!textEl) {
                        hideToolbar();
                        return;
                    }

                    selectedText = text;
                    selectedPageId = textEl.dataset.pageId ? parseInt(textEl.dataset.pageId, 10) : null;
                    selectedChapterId = textEl.dataset.chapterId ? parseInt(textEl.dataset.chapterId, 10) : null;

                    const rect = range.getBoundingClientRect();
                    if (toolbar) {
                        toolbar.style.top = `${rect.top - 8}px`;
                        toolbar.style.left = `${rect.left + (rect.width / 2)}px`;
                        toolbar.style.display = 'flex';
                    }
                }, 50);
            }

            document.addEventListener('mouseup', checkSelection);
            document.addEventListener('touchend', checkSelection);

            function hideToolbar() {
                if (toolbar) toolbar.style.display = 'none';
            }

            window.applyHighlight = function (color) {
                if (!selectedText || !selectedPageId) return;

                const wire = getLivewire();
                if (!wire) return;

                wire.call('saveHighlight', selectedPageId, selectedChapterId, selectedText, color)
                    .then(() => {
                        showToast('Stabilo berhasil disimpan! ✨');
                        hideToolbar();
                        if (window.getSelection) {
                            window.getSelection().removeAllRanges();
                        }
                    });
            };

            window.openNoteModalForSelection = function () {
                hideToolbar();
                const quoteEl = document.getElementById('note-modal-quote');
                const textInput = document.getElementById('note-modal-text');
                if (quoteEl) quoteEl.innerText = `"${selectedText}"`;
                if (textInput) textInput.value = '';
                if (noteModal) noteModal.style.display = 'flex';
            };

            window.openFreeNoteModal = function () {
                selectedText = '';
                const quoteEl = document.getElementById('note-modal-quote');
                const textInput = document.getElementById('note-modal-text');
                if (quoteEl) quoteEl.innerText = 'Catatan Mandiri Halaman Ini';
                if (textInput) textInput.value = '';
                if (noteModal) noteModal.style.display = 'flex';
            };

            window.closeNoteModal = function () {
                if (noteModal) noteModal.style.display = 'none';
            };

            window.submitNoteModal = function () {
                const textInput = document.getElementById('note-modal-text');
                const colorInput = document.querySelector('input[name="modal-note-color"]:checked');
                const note = textInput ? textInput.value.trim() : '';
                const color = colorInput ? colorInput.value : 'yellow';

                if (!note) {
                    alert('Silakan tuliskan catatan terlebih dahulu.');
                    return;
                }

                const wire = getLivewire();
                if (!wire) return;

                if (selectedText && selectedPageId) {
                    wire.call('saveHighlight', selectedPageId, selectedChapterId, selectedText, color, note)
                        .then(() => {
                            window.closeNoteModal();
                            showToast('Catatan & stabilo berhasil disimpan! 📝');
                        });
                } else {
                    const modalEl = document.getElementById('note-modal');
                    const fallbackPageId = selectedPageId || (modalEl && modalEl.dataset.currentPageId ? parseInt(modalEl.dataset.currentPageId, 10) : null);
                    const fallbackChapId = selectedChapterId || (modalEl && modalEl.dataset.currentChapterId ? parseInt(modalEl.dataset.currentChapterId, 10) : null);
                    wire.call('saveStickyNote', fallbackPageId, fallbackChapId, note, color)
                        .then(() => {
                            window.closeNoteModal();
                            showToast('Catatan halaman berhasil ditambahkan! 📌');
                        });
                }
            };

            function showToast(msg) {
                const toast = document.getElementById('reader-toast');
                if (!toast) return;
                toast.innerText = msg;
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 2600);
            }

            window.closeHighlightPopup = function () {
                if (highlightPopup) highlightPopup.style.display = 'none';
                window.activeHighlightId = null;
            };

            window.deleteActiveHighlight = function () {
                if (window.activeHighlightId) {
                    window.deleteAnnotationById(window.activeHighlightId);
                    window.closeHighlightPopup();
                }
            };

            window.deleteAnnotationById = function (id) {
                const wire = getLivewire();
                if (wire) {
                    wire.call('deleteAnnotation', id);
                    document.querySelectorAll(`mark[data-annotation-id="${id}"]`).forEach(m => {
                        const parent = m.parentNode;
                        while (m.firstChild) parent.insertBefore(m.firstChild, m);
                        parent.removeChild(m);
                    });
                    document.querySelectorAll(`.page-note-badge[data-annotation-id="${id}"]`).forEach(b => b.remove());
                    showToast('Catatan berhasil dihapus 🗑️');
                }
            };

            // Click listener on highlight marks
            document.addEventListener('click', function (e) {
                const mark = e.target.closest('mark.book-highlight');
                if (mark) {
                    e.stopPropagation();
                    const annId = mark.dataset.annotationId;
                    const quote = mark.dataset.highlightText || mark.textContent;
                    const note = mark.dataset.note || '';

                    const quoteEl = document.getElementById('popup-quote');
                    const noteEl = document.getElementById('popup-note');

                    if (highlightPopup && quoteEl) {
                        quoteEl.textContent = `"${quote}"`;
                        if (note && noteEl) {
                            noteEl.textContent = `📌 ${note}`;
                            noteEl.style.display = 'block';
                        } else if (noteEl) {
                            noteEl.style.display = 'none';
                        }

                        window.activeHighlightId = annId;

                        const rect = mark.getBoundingClientRect();
                        highlightPopup.style.top = `${Math.min(window.innerHeight - 150, rect.bottom + 6)}px`;
                        highlightPopup.style.left = `${Math.min(window.innerWidth - 270, Math.max(10, rect.left))}px`;
                        highlightPopup.style.display = 'block';
                    }
                } else if (!e.target.closest('#highlight-popup')) {
                    window.closeHighlightPopup();
                }
            });

            // Client-side DOM Highlighter Restoration
            function restoreHighlights() {
                const dataEl = document.getElementById('book-annotations-data');
                if (!dataEl) return;

                let annotations = [];
                try {
                    annotations = JSON.parse(dataEl.textContent);
                } catch (e) {
                    return;
                }

                if (!Array.isArray(annotations) || !annotations.length) return;

                annotations.forEach(item => {
                    if (!item.page_id) return;

                    const containers = document.querySelectorAll(`.page-text-content[data-page-id="${item.page_id}"]`);
                    containers.forEach(container => {
                        // Restore Highlight text
                        if (item.highlighted_text && item.highlighted_text.trim().length >= 2) {
                            if (!container.querySelector(`mark[data-annotation-id="${item.id}"]`)) {
                                highlightTextInContainer(container, item.highlighted_text.trim(), item.id, `book-highlight-${item.color}`, item.note);
                            }
                        }

                        // Restore Page-level Sticky Note Badge
                        if (item.type === 'sticky_note' && item.note) {
                            if (!container.querySelector(`.page-note-badge[data-annotation-id="${item.id}"]`)) {
                                const badge = document.createElement('div');
                                badge.className = 'page-note-badge';
                                badge.dataset.annotationId = item.id;
                                badge.style.cssText = 'background: #fefce8; border: 1px solid #fef08a; border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; font-size: 0.82rem; color: #713f12; display: flex; align-items: center; justify-content: space-between; gap: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);';
                                badge.innerHTML = `
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span>📌</span>
                                        <strong>Catatan:</strong>
                                        <span>${escapeHtml(item.note)}</span>
                                    </div>
                                    <button onclick="window.deleteAnnotationById(${item.id})" style="background: none; border: none; color: #ef4444; font-size: 0.75rem; cursor: pointer; font-weight: 700;">✕</button>
                                `;
                                container.prepend(badge);
                            }
                        }
                    });
                });
            }

            function highlightTextInContainer(container, targetText, annotationId, colorClass, note) {
                const walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT, null, false);
                let node;
                const matches = [];

                while (node = walker.nextNode()) {
                    if (node.parentElement && node.parentElement.closest('mark.book-highlight')) {
                        continue;
                    }
                    if (node.nodeValue.includes(targetText)) {
                        matches.push(node);
                    }
                }

                matches.forEach(textNode => {
                    const parent = textNode.parentNode;
                    if (!parent) return;

                    const content = textNode.nodeValue;
                    const idx = content.indexOf(targetText);
                    if (idx === -1) return;

                    const before = content.substring(0, idx);
                    const match = content.substring(idx, idx + targetText.length);
                    const after = content.substring(idx + targetText.length);

                    const frag = document.createDocumentFragment();
                    if (before) frag.appendChild(document.createTextNode(before));

                    const mark = document.createElement('mark');
                    mark.className = `book-highlight ${colorClass}`;
                    mark.dataset.annotationId = annotationId;
                    mark.dataset.highlightText = match;
                    mark.dataset.note = note || '';
                    mark.textContent = match;

                    if (note) {
                        const pin = document.createElement('span');
                        pin.className = 'note-pin';
                        pin.textContent = '📌';
                        pin.title = note;
                        mark.appendChild(pin);
                    }

                    frag.appendChild(mark);
                    if (after) frag.appendChild(document.createTextNode(after));

                    parent.replaceChild(frag, textNode);
                });
            }

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            window.restoreBookHighlights = restoreHighlights;

            // Trigger restoration on page flip and load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => setTimeout(restoreHighlights, 300));
            } else {
                setTimeout(restoreHighlights, 300);
            }

            document.addEventListener('livewire:navigated', () => setTimeout(restoreHighlights, 300));
            document.addEventListener('livewire:initialized', () => {
                setTimeout(restoreHighlights, 300);
                if (window.Livewire) {
                    window.Livewire.hook('commit', () => setTimeout(restoreHighlights, 100));
                }
            });

            window.addEventListener('annotation-saved', () => setTimeout(restoreHighlights, 150));
            window.addEventListener('annotation-deleted', () => setTimeout(restoreHighlights, 150));

            window.copySummaryToClipboard = function () {
                const cards = document.querySelectorAll('.annotation-card');
                if (!cards.length) {
                    alert('Belum ada rangkuman atau catatan untuk disalin.');
                    return;
                }

                const bookHeader = document.querySelector('.reader-header-title');
                const bookTitleText = bookHeader ? bookHeader.textContent.trim() : 'Buku Digital';
                let summary = '# 📖 Rangkuman & Catatan Buku: ' + bookTitleText + '\n\n';
                cards.forEach((card, idx) => {
                    const quote = card.querySelector('.annotation-quote');
                    const note = card.querySelector('.annotation-note-box');
                    summary += `### Catatan ${idx + 1}\n`;
                    if (quote) summary += `> ${quote.innerText}\n\n`;
                    if (note) summary += `**Rangkuman:** ${note.innerText}\n\n`;
                    summary += `---\n\n`;
                });

                navigator.clipboard.writeText(summary).then(() => {
                    showToast('Semua rangkuman berhasil disalin ke clipboard! 📋');
                }).catch(() => {
                    alert('Gagal menyalin rangkuman.');
                });
            };
        })();
    </script>
</div>

