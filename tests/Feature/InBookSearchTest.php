<?php

namespace Tests\Feature;

use App\Livewire\BookReader;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Page;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class InBookSearchTest extends TestCase
{
    public function test_reader_page_renders_inbook_search_button_and_drawer(): void
    {
        $book = Book::where('slug', 'petualangan-sains-mengenal-tata-surya')->first() ?? Book::first();
        $this->assertNotNull($book);

        $user = User::first() ?? User::factory()->create();
        $response = $this->actingAs($user)->get("/baca/{$book->slug}");
        $response->assertStatus(200);

        // Header tombol pencarian
        $response->assertSee('btn-inbook-search', false);
        $response->assertSee('id="btn-inbook-search-toggle"', false);
        $response->assertSee('Cari Kata');
        $response->assertSee('Ctrl+F');

        // Slide-over search drawer & input
        $response->assertSee('inbook-search-drawer', false);
        $response->assertSee('inbook-search-query-input', false);
        $response->assertSee('inbook-search-index-data', false);
        $response->assertSee('book-search.js', false);
    }

    public function test_livewire_book_reader_search_in_book_finds_exact_matches_with_snippets(): void
    {
        $book = Book::where('slug', 'petualangan-sains-mengenal-tata-surya')->first() ?? Book::first();
        $this->assertNotNull($book);

        $chapter = $book->chapters->first();
        $page = $chapter->pages->first();
        $page->update([
            'content' => '<p>Matahari adalah bintang induk yang menjadi pusat tata surya kita dengan gravitasi yang sangat kuat.</p>',
        ]);

        $component = Livewire::test(BookReader::class, ['book' => $book]);

        $searchResult = $component->instance()->searchInBook('gravitasi');

        $this->assertIsArray($searchResult);
        $this->assertEquals('gravitasi', $searchResult['query']);
        $this->assertGreaterThanOrEqual(1, $searchResult['total_matches']);
        $this->assertNotEmpty($searchResult['results']);

        $firstResult = $searchResult['results'][0];
        $this->assertEquals($page->id, $firstResult['page_id']);
        $this->assertEquals($chapter->id, $firstResult['chapter_id']);
        $this->assertStringContainsString('snippet-highlight', $firstResult['snippet']);
        $this->assertStringContainsString('gravitasi', $firstResult['snippet']);
    }

    public function test_search_with_short_keyword_returns_empty(): void
    {
        $book = Book::first();
        $this->assertNotNull($book);

        $component = Livewire::test(BookReader::class, ['book' => $book]);
        $searchResult = $component->instance()->searchInBook('x');

        $this->assertEquals(0, $searchResult['total_matches']);
        $this->assertEmpty($searchResult['results']);
    }

    public function test_jump_to_page_and_highlight_dispatches_browser_event(): void
    {
        $book = Book::first();
        $this->assertNotNull($book);
        $page = $book->chapters->first()->pages->first();

        Livewire::test(BookReader::class, ['book' => $book])
            ->call('jumpToPageAndHighlight', $page->id, 'matahari')
            ->assertDispatched('highlight-search-keyword', pageId: $page->id, keyword: 'matahari');
    }

    public function test_toggle_search_modal_dispatches_clear_event_when_closing(): void
    {
        $book = Book::first();
        $this->assertNotNull($book);

        Livewire::test(BookReader::class, ['book' => $book])
            ->call('toggleSearchModal') // Open
            ->assertSet('isSearchModalOpen', true)
            ->call('toggleSearchModal') // Close
            ->assertSet('isSearchModalOpen', false)
            ->assertSet('searchKeyword', '')
            ->assertSet('searchResults', [])
            ->assertDispatched('clear-inbook-search');
    }
}
