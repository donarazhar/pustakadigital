<?php

namespace App\Filament\Siswa\Resources\TugasMembaca\Tables;

use App\Models\ReadingAssignment;
use Filament\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TugasMembacaTable
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

                TextColumn::make('title')
                    ->label('Judul Penugasan')
                    ->weight('bold')
                    ->searchable()
                    ->description(fn (ReadingAssignment $record) => $record->teacher ? "Ditugaskan oleh: {$record->teacher->name}" : null),

                TextColumn::make('book.title')
                    ->label('Buku Digital')
                    ->searchable()
                    ->sortable()
                    ->limit(28),

                TextColumn::make('target_chapter')
                    ->label('Target Bab')
                    ->state(fn (ReadingAssignment $record) => $record->targetChapter ? $record->targetChapter->title : 'Seluruh Isi Buku')
                    ->badge()
                    ->color(fn (ReadingAssignment $record) => $record->targetChapter ? 'info' : 'gray'),

                TextColumn::make('status_siswa')
                    ->label('Status Membaca')
                    ->state(function (ReadingAssignment $record): string {
                        $pivot = $record->assignmentStudents->firstWhere('student_id', Auth::id());
                        $status = $pivot->status ?? 'assigned';
                        $percent = $pivot->progress_percent ?? 0;

                        return match ($status) {
                            'completed' => '✅ Selesai (100%)',
                            'in_progress' => "⏳ Sedang Dibaca ({$percent}%)",
                            default => '⚪ Belum Dibaca (0%)',
                        };
                    })
                    ->badge()
                    ->color(function (ReadingAssignment $record): string {
                        $pivot = $record->assignmentStudents->firstWhere('student_id', Auth::id());
                        $status = $pivot->status ?? 'assigned';

                        return match ($status) {
                            'completed' => 'success',
                            'in_progress' => 'warning',
                            default => 'gray',
                        };
                    }),

                TextColumn::make('due_date')
                    ->label('Tenggat Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->color(function (ReadingAssignment $record): string {
                        $pivot = $record->assignmentStudents->firstWhere('student_id', Auth::id());
                        $isCompleted = ($pivot->status ?? '') === 'completed';

                        return (! $isCompleted && $record->isOverdue()) ? 'danger' : 'gray';
                    })
                    ->description(function (ReadingAssignment $record): ?string {
                        $pivot = $record->assignmentStudents->firstWhere('student_id', Auth::id());
                        $isCompleted = ($pivot->status ?? '') === 'completed';

                        if (! $isCompleted && $record->isOverdue()) {
                            return '⚠️ Terlewat Deadline';
                        }
                        if ($record->due_date && ! $isCompleted) {
                            return $record->due_date->diffForHumans();
                        }
                        return null;
                    }),
            ])
            ->filters([
                SelectFilter::make('book_id')
                    ->label('Filter Buku')
                    ->relationship('book', 'title'),
            ])
            ->recordActions([
                Action::make('read')
                    ->label(function (ReadingAssignment $record): string {
                        $pivot = $record->assignmentStudents->firstWhere('student_id', Auth::id());
                        $status = $pivot->status ?? 'assigned';

                        return match ($status) {
                            'completed' => 'Baca Ulang',
                            'in_progress' => 'Lanjutkan Baca',
                            default => 'Mulai Baca 📖',
                        };
                    })
                    ->icon('heroicon-m-book-open')
                    ->color('primary')
                    ->button()
                    ->url(function (ReadingAssignment $record): string {
                        if ($record->target_chapter_id) {
                            return route('books.read.chapter', [
                                'book' => $record->book->slug,
                                'chapter' => $record->target_chapter_id,
                            ]);
                        }

                        return route('books.read', $record->book->slug);
                    }),

                Action::make('detail')
                    ->label('Instruksi')
                    ->icon('heroicon-m-information-circle')
                    ->color('gray')
                    ->modalHeading(fn (ReadingAssignment $record) => "📋 {$record->title}")
                    ->modalContent(fn (ReadingAssignment $record) => view('filament.siswa.tugas-detail-modal', ['assignment' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ]);
    }
}
