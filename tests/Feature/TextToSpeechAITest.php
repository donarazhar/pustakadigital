<?php

namespace Tests\Feature;

use App\Livewire\BookReader;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Page;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class TextToSpeechAITest extends TestCase
{
    public function test_reader_displays_ai_tts_player_when_page_has_no_manual_audio(): void
    {
        $book = Book::where('slug', 'petualangan-sains-mengenal-tata-surya')->first() ?? Book::first();
        $this->assertNotNull($book);

        // Pastikan ada halaman tanpa rekaman manual
        $chapter = $book->chapters->first();
        $page = $chapter->pages->first();
        $page->update(['audio_narration_url' => null]);

        $user = User::first() ?? User::factory()->create();
        $response = $this->actingAs($user)->get("/baca/{$book->slug}");
        $response->assertStatus(200);

        // Widget AI TTS Otomatis harus tampil
        $response->assertSee('ai-tts-player', false);
        $response->assertSee('🤖 AI TTS');
        $response->assertSee('Narasi Suara Otomatis');
        $response->assertSee('Bacakan Teks');

        // Pengaturan kontrol kecepatan & popover harus ada
        $response->assertSee('tts-settings-popover', false);
        $response->assertSee('Pilihan Suara AI');
        $response->assertSee('Bacakan otomatis saat membalik halaman');
    }

    public function test_reader_displays_manual_recording_when_teacher_audio_exists(): void
    {
        $book = Book::where('slug', 'petualangan-sains-mengenal-tata-surya')->first() ?? Book::first();
        $chapter = $book->chapters->first();
        $page = $chapter->pages->first();

        $page->update(['audio_narration_url' => 'https://sekolah.id/audio/bab1-narasi.mp3']);

        $user = User::first() ?? User::factory()->create();
        $response = $this->actingAs($user)->get("/baca/{$book->slug}");
        $response->assertStatus(200);

        $response->assertSee('manual-audio-player', false);
        $response->assertSee('Rekaman Guru:');
    }

    public function test_focus_mode_displays_ai_tts_player_when_no_audio(): void
    {
        $book = Book::where('slug', 'petualangan-sains-mengenal-tata-surya')->first() ?? Book::first();
        $chapter = $book->chapters->first();
        $page = $chapter->pages->first();
        $page->update(['audio_narration_url' => null]);

        // Uji komponen Livewire saat beralih ke mode fokus
        Livewire::test(BookReader::class, ['book' => $book, 'chapter' => $chapter])
            ->set('viewMode', 'focus')
            ->assertSee('ai-tts-player', false)
            ->assertSee('Narator AI Berbahasa Indonesia')
            ->assertSee('Bacakan Teks');
    }
}
