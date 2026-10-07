<?php

namespace App\Filament\Admin\Resources\Books\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BooksTable
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
                    ->description(fn ($record) => $record->author ? "Penulis: {$record->author}" : null),

                TextColumn::make('grade.name')
                    ->label('Jenjang/Kelas')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('chapters_count')
                    ->label('Jml Bab')
                    ->counts('chapters')
                    ->badge()
                    ->color('warning'),

                IconColumn::make('is_published')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('estimated_read_time')
                    ->label('Durasi')
                    ->suffix(' mnt')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('view_count')
                    ->label('Dibaca')
                    ->suffix('x')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('grade_id')
                    ->label('Filter Jenjang/Kelas')
                    ->relationship('grade', 'name'),

                SelectFilter::make('subject_id')
                    ->label('Filter Mapel')
                    ->relationship('subject', 'name'),

                SelectFilter::make('category_id')
                    ->label('Filter Kategori')
                    ->relationship('category', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
