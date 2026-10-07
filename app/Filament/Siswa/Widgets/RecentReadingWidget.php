<?php

namespace App\Filament\Siswa\Widgets;

use App\Models\StudentReadingLog;
use Filament\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;

class RecentReadingWidget extends TableWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Buku Terakhir Dibaca';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StudentReadingLog::query()
                    ->where('user_id', Auth::id())
                    ->with(['book.grade', 'book.subject', 'lastChapter'])
                    ->latest('last_read_at')
            )
            ->columns([
                ImageColumn::make('book.cover_image')
                    ->label('Sampul')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl('/images/default-book-cover.png'),

                TextColumn::make('book.title')
                    ->label('Judul Buku')
                    ->weight('bold')
                    ->description(fn ($record) => $record->lastChapter ? "Bab: {$record->lastChapter->title}" : null),

                TextColumn::make('book.grade.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),

                TextColumn::make('progress_percent')
                    ->label('Progress')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'info',
                        default => 'warning',
                    }),

                TextColumn::make('last_read_at')
                    ->label('Waktu Baca')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('baca')
                    ->label('Lanjutkan')
                    ->icon('heroicon-m-book-open')
                    ->color('primary')
                    ->url(fn (StudentReadingLog $record): string => route('books.read', $record->book->slug)),
            ])
            ->emptyStateHeading('Belum Ada Buku Yang Dibaca')
            ->emptyStateDescription('Mulai jelajahi katalog buku dan tingkatkan literasimu!')
            ->paginated([5]);
    }
}
