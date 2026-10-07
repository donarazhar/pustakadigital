<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Foto')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl('https://ui-avatars.com/api/?background=6366f1&color=fff&name=User'),

                TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'admin' => 'Admin',
                        'teacher' => 'Guru',
                        'student' => 'Siswa',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'admin' => 'danger',
                        'teacher' => 'warning',
                        'student' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('grade.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Filter Peran')
                    ->options([
                        'admin' => 'Admin',
                        'teacher' => 'Guru',
                        'student' => 'Siswa',
                    ]),
                SelectFilter::make('grade_id')
                    ->label('Filter Kelas')
                    ->relationship('grade', 'name'),
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
