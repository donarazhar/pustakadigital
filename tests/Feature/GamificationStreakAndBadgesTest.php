<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Book;
use App\Models\Quiz;
use App\Models\StudentQuizAttempt;
use App\Models\StudentReadingLog;
use App\Models\Subject;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GamificationStreakAndBadgesTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    public function test_student_reading_activity_updates_streak_and_points(): void
    {
        $service = app(GamificationService::class);

        $student = User::factory()->create([
            'role' => 'student',
            'reading_streak_days' => 0,
            'longest_streak_days' => 0,
            'literacy_points' => 0,
            'last_read_date' => null,
        ]);

        Carbon::setTestNow('2026-10-01 08:00:00');
        $service->recordReadingActivity($student);

        $student->refresh();
        $this->assertEquals(1, $student->reading_streak_days);
        $this->assertEquals(1, $student->longest_streak_days);
        $this->assertEquals(15, $student->literacy_points);
        $this->assertEquals('2026-10-01', $student->last_read_date->format('Y-m-d'));

        // Hari berikutnya: streak harus bertambah jadi 2 dan poin bertambah
        Carbon::setTestNow('2026-10-02 09:00:00');
        $service->recordReadingActivity($student);

        $student->refresh();
        $this->assertEquals(2, $student->reading_streak_days);
        $this->assertEquals(2, $student->longest_streak_days);
        $this->assertGreaterThan(15, $student->literacy_points);

        // Jika terlewat 2 hari: streak reset ke 1 tapi longest_streak tetap 2
        Carbon::setTestNow('2026-10-05 10:00:00');
        $service->recordReadingActivity($student);

        $student->refresh();
        $this->assertEquals(1, $student->reading_streak_days);
        $this->assertEquals(2, $student->longest_streak_days);
    }

    public function test_5_days_streak_automatically_unlocks_badge(): void
    {
        $service = app(GamificationService::class);
        $service->seedBadges();

        $student = User::factory()->create([
            'role' => 'student',
            'reading_streak_days' => 5,
            'longest_streak_days' => 5,
            'literacy_points' => 100,
        ]);

        $service->checkAndAwardBadges($student);

        $this->assertTrue($student->fresh()->hasBadge('membaca-5-hari-berturut-turut'));
    }

    public function test_perfect_quiz_score_unlocks_juara_kuis_100_badge(): void
    {
        $service = app(GamificationService::class);
        $service->seedBadges();

        $student = User::factory()->create([
            'role' => 'student',
            'literacy_points' => 50,
        ]);

        $quiz = Quiz::first() ?? Quiz::create([
            'title' => 'Kuis Uji Coba',
            'description' => 'Deskripsi kuis',
            'minimum_score' => 70,
            'is_active' => true,
        ]);

        $attempt = StudentQuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'score' => 100,
            'total_questions' => 5,
            'correct_answers' => 5,
            'is_passed' => true,
            'completed_at' => now(),
        ]);

        $service->recordQuizCompleted($student, $attempt);

        $this->assertTrue($student->fresh()->hasBadge('juara-kuis-100'));
        $this->assertGreaterThan(50, $student->fresh()->literacy_points);
    }

    public function test_science_explorer_badge_unlocked_when_reading_science_book(): void
    {
        $service = app(GamificationService::class);
        $service->seedBadges();

        $subject = Subject::firstOrCreate(
            ['name' => 'Ilmu Pengetahuan Alam (IPA)']
        );

        $book = Book::firstOrCreate(
            ['slug' => 'sains-dan-alam-semesta'],
            [
                'title' => 'Sains dan Alam Semesta',
                'subject_id' => $subject->id,
                'author' => 'Pakar Sains',
                'description' => 'Buku sains lengkap',
                'is_published' => true,
            ]
        );

        $student = User::factory()->create([
            'role' => 'student',
            'literacy_points' => 10,
        ]);

        StudentReadingLog::create([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'is_completed' => true,
            'progress_percentage' => 100,
            'last_read_at' => now(),
        ]);

        $service->checkAndAwardBadges($student);

        $this->assertTrue($student->fresh()->hasBadge('penjelajah-sains'));
    }

    public function test_student_can_access_leaderboard_page(): void
    {
        $student = User::where('role', 'student')->first() ?? User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/siswa/papan-peringkat');
        $response->assertStatus(200);
        $response->assertSee('Papan Peringkat Literasi Siswa');
        $response->assertSee('Semua Siswa');
        $response->assertSee('Poin Literasi');
    }

    public function test_student_can_access_lencana_literasi_page(): void
    {
        $student = User::where('role', 'student')->first() ?? User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/siswa/lencana-literasi');
        $response->assertStatus(200);
        $response->assertSee('Koleksi Lencana Literasi');
        $response->assertSee('Lencana Koleksimu');
        $response->assertSee('Terkunci');
    }

    public function test_student_dashboard_displays_streak_and_leaderboard_stats(): void
    {
        $student = User::where('role', 'student')->first() ?? User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/siswa');
        $response->assertStatus(200);
        $response->assertSee('Reading Streak');
        $response->assertSee('Peringkat Literasi');
    }
}
