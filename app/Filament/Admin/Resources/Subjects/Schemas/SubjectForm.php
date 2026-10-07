<?php

namespace App\Filament\Admin\Resources\Subjects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Mata Pelajaran')
                    ->description('Tentukan kurikulum, jenjang, dan program spesifik mata pelajaran ini')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Mata Pelajaran')
                                    ->placeholder('contoh: Cambridge Science, Tahfizh Al-Qur\'an, Matematika')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('code')
                                    ->label('Kode Pelajaran')
                                    ->placeholder('contoh: SCI-BIL, TFZ-30, MTK')
                                    ->maxLength(50),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('program_id')
                                    ->label('Program Sekolah (Peminatan)')
                                    ->relationship('program', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Umum / Semua Program (Bisa diambil semua murid)')
                                    ->helperText('Kosongkan jika mata pelajaran ini berlaku umum untuk seluruh program.'),

                                Select::make('level')
                                    ->label('Jenjang Sekolah')
                                    ->options([
                                        'ALL' => 'Semua Jenjang (SD, SMP, SMA)',
                                        'SD' => 'Sekolah Dasar (SD)',
                                        'SMP' => 'Sekolah Menengah Pertama (SMP)',
                                        'SMA' => 'Sekolah Menengah Atas / SMK (SMA)',
                                    ])
                                    ->default('ALL')
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('icon')
                                    ->label('Ikon Simbol / Emoji')
                                    ->placeholder('contoh: 🔬, 📖, 📐, 🌐'),

                                TextInput::make('color')
                                    ->label('Kode Warna HEX')
                                    ->placeholder('#10b981')
                                    ->default('#4f46e5'),
                            ]),

                        Textarea::make('description')
                            ->label('Deskripsi & Capaian Pembelajaran')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }
}
