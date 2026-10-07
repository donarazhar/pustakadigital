<?php

namespace App\Filament\Siswa\Pages;

use App\Models\Badge;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class LencanaLiterasiPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Lencana Literasi';
    protected static string|\UnitEnum|null $navigationGroup = 'Aktivitas Belajar';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = '🏅 Koleksi Lencana Literasi';
    protected static ?string $slug = 'lencana-literasi';

    protected string $view = 'filament.siswa.pages.lencana-literasi-page';

    public function getBadgesProperty(): Collection
    {
        /** @var User $user */
        $user = Auth::user();
        $earnedBadgeIds = $user ? $user->badges()->pluck('badges.id')->toArray() : [];
        $earnedBadgesMap = $user ? $user->badges()->get()->keyBy('id') : collect();

        $allBadges = Badge::orderBy('order')->get();

        return $allBadges->map(function (Badge $b) use ($earnedBadgeIds, $earnedBadgesMap, $user) {
            $isEarned = in_array($b->id, $earnedBadgeIds);
            $pivot = $isEarned ? $earnedBadgesMap->get($b->id)?->pivot : null;

            // Hitung persentase progres untuk lencana yang belum terbuka
            $progressData = $this->calculateProgress($user, $b);

            return [
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
                'description' => $b->description,
                'icon' => $b->icon,
                'badge_color' => $b->badge_color,
                'category' => $b->category,
                'points_reward' => $b->points_reward,
                'is_earned' => $isEarned,
                'awarded_at' => $pivot ? $pivot->awarded_at : null,
                'progress_current' => $progressData['current'],
                'progress_target' => $progressData['target'],
                'progress_percent' => $progressData['percent'],
            ];
        });
    }

    protected function calculateProgress(?User $user, Badge $badge): array
    {
        if (! $user) {
            return ['current' => 0, 'target' => $badge->criteria_value, 'percent' => 0];
        }

        $current = 0;
        $target = $badge->criteria_value ?: 1;

        switch ($badge->criteria_type) {
            case 'streak_days':
                $current = max($user->reading_streak_days, $user->longest_streak_days);
                break;
            case 'perfect_quiz':
                $current = $user->quizAttempts()->where('score', 100)->count();
                break;
            case 'quizzes_passed':
                $current = $user->quizAttempts()->where('is_passed', true)->count();
                break;
            case 'completed_books':
                $current = $user->readingLogs()->where('is_completed', true)->count();
                break;
            case 'total_readings':
                $current = $user->readingLogs()->count();
                break;
            case 'annotations_count':
                $current = $user->bookAnnotations()->count();
                break;
            case 'subject_science':
                $current = $user->readingLogs()
                    ->whereHas('book.subject', fn ($q) => $q->where('name', 'like', '%IPA%')->orWhere('name', 'like', '%Sains%'))
                    ->where('is_completed', true)
                    ->count();
                break;
        }

        $percent = min(100, (int) round(($current / $target) * 100));

        return [
            'current' => $current,
            'target' => $target,
            'percent' => $percent,
        ];
    }
}
