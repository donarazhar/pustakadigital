<?php

namespace App\Filament\Siswa\Resources\QuizAttempts\Tables;

use App\Models\StudentQuizAttempt;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NilaiKuisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quiz.title')
                    ->label('Judul Evaluasi / Kuis')
                    ->weight('bold')
                    ->searchable()
                    ->description(fn (StudentQuizAttempt $record) => $record->quiz?->chapter?->book?->title ? "Buku: {$record->quiz->chapter->book->title} (Bab {$record->quiz->chapter->chapter_number})" : null),

                TextColumn::make('score')
                    ->label('Skor Perolehan')
                    ->numeric()
                    ->suffix(' / 100')
                    ->badge()
                    ->color(fn (StudentQuizAttempt $record): string => $record->is_passed ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('correct_answers')
                    ->label('Ketepatan Jawaban')
                    ->formatStateUsing(fn (StudentQuizAttempt $record): string => "{$record->correct_answers} dari {$record->total_questions} soal")
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_passed')
                    ->label('Kelulusan')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('submitted_at')
                    ->label('Waktu Pengiriman')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}
