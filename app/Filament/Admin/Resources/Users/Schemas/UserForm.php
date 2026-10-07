<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun Pengguna')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Select::make('role')
                                    ->label('Peran / Hak Akses')
                                    ->options([
                                        'admin' => 'Administrator',
                                        'teacher' => 'Guru / Pustakawan',
                                        'student' => 'Siswa / Pelajar',
                                    ])
                                    ->default('student')
                                    ->required()
                                    ->live(),

                                Select::make('grade_id')
                                    ->label('Tingkat Kelas (Khusus Siswa)')
                                    ->relationship('grade', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn ($get) => $get('role') === 'student'),

                                Select::make('program_id')
                                    ->label('Program Sekolah (Khusus Siswa)')
                                    ->relationship('program', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih Program (Bilingual, Tahfizh, dll.)')
                                    ->visible(fn ($get) => $get('role') === 'student'),
                            ]),

                        TextInput::make('password')
                            ->label('Kata Sandi')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->helperText('Kosongkan jika tidak ingin mengubah kata sandi'),

                        FileUpload::make('avatar')
                            ->label('Foto Profil / Avatar')
                            ->image()
                            ->avatar()
                            ->directory('avatars')
                            ->disk('public'),
                    ]),
            ]);
    }
}
