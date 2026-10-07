<?php

namespace App\Filament\Siswa\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class LeaderboardPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Papan Peringkat';
    protected static string|\UnitEnum|null $navigationGroup = 'Aktivitas Belajar';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = '🏆 Papan Peringkat Literasi Siswa';
    protected static ?string $slug = 'papan-peringkat';

    protected string $view = 'filament.siswa.pages.leaderboard-page';

    public string $filterScope = 'all'; // 'all' atau 'grade'

    public function setFilter(string $scope): void
    {
        $this->filterScope = in_array($scope, ['all', 'grade']) ? $scope : 'all';
    }

    public function getStudentsProperty(): Collection
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $query = User::where('role', 'student')
            ->with(['grade', 'badges'])
            ->withCount([
                'readingLogs as completed_books_count' => fn ($q) => $q->where('is_completed', true),
                'badges as badges_count',
            ]);

        if ($this->filterScope === 'grade' && $currentUser && $currentUser->grade_id) {
            $query->where('grade_id', $currentUser->grade_id);
        }

        return $query
            ->orderByDesc('literacy_points')
            ->orderByDesc('reading_streak_days')
            ->orderByDesc('completed_books_count')
            ->get()
            ->values();
    }

    public function getCurrentUserRankProperty(): int
    {
        $userId = Auth::id();
        $students = $this->students;

        $rank = $students->search(fn ($s) => $s->id === $userId);

        return $rank !== false ? $rank + 1 : 1;
    }
}
