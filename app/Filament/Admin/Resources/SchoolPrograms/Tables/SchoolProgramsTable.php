<?php

namespace App\Filament\Admin\Resources\SchoolPrograms\Tables;

use App\Models\SchoolProgram;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SchoolProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('icon')
                    ->label('')
                    ->width('40px')
                    ->alignCenter(),

                TextColumn::make('name')
                    ->label('Nama Program')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (SchoolProgram $record): string => "Kode: {$record->code}"),

                TextColumn::make('level')
                    ->label('Jenjang')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'SD' => 'Khusus SD',
                        'SMP' => 'Khusus SMP',
                        'SMA' => 'Khusus SMA/SMK',
                        default => 'Semua Jenjang',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'SD' => 'success',
                        'SMP' => 'warning',
                        'SMA' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('students_count')
                    ->label('Murid Terdaftar')
                    ->counts('students')
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),

                TextColumn::make('subjects_count')
                    ->label('Mata Pelajaran')
                    ->counts('subjects')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('books_count')
                    ->label('Buku Khusus')
                    ->counts('books')
                    ->badge()
                    ->color('warning')
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('level')
                    ->label('Jenjang Sasaran')
                    ->options([
                        'ALL' => 'Semua Jenjang',
                        'SD' => 'Khusus SD',
                        'SMP' => 'Khusus SMP',
                        'SMA' => 'Khusus SMA',
                    ]),
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
