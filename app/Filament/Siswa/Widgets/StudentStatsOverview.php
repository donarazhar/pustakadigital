<?php

namespace App\Filament\Siswa\Widgets;

use App\Models\Book;
use App\Models\StudentQuizAttempt;
use App\Models\StudentReadingLog;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StudentStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $userId = Auth::id();

        $totalBooksAvailable = Book::where('is_published', true)->count();
        $readingLogsCount = StudentReadingLog::where('user_id', $userId)->count();
        $completedBooks = StudentReadingLog::where('user_id', $userId)->where('is_completed', true)->count();
        
        $quizAttempts = StudentQuizAttempt::where('user_id', $userId);
        $totalQuizzesTaken = (clone $quizAttempts)->count();
        $averageQuizScore = $totalQuizzesTaken > 0 ? round((clone $quizAttempts)->avg('score')) : 0;

        $assignedReadings = \App\Models\ReadingAssignmentStudent::where('student_id', $userId)
            ->whereHas('assignment', fn ($q) => $q->where('is_active', true));
        $totalAssignments = (clone $assignedReadings)->count();
        $completedAssignments = (clone $assignedReadings)->where('status', 'completed')->count();
        $pendingAssignments = $totalAssignments - $completedAssignments;

        /** @var \App\Models\User $user */
        $user = Auth::user();

        return [
            Stat::make('Reading Streak', ($user->reading_streak_days ?? 0) . ' Hari 🔥')
                ->description('Rekor: ' . ($user->longest_streak_days ?? 0) . ' hari berturut-turut')
                ->descriptionIcon('heroicon-m-fire')
                ->color('danger'),

            Stat::make('Peringkat Literasi', '#' . $user->getLeaderboardRank() . ' • ' . $user->badges()->count() . ' 🏅')
                ->description(number_format($user->literacy_points ?? 0) . ' Poin Literasi')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),

            Stat::make('Tugas Membaca', $totalAssignments > 0 ? "{$completedAssignments} / {$totalAssignments}" : '0 Tugas')
                ->description($totalAssignments > 0 ? ($pendingAssignments > 0 ? "{$pendingAssignments} tugas belum tuntas" : 'Semua tugas telah tuntas! 🎉') : 'Belum ada tugas membaca aktif')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($totalAssignments > 0 ? ($pendingAssignments > 0 ? 'warning' : 'success') : 'gray'),

            Stat::make('Sedang Dibaca', $readingLogsCount)
                ->description("{$completedBooks} buku telah diselesaikan")
                ->descriptionIcon('heroicon-m-bookmark')
                ->color('success'),
        ];
    }
}
