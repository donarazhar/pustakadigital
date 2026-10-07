<?php

namespace App\Filament\Admin\Resources\PdfBooks\Tables;

use App\Models\Book;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PdfBooksTable
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
                    ->label('Judul Buku PDF')
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
                    ->color('primary')
                    ->placeholder('Semua Program')
                    ->sortable(),

                TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('total_pages')
                    ->label('Jml Hal')
                    ->suffix(' hal')
                    ->badge()
                    ->color('warning')
                    ->default('-')
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('view_count')
                    ->label('Dibaca')
                    ->suffix('x')
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
                    ->label('Filter Mapel')
                    ->relationship('subject', 'name'),

                SelectFilter::make('category_id')
                    ->label('Filter Kategori')
                    ->relationship('category', 'name'),
            ])
            ->recordActions([
                Action::make('read')
                    ->label('Baca PDF')
                    ->icon('heroicon-m-book-open')
                    ->color('primary')
                    ->url(fn (Book $record): string => route('books.read', $record->slug))
                    ->openUrlInNewTab(),

                Action::make('download')
                    ->label('Unduh')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Book $record): ?string => $record->pdf_file ? asset('storage/' . $record->pdf_file) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (Book $record): bool => !empty($record->pdf_file)),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
