<?php

namespace App\Filament\Siswa\Resources\Books\Tables;

use App\Models\Book;
use Filament\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BukuTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Sampul')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl('/images/default-book-cover.png'),

                TextColumn::make('title')
                    ->label('Judul Buku')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Book $record) => $record->author ? "Penulis: {$record->author}" : null),

                TextColumn::make('grade.name')
                    ->label('Jenjang/Kelas')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('program.name')
                    ->label('Program')
                    ->badge()
                    ->placeholder('Semua Program')
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('book_type')
                    ->label('Format')
                    ->state(fn (Book $record): string => $record->isPdf() ? 'E-Book PDF' : 'Interaktif 3D')
                    ->badge()
                    ->color(fn (Book $record): string => $record->isPdf() ? 'warning' : 'info'),

                TextColumn::make('konten')
                    ->label('Isi Materi')
                    ->state(fn (Book $record): string => $record->isPdf() ? ($record->total_pages ? "{$record->total_pages} Hal" : 'Dokumen PDF') : "{$record->chapters->count()} Bab")
                    ->badge()
                    ->color('gray'),

                TextColumn::make('estimated_read_time')
                    ->label('Estimasi Waktu')
                    ->suffix(' menit')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('grade_id')
                    ->label('Filter Jenjang/Kelas')
                    ->relationship('grade', 'name'),

                SelectFilter::make('program_id')
                    ->label('Filter Program')
                    ->relationship('program', 'name'),

                SelectFilter::make('subject_id')
                    ->label('Filter Mata Pelajaran')
                    ->relationship('subject', 'name'),

                SelectFilter::make('category_id')
                    ->label('Filter Kategori')
                    ->relationship('category', 'name'),
            ])
            ->recordActions([
                Action::make('read')
                    ->label('Mulai Baca')
                    ->icon('heroicon-m-book-open')
                    ->color('primary')
                    ->button()
                    ->url(fn (Book $record): string => route('books.read', $record->slug)),

                Action::make('detail')
                    ->label('Sinopsis')
                    ->icon('heroicon-m-information-circle')
                    ->color('gray')
                    ->modalHeading(fn (Book $record): string => $record->title)
                    ->modalDescription(fn (Book $record): string => 'Penerbit: ' . ($record->publisher ?? '-') . ' | Tahun: ' . ($record->publication_year ?? '-'))
                    ->modalContent(fn (Book $record) => view('filament.siswa.book-detail-modal', ['book' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ]);
    }
}
