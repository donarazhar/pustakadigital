<div>
    <!-- Sticky Top Header -->
    <header class="reader-header">
        <div class="reader-header-left" style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ auth()->check() && auth()->user()->isStudent() ? url('/siswa') : route('home') }}" class="btn-icon" title="Kembali">
                ←
            </a>
            <img src="{{ asset('images/logo-hitam.png') }}" alt="Logo Sekolah" style="height: 30px; width: auto; object-fit: contain;">
            <div>
                <h1 class="reader-header-title" style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;">{{ $book->title }}</h1>
                <div class="book-badge-meta" style="display: flex; align-items: center; gap: 6px; margin-top: 3px;">
                    <span class="badge" style="background: #fef3c7; color: #b45309; font-size: 0.72rem; font-weight: 700;">📄 E-Book PDF</span>
                    @if($book->grade)
                        <span class="badge badge-primary">{{ $book->grade->name }}</span>
                    @endif
                    @if($book->subject)
                        <span class="badge badge-success">{{ $book->subject->name }}</span>
                    @endif
                    <span id="header-page-count" class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.72rem;">
                        {{ $book->total_pages ? $book->total_pages . ' Halaman' : 'Memuat halaman...' }}
                    </span>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <!-- Mode Switcher: 3D Flipbook vs Document Scroll -->
            <div style="background: #f1f5f9; padding: 4px; border-radius: 10px; display: flex; gap: 4px; border: 1px solid var(--border-color);">
                <button 
                    id="btn-mode-3d"
                    onclick="switchPdfViewMode('3d')" 
                    class="btn-icon btn-mode-toggle active" 
                    title="Membaca dengan Sensasi Lembaran Buku Fisik 3D"
                    style="font-size: 0.8rem; padding: 4px 10px; border-radius: 6px;"
                >
                    📖 Buku 3D
                </button>
                <button 
                    id="btn-mode-doc"
                    onclick="switchPdfViewMode('doc')" 
                    class="btn-icon btn-mode-toggle" 
                    title="Membaca Dokumen PDF Standar"
                    style="font-size: 0.8rem; padding: 4px 10px; border-radius: 6px;"
                >
                    📄 Dokumen
                </button>
            </div>

            @if($book->pdf_file)
                <a href="{{ route('books.secure-pdf', ['book' => $book->slug, 'download' => 1]) }}" class="btn-icon" style="width: auto; padding: 0 14px; font-size: 0.82rem; gap: 6px; background: #f0fdf4; border-color: #bbf7d0; color: #15803d;" title="Unduh Berkas PDF Resmi">
                    <span>⬇️</span>
                    <span>Unduh PDF</span>
                </a>
            @endif

            <button onclick="togglePdfFullscreen()" class="btn-icon" style="width: auto; padding: 0 12px; font-size: 0.82rem;" title="Mode Layar Penuh">
                ⛶ Layar Penuh
            </button>

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

    <!-- 3D FLIPBOOK VIEW MODE -->
    <div id="view-mode-3d-container" class="reader-container flipbook-layout" style="display: block;">
        <div class="flipbook-theater">
            <!-- Loading Indicator -->
            <div id="pdf-3d-loading" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 480px; gap: 16px;">
                <div class="loading-spinner" style="width: 44px; height: 44px; border: 4px solid #e2e8f0; border-top-color: #4f46e5; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                <div style="text-align: center;">
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">Menyiapkan Buku 3D dari Berkas PDF...</h3>
                    <p id="pdf-loading-progress-text" style="font-size: 0.85rem; color: #64748b;">Merender lembaran halaman...</p>
                </div>
            </div>

            <!-- Outer Container for 3D Book -->
            <div id="pdf-flipbook-outer" class="flipbook-container-outer" style="display: none;">
                <!-- Left Nav Arrow -->
                <button id="btn-pdf-prev" class="flip-nav-arrow flip-nav-left" title="Halaman Sebelumnya">
                    ‹
                </button>

                <!-- Book Element for StPageFlip -->
                <div id="pdf-flipbook" class="flip-book">
                    <!-- Pages will be dynamically appended here as canvas or div.flip-page elements -->
                </div>

                <!-- Right Nav Arrow -->
                <button id="btn-pdf-next" class="flip-nav-arrow flip-nav-right" title="Halaman Selanjutnya">
                    ›
                </button>
            </div>

            <!-- Floating Control Dock -->
            <div id="pdf-control-dock" class="flip-control-dock" style="display: none;">
                <button id="btn-pdf-dock-prev" class="dock-btn" onclick="if(window.pdfPageFlip) window.pdfPageFlip.flipPrev();">
                    <span>‹</span> Sebelumnya
                </button>

                <div class="dock-slider-wrap">
                    <input type="range" id="pdf-scrubber" min="0" max="1" value="0" class="dock-slider">
                    <span id="pdf-page-indicator" style="font-size: 0.78rem; font-weight: 700; min-width: 110px; text-align: center; color: #f1f5f9;">
                        Halaman 1
                    </span>
                </div>

                <button id="btn-pdf-dock-next" class="dock-btn" onclick="if(window.pdfPageFlip) window.pdfPageFlip.flipNext();">
                    Selanjutnya <span>›</span>
                </button>

                <div style="width: 1px; height: 18px; background: rgba(255,255,255,0.2);"></div>

                <button id="btn-sound-toggle" class="dock-btn" onclick="togglePdfSound()">
                    <span>🔊</span> Suara
                </button>
            </div>
        </div>
    </div>

    <!-- DOCUMENT EMBED VIEW MODE -->
    <div id="view-mode-doc-container" class="reader-container" style="display: none; height: calc(100vh - 90px); padding: 16px 20px 24px 20px;">
        <div style="width: 100%; height: 100%; border-radius: 14px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid var(--border-color);">
            @if($book->pdf_file)
                <iframe 
                    id="doc-pdf-frame"
                    src="{{ route('books.secure-pdf', $book->slug) }}#toolbar=1&navpanes=1&scrollbar=1&view=FitH" 
                    style="width: 100%; height: 100%; border: none;"
                    title="{{ $book->title }}"
                ></iframe>
            @endif
        </div>
    </div>

    <style>
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .btn-mode-toggle.active {
            background: #ffffff !important;
            color: #4f46e5 !important;
            font-weight: 700 !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .pdf-page-canvas {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain;
            display: block;
            background: #ffffff;
        }
    </style>

    <script>
        let isSoundEnabled = true;
        let pdfDoc = null;
        let totalPages = 0;
        let is3DInitialized = false;

        // Sound effect on page flip
        function playFlipSound() {
            if (!isSoundEnabled) return;
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(140, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(380, ctx.currentTime + 0.08);
                gain.gain.setValueAtTime(0.06, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.12);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.13);
            } catch(e) {}
        }

        function togglePdfSound() {
            isSoundEnabled = !isSoundEnabled;
            const btn = document.getElementById('btn-sound-toggle');
            if (btn) {
                btn.innerHTML = isSoundEnabled ? '<span>🔊</span> Suara' : '<span>🔇</span> Senyap';
            }
        }

        function switchPdfViewMode(mode) {
            const container3d = document.getElementById('view-mode-3d-container');
            const containerDoc = document.getElementById('view-mode-doc-container');
            const btn3d = document.getElementById('btn-mode-3d');
            const btnDoc = document.getElementById('btn-mode-doc');

            if (mode === '3d') {
                container3d.style.display = 'block';
                containerDoc.style.display = 'none';
                btn3d.classList.add('active');
                btnDoc.classList.remove('active');
                if (window.pdfPageFlip) {
                    try { window.pdfPageFlip.update(); } catch(e) {}
                }
            } else {
                container3d.style.display = 'none';
                containerDoc.style.display = 'block';
                btn3d.classList.remove('active');
                btnDoc.classList.add('active');
            }
        }

        function togglePdfFullscreen() {
            const elem = document.documentElement;
            if (!document.fullscreenElement) {
                if (elem.requestFullscreen) elem.requestFullscreen();
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
            }
        }

        // Initialize PDF 3D Flipbook using PDF.js & StPageFlip
        async function initPdf3DFlipbook() {
            const pdfUrl = "{{ route('books.secure-pdf', $book->slug) }}";
            if (!pdfUrl) return;

            const loadingEl = document.getElementById('pdf-3d-loading');
            const outerContainer = document.getElementById('pdf-flipbook-outer');
            const dock = document.getElementById('pdf-control-dock');
            const bookEl = document.getElementById('pdf-flipbook');
            const progressText = document.getElementById('pdf-loading-progress-text');
            const pageIndicator = document.getElementById('pdf-page-indicator');
            const scrubber = document.getElementById('pdf-scrubber');
            const headerPageCount = document.getElementById('header-page-count');

            if (typeof pdfjsLib === 'undefined' || typeof St === 'undefined' || typeof St.PageFlip === 'undefined') {
                console.warn('Menunggu library termuat...');
                setTimeout(initPdf3DFlipbook, 300);
                return;
            }

            try {
                pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/pdf.worker.min.js') }}";
                const loadingTask = pdfjsLib.getDocument(pdfUrl);
                pdfDoc = await loadingTask.promise;
                totalPages = pdfDoc.numPages;

                if (headerPageCount) {
                    headerPageCount.textContent = totalPages + ' Halaman';
                }

                // Render all pages to canvas elements inside flip-page wrappers
                bookEl.innerHTML = '';
                const renderedPages = [];

                for (let i = 1; i <= totalPages; i++) {
                    progressText.textContent = `Merender halaman ${i} dari ${totalPages}...`;
                    const page = await pdfDoc.getPage(i);

                    // High DPI rendering scale
                    const viewport = page.getViewport({ scale: 1.5 });

                    const pageDiv = document.createElement('div');
                    pageDiv.className = 'flip-page ' + (i === 1 ? 'page-hard page-cover-front' : 'page-soft');
                    pageDiv.style.backgroundColor = '#ffffff';
                    pageDiv.style.overflow = 'hidden';

                    const canvas = document.createElement('canvas');
                    canvas.className = 'pdf-page-canvas';
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    await page.render({
                        canvasContext: context,
                        viewport: viewport
                    }).promise;

                    pageDiv.appendChild(canvas);
                    bookEl.appendChild(pageDiv);
                    renderedPages.push(pageDiv);
                }

                // Ensure total pages is even for perfect 3D hardcover closure
                if (renderedPages.length % 2 !== 0) {
                    const blankBack = document.createElement('div');
                    blankBack.className = 'flip-page page-hard page-cover-back';
                    blankBack.style.backgroundColor = '#1e1b4b';
                    blankBack.style.display = 'flex';
                    blankBack.style.alignItems = 'center';
                    blankBack.style.justifyContent = 'center';
                    blankBack.style.color = 'rgba(255,255,255,0.4)';
                    blankBack.innerHTML = '<span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">Pustaka Digital Sekolah</span>';
                    bookEl.appendChild(blankBack);
                    renderedPages.push(blankBack);
                }

                // Setup StPageFlip
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

                pageFlip.loadFromHTML(renderedPages);
                window.pdfPageFlip = pageFlip;

                // Wire up controls
                if (scrubber) {
                    scrubber.max = renderedPages.length - 1;
                    scrubber.value = 0;
                    scrubber.addEventListener('input', function() {
                        pageFlip.turnToPage(parseInt(this.value, 10));
                    });
                }

                const prevBtn = document.getElementById('btn-pdf-prev');
                const nextBtn = document.getElementById('btn-pdf-next');

                if (prevBtn) prevBtn.onclick = () => pageFlip.flipPrev();
                if (nextBtn) nextBtn.onclick = () => pageFlip.flipNext();

                pageFlip.on('flip', (e) => {
                    playFlipSound();
                    const current = e.data + 1;
                    if (pageIndicator) {
                        pageIndicator.textContent = current === 1 ? 'Sampul Depan' : (current >= renderedPages.length ? 'Sampul Belakang' : `Hal ${current} dari ${totalPages}`);
                    }
                    if (scrubber) {
                        scrubber.value = e.data;
                    }
                });

                // Show flipbook and dock, hide loader
                loadingEl.style.display = 'none';
                outerContainer.style.display = 'flex';
                dock.style.display = 'flex';
                is3DInitialized = true;

            } catch (err) {
                console.error('Error saat merender PDF 3D:', err);
                if (progressText) {
                    progressText.innerHTML = '<span style="color: #ef4444;">Gagal memuat visual 3D. Beralih ke Mode Dokumen...</span>';
                }
                setTimeout(() => switchPdfViewMode('doc'), 1500);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initPdf3DFlipbook();
        });
    </script>
</div>
