<?php

namespace App\Livewire;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Page;
use App\Models\ReadingAssignment;
use App\Models\ReadingAssignmentStudent;
use App\Models\StudentBookAnnotation;
use App\Models\StudentReadingLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.reader')]
class BookReader extends Component
{
    public Book $book;
    public ?Chapter $currentChapter = null;
    public ?Page $currentPage = null;
    public int $currentPageIndex = 0;
    public int $totalPagesInChapter = 0;
    public bool $isSidebarOpen = false;
    public bool $isNotesDrawerOpen = false;
    public bool $showQuiz = false;
    public string $viewMode = 'flipbook'; // 'flipbook' or 'focus'
    public int $activeQuizChapterId = 0;
    public string $activeHighlightColor = 'yellow';
    public string $searchKeyword = '';
    public bool $isSearchModalOpen = false;
    public array $searchResults = [];

    public function mount(Book $book, ?Chapter $chapter = null, ?int $page = null)
    {
        $this->book = $book->load(['chapters.pages', 'chapters.quiz.questions.options', 'grade', 'subject', 'category']);

        // Set active chapter
        if ($chapter && $chapter->book_id === $this->book->id) {
            $this->currentChapter = $chapter;
        } else {
            $this->currentChapter = $this->book->chapters->first();
        }

        if ($this->currentChapter) {
            $this->loadChapterPages($page);
        }

        if ($this->book->isPdf() && Auth::check()) {
            StudentReadingLog::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'book_id' => $this->book->id,
                ],
                [
                    'progress_percent' => 100,
                    'is_completed' => true,
                    'last_read_at' => now(),
                ]
            );
            $this->syncAssignmentProgress(100);
        }

        // Increment book view count
        $this->book->increment('view_count');
    }

    public function switchMode(string $mode)
    {
        if (in_array($mode, ['flipbook', 'focus'])) {
            $this->viewMode = $mode;
        }
    }

    public function openQuizForChapter(int $chapterId)
    {
        $chap = $this->book->chapters->firstWhere('id', $chapterId);
        if ($chap && $chap->quiz && $chap->quiz->is_active) {
            $this->currentChapter = $chap;
            $this->activeQuizChapterId = $chap->id;
            $this->showQuiz = true;
        }
    }

    public function recordFlipbookProgress(int $currentPage, int $totalPages)
    {
        if (Auth::check() && $totalPages > 0) {
            $percent = min(100, (int) round(($currentPage / $totalPages) * 100));

            StudentReadingLog::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'book_id' => $this->book->id,
                ],
                [
                    'progress_percent' => $percent,
                    'is_completed' => $percent >= 100,
                    'last_read_at' => now(),
                ]
            );

            $this->syncAssignmentProgress($percent);
        }
    }

    public function loadChapterPages(?int $targetPageNumber = null)
    {
        if (! $this->currentChapter) {
            return;
        }

        $pages = $this->currentChapter->pages;
        $this->totalPagesInChapter = $pages->count();

        if ($targetPageNumber && $targetPage = $pages->firstWhere('page_number', $targetPageNumber)) {
            $this->currentPage = $targetPage;
        } else {
            $this->currentPage = $pages->first();
        }

        $this->currentPageIndex = $pages->search(fn ($p) => $p->id === optional($this->currentPage)->id) ?: 0;
        $this->showQuiz = false;

        $this->updateReadingProgress();
    }

    public function nextPage()
    {
        if (! $this->currentChapter) {
            return;
        }

        $pages = $this->currentChapter->pages;

        if ($this->currentPageIndex < $pages->count() - 1) {
            $this->currentPageIndex++;
            $this->currentPage = $pages[$this->currentPageIndex];
            $this->updateReadingProgress();
        } else {
            // End of chapter reached - Check if chapter has a quiz!
            if ($this->currentChapter->quiz && $this->currentChapter->quiz->is_active) {
                $this->showQuiz = true;
            } else {
                // Move to next chapter if available
                $this->nextChapter();
            }
        }
    }

    public function previousPage()
    {
        if ($this->showQuiz) {
            $this->showQuiz = false;
            return;
        }

        if ($this->currentPageIndex > 0) {
            $this->currentPageIndex--;
            $this->currentPage = $this->currentChapter->pages[$this->currentPageIndex];
            $this->updateReadingProgress();
        }
    }

    public function selectChapter(int $chapterId)
    {
        $target = $this->book->chapters->firstWhere('id', $chapterId);
        if ($target) {
            $this->currentChapter = $target;
            $this->loadChapterPages();
            $this->isSidebarOpen = false;
        }
    }

    public function selectPage(int $pageId)
    {
        $target = $this->currentChapter->pages->firstWhere('id', $pageId);
        if ($target) {
            $this->currentPage = $target;
            $this->currentPageIndex = $this->currentChapter->pages->search(fn ($p) => $p->id === $pageId);
            $this->showQuiz = false;
            $this->isSidebarOpen = false;
            $this->updateReadingProgress();
        }
    }

    public function nextChapter()
    {
        $currentIdx = $this->book->chapters->search(fn ($c) => $c->id === $this->currentChapter->id);
        if ($currentIdx !== false && $currentIdx < $this->book->chapters->count() - 1) {
            $this->currentChapter = $this->book->chapters[$currentIdx + 1];
            $this->loadChapterPages();
        }
    }

    public function openQuiz()
    {
        $this->showQuiz = true;
    }

    public function closeQuiz()
    {
        $this->showQuiz = false;
    }

    public function toggleSidebar()
    {
        $this->isSidebarOpen = ! $this->isSidebarOpen;
    }

    public function toggleSearchModal()
    {
        $this->isSearchModalOpen = ! $this->isSearchModalOpen;
        if (! $this->isSearchModalOpen) {
            $this->searchKeyword = '';
            $this->searchResults = [];
            $this->dispatch('clear-inbook-search');
        }
    }

    public function updatedSearchKeyword($value)
    {
        $this->searchInBook($value);
    }

    public function searchInBook(?string $keyword = null): array
    {
        $query = trim($keyword ?? $this->searchKeyword);
        if (mb_strlen($query) < 2) {
            $this->searchResults = [];
            return [
                'query' => $query,
                'total_matches' => 0,
                'results' => [],
            ];
        }

        $results = [];
        $totalMatches = 0;

        foreach ($this->book->chapters as $chapter) {
            foreach ($chapter->pages as $page) {
                $cleanContent = strip_tags(html_entity_decode($page->content ?? ''));
                $searchContent = ($page->title ? $page->title . ' ' : '') . $cleanContent;

                $count = mb_substr_count(mb_strtolower($searchContent), mb_strtolower($query));
                if ($count > 0) {
                    $totalMatches += $count;
                    $snippet = $this->generateSnippet($searchContent, $query);

                    $results[] = [
                        'chapter_id' => $chapter->id,
                        'chapter_title' => $chapter->title,
                        'chapter_number' => $chapter->chapter_number,
                        'page_id' => $page->id,
                        'page_number' => $page->page_number,
                        'page_title' => $page->title ?: 'Halaman ' . $page->page_number,
                        'matches_count' => $count,
                        'snippet' => $snippet,
                    ];
                }
            }
        }

        $this->searchResults = $results;
        return [
            'query' => $query,
            'total_matches' => $totalMatches,
            'results' => $results,
        ];
    }

    protected function generateSnippet(string $text, string $query, int $radius = 60): string
    {
        $pos = mb_stripos($text, $query);
        if ($pos === false) {
            return e(mb_substr($text, 0, 120)) . '...';
        }

        $start = max(0, $pos - $radius);
        $length = mb_strlen($query) + ($radius * 2);
        $rawExcerpt = mb_substr($text, $start, $length);

        $prefix = $start > 0 ? '...' : '';
        $suffix = ($start + $length) < mb_strlen($text) ? '...' : '';

        $escaped = e($rawExcerpt);
        $highlighted = preg_replace('/(' . preg_quote($query, '/') . ')/iu', '<mark class="snippet-highlight">$1</mark>', $escaped);

        return $prefix . $highlighted . $suffix;
    }

    public function jumpToPageAndHighlight(int $pageId, ?string $keyword = null)
    {
        $targetChapter = $this->book->chapters->first(function ($c) use ($pageId) {
            return $c->pages->contains('id', $pageId);
        });

        if ($targetChapter) {
            $this->currentChapter = $targetChapter;
            $this->selectPage($pageId);
        }

        $searchWord = $keyword ?? $this->searchKeyword;
        $this->dispatch('highlight-search-keyword', pageId: $pageId, keyword: $searchWord);
    }

    protected function updateReadingProgress()
    {
        if (Auth::check() && $this->currentPage) {
            // Hitung total halaman di semua bab
            $totalBookPages = $this->book->chapters->sum(fn ($c) => $c->pages->count());
            if ($totalBookPages > 0) {
                // Halaman saat ini dari awal buku
                $pagesBefore = 0;
                foreach ($this->book->chapters as $chap) {
                    if ($chap->id === $this->currentChapter->id) {
                        break;
                    }
                    $pagesBefore += $chap->pages->count();
                }
                $currentGlobalPage = $pagesBefore + $this->currentPageIndex + 1;
                $percent = min(100, (int) round(($currentGlobalPage / $totalBookPages) * 100));

                StudentReadingLog::updateOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'book_id' => $this->book->id,
                    ],
                    [
                        'last_chapter_id' => $this->currentChapter->id,
                        'last_page_id' => $this->currentPage->id,
                        'progress_percent' => $percent,
                        'is_completed' => $percent >= 100,
                        'last_read_at' => now(),
                    ]
                );

                $this->syncAssignmentProgress($percent);

                app(\App\Services\GamificationService::class)->recordReadingActivity(Auth::user(), $this->book);
            }
        }
    }

    public function getActiveAssignmentProperty(): ?ReadingAssignment
    {
        if (! Auth::check()) {
            return null;
        }

        $asStudent = ReadingAssignmentStudent::where('student_id', Auth::id())
            ->whereHas('assignment', function ($q) {
                $q->where('book_id', $this->book->id)
                  ->where('is_active', true);
            })
            ->with('assignment')
            ->first();

        return $asStudent?->assignment;
    }

    protected function syncAssignmentProgress(int $bookPercent): void
    {
        if (! Auth::check()) {
            return;
        }

        $userId = Auth::id();
        $assignments = ReadingAssignmentStudent::where('student_id', $userId)
            ->whereHas('assignment', function ($q) {
                $q->where('book_id', $this->book->id)
                  ->where('is_active', true);
            })
            ->with('assignment')
            ->get();

        foreach ($assignments as $as) {
            $targetChapId = $as->assignment->target_chapter_id;
            if ($targetChapId) {
                if ($this->currentChapter && $this->currentChapter->id === $targetChapId) {
                    $chapPages = $this->totalPagesInChapter ?: 1;
                    $chapPercent = min(100, (int) round((($this->currentPageIndex + 1) / $chapPages) * 100));
                    if ($chapPercent > $as->progress_percent) {
                        $as->updateProgress($chapPercent);
                    }
                }
            } else {
                if ($bookPercent > $as->progress_percent) {
                    $as->updateProgress($bookPercent);
                }
            }
        }
    }

    public function getAnnotationsProperty()
    {
        if (! Auth::check()) {
            return collect();
        }

        return StudentBookAnnotation::where('user_id', Auth::id())
            ->where('book_id', $this->book->id)
            ->with(['chapter', 'page'])
            ->latest()
            ->get();
    }

    public function toggleNotesDrawer()
    {
        $this->isNotesDrawerOpen = ! $this->isNotesDrawerOpen;
    }

    public function saveHighlight(int $pageId, ?int $chapterId, string $highlightedText, string $color = 'yellow', ?string $note = null)
    {
        if (! Auth::check()) {
            return null;
        }

        $type = ! empty($note) ? 'highlight_note' : 'highlight';

        $annotation = StudentBookAnnotation::create([
            'user_id' => Auth::id(),
            'book_id' => $this->book->id,
            'chapter_id' => $chapterId,
            'page_id' => $pageId,
            'type' => $type,
            'highlighted_text' => trim($highlightedText),
            'color' => in_array($color, ['yellow', 'green', 'blue', 'orange', 'purple', 'pink']) ? $color : 'yellow',
            'note' => $note ? trim($note) : null,
        ]);

        $this->dispatch('annotation-saved', annotation: $annotation->toArray());

        app(\App\Services\GamificationService::class)->recordAnnotationCreated(Auth::user());

        return $annotation->id;
    }

    public function saveStickyNote(int $pageId, ?int $chapterId, string $note, string $color = 'yellow')
    {
        if (! Auth::check() || empty(trim($note))) {
            return null;
        }

        $annotation = StudentBookAnnotation::create([
            'user_id' => Auth::id(),
            'book_id' => $this->book->id,
            'chapter_id' => $chapterId,
            'page_id' => $pageId,
            'type' => 'sticky_note',
            'highlighted_text' => null,
            'color' => in_array($color, ['yellow', 'green', 'blue', 'orange', 'purple', 'pink']) ? $color : 'yellow',
            'note' => trim($note),
        ]);

        $this->dispatch('annotation-saved', annotation: $annotation->toArray());

        app(\App\Services\GamificationService::class)->recordAnnotationCreated(Auth::user());

        return $annotation->id;
    }

    public function updateAnnotation(int $annotationId, ?string $note = null, ?string $color = null)
    {
        if (! Auth::check()) {
            return;
        }

        $annotation = StudentBookAnnotation::where('id', $annotationId)
            ->where('user_id', Auth::id())
            ->first();

        if ($annotation) {
            $data = [];
            if ($note !== null) {
                $data['note'] = trim($note);
                if ($annotation->type === 'highlight' && ! empty(trim($note))) {
                    $data['type'] = 'highlight_note';
                }
            }
            if ($color && in_array($color, ['yellow', 'green', 'blue', 'orange', 'purple', 'pink'])) {
                $data['color'] = $color;
            }

            $annotation->update($data);
            $this->dispatch('annotation-updated', annotation: $annotation->toArray());
        }
    }

    public function deleteAnnotation(int $annotationId)
    {
        if (! Auth::check()) {
            return;
        }

        StudentBookAnnotation::where('id', $annotationId)
            ->where('user_id', Auth::id())
            ->delete();

        $this->dispatch('annotation-deleted', id: $annotationId);
    }

    public function render()
    {
        if ($this->book->isPdf()) {
            return view('livewire.pdf-reader');
        }

        return view('livewire.book-reader');
    }
}
