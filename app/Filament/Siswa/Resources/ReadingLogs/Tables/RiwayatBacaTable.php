<?php

namespace App\Filament\Siswa\Resources\ReadingLogs\Tables;

use App\Models\StudentReadingLog;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RiwayatBacaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('book.cover_image')
                    ->label('Sampul')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl('/images/default-book-cover.png'),

                TextColumn::make('book.title')
                    ->label('Judul Buku')
                    ->weight('bold')
                    ->searchable()
                    ->description(fn (StudentReadingLog $record) => $record->lastChapter ? "Bab: {$record->lastChapter->title}" : null),

                TextColumn::make('book.grade.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),

                TextColumn::make('progress_percent')
                    ->label('Progress Baca')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'info',
                        default => 'warning',
                    })
                    ->sortable(),

                IconColumn::make('is_completed')
                    ->label('Selesai?')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-arrow-path')
                    ->trueColor('success')
                    ->falseColor('warning'),

                TextColumn::make('last_read_at')
                    ->label('Terakhir Dibaca')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('last_read_at', 'desc')
            ->recordActions([
                Action::make('continue')
                    ->label('Lanjutkan Membaca')
                    ->icon('heroicon-m-book-open')
                    ->color('primary')
                    ->button()
                    ->url(fn (StudentReadingLog $record): string => route('books.read', $record->book->slug)),
            ]);
    }
}
