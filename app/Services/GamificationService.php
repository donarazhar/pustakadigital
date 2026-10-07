<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Book;
use App\Models\StudentQuizAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GamificationService
{
    /**
     * Catat aktivitas membaca siswa, hitung streak harian & poin literasi.
     */
    public function recordReadingActivity(User $user, ?Book $book = null): array
    {
        if (! $user->isStudent()) {
            return [];
        }

        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');
        $lastDate = $user->last_read_date?->format('Y-m-d');

        $pointsAwarded = 0;

        if ($lastDate === $today) {
            // Sudah tercatat hari ini, berikan poin aktivitas membaca tambahan
            $pointsAwarded = 5;
            $user->literacy_points += $pointsAwarded;
        } elseif ($lastDate === $yesterday) {
            // Streak berlanjut hari berikutnya!
            $user->reading_streak_days += 1;
            $bonus = 15 + min(35, ($user->reading_streak_days - 1) * 5); // 15..50 poin
            $pointsAwarded = $bonus;
            $user->literacy_points += $pointsAwarded;
        } else {
            // Hari pertama atau streak terputus
            $user->reading_streak_days = 1;
            $pointsAwarded = 15;
            $user->literacy_points += $pointsAwarded;
        }

        $user->longest_streak_days = max((int) $user->longest_streak_days, (int) $user->reading_streak_days);
        $user->last_read_date = Carbon::now();
        $user->save();

        $newBadges = $this->checkAndAwardBadges($user);

        return [
            'streak' => $user->reading_streak_days,
            'points_awarded' => $pointsAwarded,
            'new_badges' => $newBadges,
        ];
    }

    /**
     * Catat evaluasi kuis yang diselesaikan oleh siswa.
     */
    public function recordQuizCompleted(User $user, StudentQuizAttempt $attempt): array
    {
        if (! $user->isStudent()) {
            return [];
        }

        $points = 10;
        if ($attempt->is_passed) {
            $points += (int) round($attempt->score / 2); // Skor 80 => +40 poin
        }
        if ((int) $attempt->score === 100) {
            $points += 50; // Bonus nilai sempurna
        }

        $user->increment('literacy_points', $points);
        $user->refresh();

        $newBadges = $this->checkAndAwardBadges($user);

        return [
            'points_awarded' => $points,
            'new_badges' => $newBadges,
        ];
    }

    /**
     * Catat aktivitas pembuatan stabilo atau catatan rangkuman mandiri.
     */
    public function recordAnnotationCreated(User $user): array
    {
        if (! $user->isStudent()) {
            return [];
        }

        $user->increment('literacy_points', 10);
        $user->refresh();

        $newBadges = $this->checkAndAwardBadges($user);

        return [
            'points_awarded' => 10,
            'new_badges' => $newBadges,
        ];
    }

    /**
     * Evaluasi semua kriteria lencana yang belum dimiliki siswa dan sematkan secara otomatis.
     */
    public function checkAndAwardBadges(User $user): Collection
    {
        $existingBadgeIds = $user->badges()->pluck('badges.id')->toArray();
        $availableBadges = Badge::whereNotIn('id', $existingBadgeIds)->get();

        $newlyEarned = collect();

        foreach ($availableBadges as $badge) {
            if ($this->isCriteriaMet($user, $badge)) {
                $user->badges()->attach($badge->id, [
                    'awarded_at' => now(),
                    'notes' => 'Diperoleh secara otomatis oleh sistem literasi',
                ]);

                // Tambahkan poin hadiah lencana
                if ($badge->points_reward > 0) {
                    $user->increment('literacy_points', $badge->points_reward);
                }

                $newlyEarned->push($badge);
            }
        }

        return $newlyEarned;
    }

    /**
     * Validasi pemenuhan kriteria perolehan lencana.
     */
    protected function isCriteriaMet(User $user, Badge $badge): bool
    {
        return match ($badge->criteria_type) {
            'streak_days' => $user->reading_streak_days >= $badge->criteria_value
                || $user->longest_streak_days >= $badge->criteria_value,

            'perfect_quiz' => $user->quizAttempts()
                ->where('score', 100)
                ->count() >= $badge->criteria_value,

            'quizzes_passed' => $user->quizAttempts()
                ->where('is_passed', true)
                ->count() >= $badge->criteria_value,

            'completed_books' => $user->readingLogs()
                ->where('is_completed', true)
                ->count() >= $badge->criteria_value,

            'total_readings' => $user->readingLogs()
                ->count() >= $badge->criteria_value,

            'annotations_count' => $user->bookAnnotations()
                ->count() >= $badge->criteria_value,

            'subject_science' => $user->readingLogs()
                ->whereHas('book.subject', function ($q) {
                    $q->where('name', 'like', '%IPA%')
                      ->orWhere('name', 'like', '%Sains%')
                      ->orWhere('code', 'IPA');
                })
                ->where('is_completed', true)
                ->exists(),

            default => false,
        };
    }

    /**
     * Inisialisasi daftar lencana literasi bawaan sekolah.
     */
    public static function seedBadges(): void
    {
        $badges = [
            [
                'name' => 'Kutu Buku Pemula',
                'slug' => 'kutu-buku-pemula',
                'description' => 'Membuka dan membaca buku digital pertamamu di Pustaka Sekolah.',
                'icon' => '🌱',
                'badge_color' => 'emerald',
                'category' => 'reading',
                'criteria_type' => 'total_readings',
                'criteria_value' => 1,
                'points_reward' => 50,
                'order' => 1,
            ],
            [
                'name' => 'Penjelajah Sains',
                'slug' => 'penjelajah-sains',
                'description' => 'Menuntaskan membaca seluruh materi buku bertopik Ilmu Pengetahuan Alam (IPA/Sains).',
                'icon' => '🔬',
                'badge_color' => 'blue',
                'category' => 'subject',
                'criteria_type' => 'subject_science',
                'criteria_value' => 1,
                'points_reward' => 150,
                'order' => 2,
            ],
            [
                'name' => 'Semangat 3 Hari',
                'slug' => 'semangat-3-hari',
                'description' => 'Konsisten membaca buku selama 3 hari berturut-turut tanpa jeda.',
                'icon' => '⚡',
                'badge_color' => 'amber',
                'category' => 'streak',
                'criteria_type' => 'streak_days',
                'criteria_value' => 3,
                'points_reward' => 100,
                'order' => 3,
            ],
            [
                'name' => 'Membaca 5 Hari Berturut-turut',
                'slug' => 'membaca-5-hari-berturut-turut',
                'description' => 'Mencapai Reading Streak legendaris selama 5 hari berturut-turut!',
                'icon' => '🔥',
                'badge_color' => 'rose',
                'category' => 'streak',
                'criteria_type' => 'streak_days',
                'criteria_value' => 5,
                'points_reward' => 200,
                'order' => 4,
            ],
            [
                'name' => 'Juara Kuis 100',
                'slug' => 'juara-kuis-100',
                'description' => 'Meraih nilai sempurna 100 pada evaluasi kuis bab pembelajaran.',
                'icon' => '💯',
                'badge_color' => 'amber',
                'category' => 'quiz',
                'criteria_type' => 'perfect_quiz',
                'criteria_value' => 1,
                'points_reward' => 150,
                'order' => 5,
            ],
            [
                'name' => 'Ahli Evaluasi Tangkas',
                'slug' => 'ahli-evaluasi-tangkas',
                'description' => 'Berhasil lulus minimal 3 evaluasi kuis bab pembelajaran.',
                'icon' => '🎯',
                'badge_color' => 'purple',
                'category' => 'quiz',
                'criteria_type' => 'quizzes_passed',
                'criteria_value' => 3,
                'points_reward' => 120,
                'order' => 6,
            ],
            [
                'name' => 'Kolektor Catatan & Stabilo',
                'slug' => 'kolektor-catatan-stabilo',
                'description' => 'Membuat minimal 5 kalimat stabilo atau catatan rangkuman mandiri.',
                'icon' => '✍️',
                'badge_color' => 'cyan',
                'category' => 'annotation',
                'criteria_type' => 'annotations_count',
                'criteria_value' => 5,
                'points_reward' => 100,
                'order' => 7,
            ],
            [
                'name' => 'Master Literasi Sekolah',
                'slug' => 'master-literasi-sekolah',
                'description' => 'Menuntaskan membaca 3 buku digital sekolah secara lengkap 100%.',
                'icon' => '👑',
                'badge_color' => 'amber',
                'category' => 'reading',
                'criteria_type' => 'completed_books',
                'criteria_value' => 3,
                'points_reward' => 300,
                'order' => 8,
            ],
        ];

        foreach ($badges as $b) {
            Badge::updateOrCreate(['slug' => $b['slug']], $b);
        }
    }
}
