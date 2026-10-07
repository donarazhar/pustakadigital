<?php

namespace App\Filament\Admin\Resources\ReadingAssignments\Tables;

use App\Models\ReadingAssignment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReadingAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Penugasan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (ReadingAssignment $record) => $record->teacher ? "Guru: {$record->teacher->name}" : null),

                TextColumn::make('book.title')
                    ->label('Buku Digital')
                    ->searchable()
                    ->sortable()
                    ->limit(28),

                TextColumn::make('target_chapter')
                    ->label('Target Bab')
                    ->state(fn (ReadingAssignment $record) => $record->targetChapter ? $record->targetChapter->title : 'Seluruh Buku')
                    ->badge()
                    ->color(fn (ReadingAssignment $record) => $record->targetChapter ? 'info' : 'gray'),

                TextColumn::make('grade.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('rekap_progres')
                    ->label('Ketuntasan Siswa')
                    ->state(function (ReadingAssignment $record): string {
                        $total = $record->totalStudentsCount();
                        $completed = $record->completedStudentsCount();
                        $rate = $record->completionRate();
                        return "{$completed}/{$total} Selesai ({$rate}%)";
                    })
                    ->badge()
                    ->color(function (ReadingAssignment $record): string {
                        $rate = $record->completionRate();
                        if ($rate >= 100) return 'success';
                        if ($rate > 0) return 'warning';
                        return 'gray';
                    }),

                TextColumn::make('due_date')
                    ->label('Tenggat Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->color(fn (ReadingAssignment $record) => $record->isOverdue() ? 'danger' : 'gray')
                    ->description(fn (ReadingAssignment $record) => $record->isOverdue() ? '⚠️ Lewat Batas Waktu' : null),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('grade_id')
                    ->label('Filter Kelas')
                    ->relationship('grade', 'name'),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                Action::make('rekap')
                    ->label('Rekap Siswa')
                    ->icon('heroicon-m-chart-bar')
                    ->color('info')
                    ->button()
                    ->modalHeading(fn (ReadingAssignment $record) => "📊 Rekap Progres Siswa: {$record->title}")
                    ->modalDescription(fn (ReadingAssignment $record) => "Buku: {$record->book->title}" . ($record->targetChapter ? " | Target: {$record->targetChapter->title}" : " | Target: Seluruh Buku"))
                    ->modalContent(fn (ReadingAssignment $record) => view('filament.admin.reading-assignments.rekap-modal', ['assignment' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
