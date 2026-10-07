<?php

namespace App\Filament\Siswa\Resources\CatatanRangkuman\Tables;

use App\Models\StudentBookAnnotation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CatatanRangkumanTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('color')
                    ->label('Warna')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'green' => '🟢 Hijau',
                        'blue' => '🔵 Biru',
                        'orange' => '🟠 Oranye',
                        'purple' => '🟣 Ungu',
                        'pink' => '🌸 Pink',
                        default => '🟡 Kuning',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'green' => 'success',
                        'blue' => 'info',
                        'orange' => 'warning',
                        'purple' => 'gray',
                        default => 'warning',
                    }),

                TextColumn::make('book.title')
                    ->label('Buku Digital')
                    ->weight('bold')
                    ->searchable()
                    ->limit(26),

                TextColumn::make('lokasi')
                    ->label('Bab & Halaman')
                    ->state(function (StudentBookAnnotation $record): string {
                        $bab = $record->chapter ? "Bab {$record->chapter->chapter_number}" : '';
                        $hal = $record->page ? "Hal {$record->page->page_number}" : '';
                        return trim("{$bab} • {$hal}", ' •') ?: 'Buku';
                    })
                    ->badge()
                    ->color('gray'),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sticky_note' => '📌 Catatan',
                        'highlight_note' => '🎨 Stabilo + Catatan',
                        default => '🖍️ Stabilo',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sticky_note' => 'primary',
                        'highlight_note' => 'success',
                        default => 'warning',
                    }),

                TextColumn::make('highlighted_text')
                    ->label('Kutipan Teks Penting')
                    ->formatStateUsing(fn (?string $state): string => $state ? "\"{$state}\"" : '-')
                    ->limit(45)
                    ->searchable(),

                TextColumn::make('note')
                    ->label('Catatan Rangkuman')
                    ->placeholder('-')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Ditandai Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('book_id')
                    ->label('Filter Buku')
                    ->relationship('book', 'title'),

                SelectFilter::make('color')
                    ->label('Filter Warna')
                    ->options([
                        'yellow' => '🟡 Kuning',
                        'green' => '🟢 Hijau',
                        'blue' => '🔵 Biru',
                        'orange' => '🟠 Oranye',
                        'purple' => '🟣 Ungu',
                        'pink' => '🌸 Pink',
                    ]),
            ])
            ->recordActions([
                Action::make('read')
                    ->label('Buka Buku')
                    ->icon('heroicon-m-book-open')
                    ->color('primary')
                    ->button()
                    ->url(function (StudentBookAnnotation $record): string {
                        if ($record->chapter_id) {
                            return route('books.read.chapter', [
                                'book' => $record->book->slug,
                                'chapter' => $record->chapter_id,
                            ]);
                        }
                        return route('books.read', $record->book->slug);
                    }),

                Action::make('detail')
                    ->label('Lihat Catatan')
                    ->icon('heroicon-m-document-text')
                    ->color('gray')
                    ->modalHeading(fn (StudentBookAnnotation $record) => "Catatan: {$record->book->title}")
                    ->modalDescription(fn (StudentBookAnnotation $record) => ($record->chapter ? $record->chapter->title : '') . ($record->page ? " • Halaman {$record->page->page_number}" : ''))
                    ->modalContent(function (StudentBookAnnotation $record) {
                        return view('filament.siswa.annotation-detail-modal', ['annotation' => $record]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Stabilo & Catatan')
                    ->modalDescription('Apakah Anda yakin ingin menghapus stabilo/catatan ini?'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
