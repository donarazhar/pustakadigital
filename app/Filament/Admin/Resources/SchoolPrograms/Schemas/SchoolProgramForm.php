<?php

namespace App\Filament\Admin\Resources\SchoolPrograms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SchoolProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Program Sekolah')
                    ->description('Kelola peminatan, program khusus, atau jalur kurikulum sekolah (contoh: Bilingual, Tahfizh, Reguler)')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Program')
                                    ->placeholder('contoh: Program Bilingual (Cambridge)')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('code')
                                    ->label('Kode Singkat')
                                    ->placeholder('contoh: BIL, TFZ, REG')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Select::make('level')
                                    ->label('Jenjang Sasaran')
                                    ->options([
                                        'ALL' => 'Semua Jenjang (SD, SMP, SMA)',
                                        'SD' => 'Khusus SD (Sekolah Dasar)',
                                        'SMP' => 'Khusus SMP (Menengah Pertama)',
                                        'SMA' => 'Khusus SMA/SMK (Menengah Atas)',
                                    ])
                                    ->default('ALL')
                                    ->required(),

                                TextInput::make('icon')
                                    ->label('Ikon Emoji / Simbol')
                                    ->placeholder('contoh: 🌐, 📖, 🔬, 🌱')
                                    ->default('🎓'),

                                TextInput::make('color')
                                    ->label('Kode Warna HEX')
                                    ->placeholder('#3b82f6')
                                    ->default('#3b82f6'),
                            ]),

                        Textarea::make('description')
                            ->label('Deskripsi & Fokus Kurikulum')
                            ->placeholder('Jelaskan sasaran pembelajaran, kurikulum pendamping, atau target lulusan program ini...')
                            ->columnSpanFull()
                            ->rows(3),

                        Toggle::make('is_active')
                            ->label('Status Program Aktif')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }
}
