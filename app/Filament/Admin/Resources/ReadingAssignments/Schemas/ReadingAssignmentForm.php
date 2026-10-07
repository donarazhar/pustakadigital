<?php

namespace App\Filament\Admin\Resources\ReadingAssignments\Schemas;

use App\Models\Chapter;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReadingAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penugasan')
                    ->description('Tentukan judul, instruksi, dan tenggat waktu penugasan membaca.')
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul Penugasan')
                                    ->placeholder('Contoh: Membaca Bab 2 Ekosistem Hutan')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(8),

                                DateTimePicker::make('due_date')
                                    ->label('Tenggat Waktu (Deadline)')
                                    ->prefixIcon('heroicon-m-calendar-days')
                                    ->native(false)
                                    ->columnSpan(4),
                            ]),

                        Textarea::make('description')
                            ->label('Petunjuk & Instruksi Guru')
                            ->placeholder('Tuliskan catatan tambahan atau instruksi untuk siswa (misal: "Pahami materi rantai makanan di halaman 5-10 sebelum kuis hari Jumat")...')
                            ->rows(3)
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Aktifkan Penugasan')
                            ->helperText('Jika non-aktif, tugas tidak akan muncul di daftar tugas siswa.')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),

                Section::make('Buku & Materi Target')
                    ->description('Pilih buku digital dan bab sasaran yang wajib dibaca siswa.')
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                Select::make('book_id')
                                    ->label('Pilih Buku Digital')
                                    ->relationship('book', 'title')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (callable $set) => $set('target_chapter_id', null))
                                    ->columnSpan(6),

                                Select::make('target_chapter_id')
                                    ->label('Target Bab Spesifik (Opsional)')
                                    ->placeholder('Seluruh Isi Buku (Semua Bab)')
                                    ->options(function (callable $get) {
                                        $bookId = $get('book_id');
                                        if (! $bookId) {
                                            return [];
                                        }

                                        return Chapter::where('book_id', $bookId)
                                            ->orderBy('order')
                                            ->pluck('title', 'id');
                                    })
                                    ->searchable()
                                    ->helperText('Kosongkan jika siswa ditugaskan membaca keseluruhan buku.')
                                    ->columnSpan(6),
                            ]),
                    ]),

                Section::make('Sasaran Kelas & Siswa')
                    ->description('Pilih jenjang kelas atau centang langsung siswa yang ditugaskan.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('grade_id')
                                    ->label('Filter Jenjang / Kelas')
                                    ->relationship('grade', 'name')
                                    ->placeholder('Semua Kelas')
                                    ->live(),

                                Select::make('program_id')
                                    ->label('Filter Program Sekolah')
                                    ->relationship('program', 'name')
                                    ->placeholder('Semua Program (Bilingual, Tahfizh, dll.)')
                                    ->live(),
                            ]),

                        CheckboxList::make('students')
                            ->label('Daftar Siswa yang Ditugaskan')
                            ->relationship('students', 'name', function ($query, callable $get) {
                                $query->where('role', 'student');
                                if ($gradeId = $get('grade_id')) {
                                    $query->where('grade_id', $gradeId);
                                }
                                if ($programId = $get('program_id')) {
                                    $query->where('program_id', $programId);
                                }
                                return $query->orderBy('name');
                            })
                            ->bulkToggleable()
                            ->columns(2)
                            ->required()
                            ->helperText('Centang siswa yang wajib membaca. Klik "Select all / Deselect all" untuk memilih seluruh siswa di kelas sekaligus.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
