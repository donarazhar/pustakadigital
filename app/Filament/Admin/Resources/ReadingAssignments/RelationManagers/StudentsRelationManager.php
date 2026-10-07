<?php

namespace App\Filament\Admin\Resources\ReadingAssignments\RelationManagers;

use App\Models\ReadingAssignmentStudent;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignmentStudents';

    protected static ?string $title = 'Daftar Siswa & Status Penyelesaian';

    protected static ?string $modelLabel = 'Siswa';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->label('Status Membaca')
                    ->options([
                        'assigned' => 'Belum Dibaca',
                        'in_progress' => 'Sedang Membaca',
                        'completed' => 'Selesai Membaca',
                    ])
                    ->required(),

                TextInput::make('progress_percent')
                    ->label('Persentase Progres (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),

                Textarea::make('notes')
                    ->label('Catatan Guru / Catatan Siswa')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('student.name')
            ->columns([
                TextColumn::make('student.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (ReadingAssignmentStudent $record) => $record->student->email ?? null),

                TextColumn::make('student.grade.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Selesai',
                        'in_progress' => 'Sedang Dibaca',
                        default => 'Belum Dibaca',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('progress_percent')
                    ->label('Progres')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'assigned' => 'Belum Dibaca',
                        'in_progress' => 'Sedang Membaca',
                        'completed' => 'Selesai',
                    ]),
            ])
            ->recordActions([
                Action::make('mark_completed')
                    ->label('Tandai Selesai')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Tandai Siswa Selesai Membaca')
                    ->modalDescription('Apakah Anda yakin ingin menandai siswa ini telah menyelesaikan tugas membaca secara manual?')
                    ->action(fn (ReadingAssignmentStudent $record) => $record->markAsCompleted('Diverifikasi selesai manual oleh Guru'))
                    ->visible(fn (ReadingAssignmentStudent $record) => ! $record->isCompleted()),

                EditAction::make(),
                DeleteAction::make()->label('Hapus Siswa'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
