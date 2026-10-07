/**
 * Pustaka Digital Sekolah - In-Book Search & Text Highlighting Engine
 * Fitur pencarian kata kunci di seluruh halaman buku dengan penandaan visual (highlight) otomatis.
 */

(function () {
    class BookSearchController {
        constructor() {
            this.indexData = [];
            this.currentQuery = '';
            this.currentResults = [];
            this.allMatches = []; // Array of { pageId, flipIndex, elementIndex, markElement }
            this.activeMatchIndex = -1;
            this.isOpen = false;
            this.debounceTimer = null;

            this.dom = {
                drawer: null,
                overlay: null,
                input: null,
                btnClear: null,
                countLabel: null,
                navControls: null,
                btnPrev: null,
                btnNext: null,
                resultsContainer: null,
                toggleBtn: null,
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.init());
            } else {
                this.init();
            }
        }

        init() {
            this.loadIndexData();
            this.bindDomElements();
            this.bindEvents();
        }

        loadIndexData() {
            const indexScript = document.getElementById('inbook-search-index-data');
            if (indexScript) {
                try {
                    this.indexData = JSON.parse(indexScript.textContent);
                } catch (e) {
                    console.warn('[BookSearch] Gagal mem-parse data indeks:', e);
                }
            }
        }

        bindDomElements() {
            this.dom.drawer = document.getElementById('inbook-search-drawer');
            this.dom.overlay = document.getElementById('inbook-search-overlay');
            this.dom.input = document.getElementById('inbook-search-query-input');
            this.dom.btnClear = document.getElementById('inbook-btn-clear-search');
            this.dom.countLabel = document.getElementById('inbook-search-count-label');
            this.dom.navControls = document.getElementById('inbook-search-nav-controls');
            this.dom.btnPrev = document.getElementById('btn-inbook-prev-match');
            this.dom.btnNext = document.getElementById('btn-inbook-next-match');
            this.dom.resultsContainer = document.getElementById('inbook-search-results-container');
            this.dom.toggleBtn = document.getElementById('btn-inbook-search-toggle');
        }

        bindEvents() {
            if (this.dom.input) {
                this.dom.input.addEventListener('input', (e) => {
                    const val = e.target.value;
                    if (this.dom.btnClear) {
                        this.dom.btnClear.style.display = val.length > 0 ? 'inline-block' : 'none';
                    }
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => this.search(val), 200);
                });

                this.dom.input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (e.shiftKey) {
                            this.prevMatch();
                        } else {
                            this.nextMatch();
                        }
                    } else if (e.key === 'Escape') {
                        this.close();
                    }
                });
            }

            // Keyboard shortcut global Ctrl+F / Cmd+F
            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && (e.key === 'f' || e.key === 'F')) {
                    // Hanya tangkap jika pembaca buku aktif di layar
                    const readerTheater = document.getElementById('flipbook-theater') || document.querySelector('.book-sheet');
                    if (readerTheater) {
                        e.preventDefault();
                        this.open();
                    }
                } else if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            });

            // Livewire Custom Event Listeners
            window.addEventListener('highlight-search-keyword', (e) => {
                const detail = e.detail || {};
                const pageId = detail.pageId;
                const keyword = detail.keyword;
                if (keyword) {
                    this.highlightPageMatches(pageId, keyword);
                }
            });

            window.addEventListener('clear-inbook-search', () => {
                this.clear();
            });
        }

        open() {
            this.isOpen = true;
            if (this.dom.drawer) this.dom.drawer.classList.add('open');
            if (this.dom.overlay) this.dom.overlay.style.display = 'block';
            setTimeout(() => {
                if (this.dom.input) {
                    this.dom.input.focus();
                    this.dom.input.select();
                }
            }, 150);
        }

        close() {
            this.isOpen = false;
            if (this.dom.drawer) this.dom.drawer.classList.remove('open');
            if (this.dom.overlay) this.dom.overlay.style.display = 'none';
        }

        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        }

        clear() {
            if (this.dom.input) {
                this.dom.input.value = '';
                this.dom.input.focus();
            }
            if (this.dom.btnClear) {
                this.dom.btnClear.style.display = 'none';
            }
            this.currentQuery = '';
            this.currentResults = [];
            this.allMatches = [];
            this.activeMatchIndex = -1;
            this.clearInPageHighlights();
            this.renderEmptyState();
        }

        search(query) {
            const q = (query || '').trim();
            this.currentQuery = q;

            if (q.length < 2) {
                this.currentResults = [];
                this.clearInPageHighlights();
                this.renderEmptyState(q.length === 1 ? 'Ketik minimal 2 karakter...' : null);
                return;
            }

            const results = [];
            let totalOccurrences = 0;
            const lowerQ = q.toLowerCase();

            this.indexData.forEach((item) => {
                const text = item.plain_text || '';
                const lowerText = text.toLowerCase();
                let count = 0;
                let pos = lowerText.indexOf(lowerQ);

                while (pos !== -1) {
                    count++;
                    pos = lowerText.indexOf(lowerQ, pos + lowerQ.length);
                }

                if (count > 0) {
                    totalOccurrences += count;
                    const snippet = this.generateSnippet(text, q);
                    results.push({
                        chapter_id: item.chapter_id,
                        chapter_title: item.chapter_title,
                        chapter_number: item.chapter_number,
                        page_id: item.page_id,
                        page_number: item.page_number,
                        page_title: item.page_title,
                        flip_index: item.flip_index,
                        count: count,
                        snippet: snippet,
                    });
                }
            });

            this.currentResults = results;
            this.renderResults(results, totalOccurrences, q);

            // Highlight halaman yang sedang aktif terbuka di layar
            this.highlightCurrentVisiblePage(q);
        }

        generateSnippet(text, query, radius = 55) {
            const lowerText = text.toLowerCase();
            const lowerQuery = query.toLowerCase();
            const pos = lowerText.indexOf(lowerQuery);

            if (pos === -1) {
                return this.escapeHtml(text.substring(0, 110)) + '...';
            }

            const start = Math.max(0, pos - radius);
            const end = Math.min(text.length, pos + query.length + radius);
            let excerpt = text.substring(start, end);

            const prefix = start > 0 ? '...' : '';
            const suffix = end < text.length ? '...' : '';

            // Safe highlight inside snippet
            const safeExcerpt = this.escapeHtml(excerpt);
            const regex = new RegExp(`(${this.escapeRegex(query)})`, 'gi');
            const highlighted = safeExcerpt.replace(regex, '<mark class="snippet-highlight">$1</mark>');

            return prefix + highlighted + suffix;
        }

        renderResults(results, totalOccurrences, query) {
            if (!this.dom.resultsContainer || !this.dom.countLabel) return;

            if (results.length === 0) {
                this.dom.countLabel.innerText = '0 hasil ditemukan';
                if (this.dom.navControls) this.dom.navControls.style.display = 'none';
                this.dom.resultsContainer.innerHTML = `
                    <div class="search-empty-state">
                        <div style="font-size: 2.2rem; margin-bottom: 8px;">🔍</div>
                        <div style="font-weight: 700; color: #475569; margin-bottom: 4px;">Kata Tidak Ditemukan</div>
                        <p style="font-size: 0.8rem; line-height: 1.5; color: #94a3b8;">
                            Tidak ada kecocokan untuk kata <strong>"${this.escapeHtml(query)}"</strong>. Coba periksa ejaan atau gunakan sinonim lain.
                        </p>
                    </div>
                `;
                return;
            }

            this.dom.countLabel.innerText = `Ditemukan ${totalOccurrences} hasil (${results.length} halaman)`;
            if (this.dom.navControls) this.dom.navControls.style.display = 'flex';

            const html = results.map((r, idx) => `
                <div class="search-result-card" data-result-idx="${idx}" onclick="window.BookSearch.jumpToResult(${r.page_id}, ${r.flip_index || 0}, '${this.escapeHtml(query)}')">
                    <div class="search-result-header">
                        <span class="search-result-location">
                            Bab ${r.chapter_number} &bull; Hal ${r.page_number}
                        </span>
                        <span class="search-result-badge">
                            ${r.count}x muncul
                        </span>
                    </div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                        ${this.escapeHtml(r.page_title)}
                    </div>
                    <div class="search-result-snippet">
                        "${r.snippet}"
                    </div>
                </div>
            `).join('');

            this.dom.resultsContainer.innerHTML = html;
        }

        renderEmptyState(customMessage = null) {
            if (!this.dom.resultsContainer || !this.dom.countLabel) return;
            this.dom.countLabel.innerText = customMessage || 'Ketik kata kunci untuk mencari';
            if (this.dom.navControls) this.dom.navControls.style.display = 'none';

            this.dom.resultsContainer.innerHTML = `
                <div class="search-empty-state">
                    <div style="font-size: 2.4rem; margin-bottom: 10px;">📖</div>
                    <div style="font-weight: 700; color: #475569; margin-bottom: 4px;">Pencarian Seluruh Halaman</div>
                    <p style="font-size: 0.8rem; line-height: 1.5; max-width: 280px; margin: 0 auto;">
                        Ketik kata kunci (misal: "planet", "fotosintesis", rumus) untuk mencari di seluruh lembaran buku dan menandai lokasinya secara otomatis.
                    </p>
                </div>
            `;
        }

        jumpToResult(pageId, flipIndex, query) {
            // Tandai kartu aktif di drawer
            document.querySelectorAll('.search-result-card').forEach((card) => {
                card.classList.remove('active');
            });
            const activeCard = document.querySelector(`.search-result-card[onclick*="${pageId}"]`);
            if (activeCard) activeCard.classList.add('active');

            // 1. Jika dalam mode 3D Flipbook
            if (window.pageFlipInstance && typeof window.flipToPage === 'function') {
                const targetIdx = typeof flipIndex === 'number' && flipIndex >= 0 ? flipIndex : 0;
                window.flipToPage(targetIdx);

                // Berikan jeda animasi buka buku lalu sorot teks
                setTimeout(() => {
                    this.highlightPageMatches(pageId, query);
                }, 450);
            } else {
                // 2. Mode Baca Fokus
                if (typeof window.jumpToPage === 'function') {
                    window.jumpToPage(pageId);
                } else if (window.Livewire) {
                    const livewireComponent = Livewire.find(document.querySelector('[wire\\:id]')?.getAttribute('wire:id'));
                    if (livewireComponent) {
                        livewireComponent.call('jumpToPageAndHighlight', pageId, query);
                    }
                }
                setTimeout(() => {
                    this.highlightPageMatches(pageId, query);
                }, 300);
            }
        }

        highlightCurrentVisiblePage(query) {
            if (!query) return;

            // Cari halaman yang sedang terlihat
            let activePageId = null;

            // Di mode 3D Flipbook
            const visiblePages = document.querySelectorAll('.flip-page.page-soft');
            for (const p of visiblePages) {
                const textContainer = p.querySelector('.page-text-content');
                if (textContainer && p.offsetParent !== null) {
                    activePageId = textContainer.getAttribute('data-page-id');
                    break;
                }
            }

            // Di mode Fokus
            if (!activePageId) {
                const focusArea = document.getElementById('focus-content-area');
                if (focusArea) {
                    activePageId = focusArea.getAttribute('data-page-id');
                }
            }

            if (activePageId) {
                this.highlightPageMatches(activePageId, query);
            }
        }

        highlightPageMatches(pageId, query) {
            this.clearInPageHighlights();
            if (!query || query.length < 2) return;

            const selector = `.page-text-content[data-page-id="${pageId}"]`;
            const containers = document.querySelectorAll(selector);
            if (!containers || containers.length === 0) return;

            const allFoundMarks = [];

            containers.forEach((container) => {
                const marks = this.wrapTextWithMarks(container, query);
                allFoundMarks.push(...marks);
            });

            this.allMatches = allFoundMarks;

            if (this.allMatches.length > 0) {
                this.setActiveMatch(0);
            } else {
                this.activeMatchIndex = -1;
                this.updateMatchCounter();
            }
        }

        wrapTextWithMarks(container, query) {
            if (!container || !query) return [];
            const regex = new RegExp(`(${this.escapeRegex(query)})`, 'gi');
            const marks = [];

            const walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT, {
                acceptNode: (node) => {
                    if (!node.parentElement) return NodeFilter.FILTER_SKIP;
                    const tag = node.parentElement.tagName.toUpperCase();
                    if (tag === 'SCRIPT' || tag === 'STYLE' || node.parentElement.classList.contains('in-book-search-match')) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    return regex.test(node.nodeValue) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_SKIP;
                },
            });

            const textNodes = [];
            while (walker.nextNode()) {
                textNodes.push(walker.currentNode);
            }

            textNodes.forEach((textNode) => {
                const text = textNode.nodeValue;
                const fragment = document.createDocumentFragment();
                let lastIdx = 0;

                text.replace(regex, (match, p1, offset) => {
                    if (offset > lastIdx) {
                        fragment.appendChild(document.createTextNode(text.substring(lastIdx, offset)));
                    }
                    const mark = document.createElement('mark');
                    mark.className = 'in-book-search-match';
                    mark.textContent = match;
                    fragment.appendChild(mark);
                    marks.push(mark);
                    lastIdx = offset + match.length;
                });

                if (lastIdx < text.length) {
                    fragment.appendChild(document.createTextNode(text.substring(lastIdx)));
                }

                if (textNode.parentNode) {
                    textNode.parentNode.replaceChild(fragment, textNode);
                }
            });

            return marks;
        }

        setActiveMatch(index) {
            if (this.allMatches.length === 0) {
                this.activeMatchIndex = -1;
                this.updateMatchCounter();
                return;
            }

            this.allMatches.forEach((m) => m.classList.remove('active-match'));

            // Wrap index within range
            let targetIdx = index;
            if (targetIdx >= this.allMatches.length) targetIdx = 0;
            if (targetIdx < 0) targetIdx = this.allMatches.length - 1;

            this.activeMatchIndex = targetIdx;
            const targetEl = this.allMatches[this.activeMatchIndex];

            if (targetEl) {
                targetEl.classList.add('active-match');
                try {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } catch (e) {
                    targetEl.scrollIntoView();
                }
            }

            this.updateMatchCounter();
        }

        nextMatch() {
            if (this.allMatches.length === 0) return;
            this.setActiveMatch(this.activeMatchIndex + 1);
        }

        prevMatch() {
            if (this.allMatches.length === 0) return;
            this.setActiveMatch(this.activeMatchIndex - 1);
        }

        updateMatchCounter() {
            if (!this.dom.countLabel) return;
            if (this.allMatches.length > 0) {
                this.dom.countLabel.innerText = `Sorotan ${this.activeMatchIndex + 1} dari ${this.allMatches.length} di halaman ini`;
            } else if (this.currentResults.length > 0) {
                this.dom.countLabel.innerText = `Ditemukan ${this.currentResults.reduce((a, b) => a + b.count, 0)} hasil (${this.currentResults.length} halaman)`;
            }
        }

        clearInPageHighlights() {
            const marks = document.querySelectorAll('mark.in-book-search-match');
            marks.forEach((mark) => {
                const parent = mark.parentNode;
                if (parent) {
                    parent.replaceChild(document.createTextNode(mark.textContent), mark);
                    parent.normalize();
                }
            });
            this.allMatches = [];
            this.activeMatchIndex = -1;
        }

        escapeHtml(str) {
            if (!str) return '';
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        escapeRegex(str) {
            return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }
    }

    // Pasang ke objek window global
    window.BookSearch = new BookSearchController();
})();
