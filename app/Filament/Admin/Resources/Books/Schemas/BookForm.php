<?php

namespace App\Filament\Admin\Resources\Books\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Buku')
                    ->description('Masukkan data utama buku digital')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Buku')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Grid::make(3)
                            ->schema([
                                Select::make('grade_id')
                                    ->label('Jenjang / Kelas')
                                    ->relationship('grade', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('subject_id')
                                    ->label('Mata Pelajaran')
                                    ->relationship('subject', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('category_id')
                                    ->label('Kategori Buku')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),

                        RichEditor::make('description')
                            ->label('Sinopsis / Deskripsi Buku')
                            ->columnSpanFull(),
                    ]),

                Section::make('Sampul & Penerbitan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                FileUpload::make('cover_image')
                                    ->label('Sampul Buku (Cover)')
                                    ->image()
                                    ->directory('books/covers')
                                    ->disk('public')
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('3:4'),

                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('author')
                                            ->label('Penulis / Pengarang'),
                                        TextInput::make('publisher')
                                            ->label('Penerbit'),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('publication_year')
                                                    ->label('Tahun Terbit')
                                                    ->numeric()
                                                    ->default(date('Y')),
                                                TextInput::make('isbn')
                                                    ->label('ISBN'),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('estimated_read_time')
                                                    ->label('Estimasi Baca (Menit)')
                                                    ->numeric()
                                                    ->default(15)
                                                    ->required(),
                                                Toggle::make('is_published')
                                                    ->label('Publikasikan ke Siswa')
                                                    ->default(true)
                                                    ->inline(false),
                                            ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
