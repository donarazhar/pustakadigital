<?php

namespace Tests\Feature;

use App\Livewire\BookReader;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Page;
use App\Models\StudentBookAnnotation;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class HighlightAndNotesTest extends TestCase
{
    public function test_student_can_access_catatan_stabilo_panel_page(): void
    {
        $student = User::where('role', 'student')->first();
        $this->assertNotNull($student);

        $response = $this->actingAs($student)->get('/siswa/catatan-stabilo');
        $response->assertStatus(200);
        $response->assertSee('Catatan & Stabilo');
    }

    public function test_student_can_create_highlight_and_sticky_note_via_model(): void
    {
        $student = User::where('role', 'student')->first();
        $book = Book::first();
        $chapter = $book->chapters->first();
        $page = $chapter->pages->first();

        $annotation = StudentBookAnnotation::create([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'chapter_id' => $chapter->id,
            'page_id' => $page->id,
            'type' => 'highlight',
            'highlighted_text' => 'Matahari adalah bintang yang paling dekat dengan bumi.',
            'color' => 'yellow',
            'note' => 'Konsep penting tentang posisi matahari',
        ]);

        $this->assertDatabaseHas('student_book_annotations', [
            'id' => $annotation->id,
            'user_id' => $student->id,
            'book_id' => $book->id,
            'color' => 'yellow',
            'type' => 'highlight',
        ]);

        $this->assertEquals('#fef08a', $annotation->getColorHex());
        $this->assertTrue($annotation->isHighlight());
        $this->assertTrue($annotation->isStickyNote());
    }

    public function test_livewire_book_reader_save_highlight_action(): void
    {
        $student = User::where('role', 'student')->first();
        $book = Book::first();
        $chapter = $book->chapters->first();
        $page = $chapter->pages->first();

        Livewire::actingAs($student)
            ->test(BookReader::class, ['book' => $book])
            ->call('saveHighlight', $page->id, $chapter->id, 'Gravitasi matahari menjaga planet tetap berada di orbitnya.', 'green', 'Hukum gravitasi')
            ->assertDispatched('annotation-saved');

        $this->assertDatabaseHas('student_book_annotations', [
            'user_id' => $student->id,
            'book_id' => $book->id,
            'page_id' => $page->id,
            'color' => 'green',
            'type' => 'highlight_note',
            'note' => 'Hukum gravitasi',
        ]);
    }

    public function test_livewire_book_reader_save_sticky_note_action(): void
    {
        $student = User::where('role', 'student')->first();
        $book = Book::first();
        $chapter = $book->chapters->first();
        $page = $chapter->pages->first();

        Livewire::actingAs($student)
            ->test(BookReader::class, ['book' => $book])
            ->call('saveStickyNote', $page->id, $chapter->id, 'Catatan mandiri: jangan lupa pelajari bab ini lagi sebelum ujian.', 'purple')
            ->assertDispatched('annotation-saved');

        $this->assertDatabaseHas('student_book_annotations', [
            'user_id' => $student->id,
            'book_id' => $book->id,
            'page_id' => $page->id,
            'color' => 'purple',
            'type' => 'sticky_note',
        ]);
    }

    public function test_livewire_book_reader_delete_annotation_action(): void
    {
        $student = User::where('role', 'student')->first();
        $book = Book::first();
        $chapter = $book->chapters->first();
        $page = $chapter->pages->first();

        $annotation = StudentBookAnnotation::create([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'chapter_id' => $chapter->id,
            'page_id' => $page->id,
            'type' => 'highlight',
            'highlighted_text' => 'Teks yang akan dihapus.',
            'color' => 'blue',
        ]);

        Livewire::actingAs($student)
            ->test(BookReader::class, ['book' => $book])
            ->call('deleteAnnotation', $annotation->id)
            ->assertDispatched('annotation-deleted', id: $annotation->id);

        $this->assertDatabaseMissing('student_book_annotations', [
            'id' => $annotation->id,
        ]);
    }
}
