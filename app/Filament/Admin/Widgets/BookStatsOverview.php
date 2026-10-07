<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Quiz;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalBooks = Book::count();
        $totalChapters = Chapter::count();
        $totalStudents = User::where('role', 'student')->count();
        $totalQuizzes = Quiz::where('is_active', true)->count();

        return [
            Stat::make('Buku Digital', $totalBooks)
                ->description('Total judul buku tersedia')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary')
                ->chart([7, 10, 14, 18, 22, 25, $totalBooks]),

            Stat::make('Bab Pembelajaran', $totalChapters)
                ->description('Total modul materi interaktif')
                ->descriptionIcon('heroicon-m-bookmark-square')
                ->color('success'),

            Stat::make('Siswa Terdaftar', $totalStudents)
                ->description('Siswa aktif membaca')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning'),

            Stat::make('Kuis Interaktif', $totalQuizzes)
                ->description('Evaluasi pemahaman aktif')
                ->descriptionIcon('heroicon-m-puzzle-piece')
                ->color('danger'),
        ];
    }
}
