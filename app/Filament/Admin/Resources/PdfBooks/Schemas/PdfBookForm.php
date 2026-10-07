<?php

namespace App\Filament\Admin\Resources\PdfBooks\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PdfBookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Berkas Dokumen PDF & Sampul')
                    ->description('Unggah berkas buku PDF lengkap yang sudah mencakup bab, teks, dan halaman')
                    ->schema([
                        FileUpload::make('pdf_file')
                            ->label('Berkas Buku PDF (Lengkap)')
                            ->helperText('Format PDF, maksimal 100 MB. Buku ini siap dibaca langsung oleh siswa.')
                            ->disk('public')
                            ->directory('books/pdfs')
                            ->acceptedFileTypes(['application/pdf'])
                            ->required()
                            ->openable()
                            ->downloadable()
                            ->maxSize(102400)
                            ->columnSpanFull(),

                        FileUpload::make('cover_image')
                            ->label('Gambar Sampul Depan (Cover)')
                            ->helperText('Format gambar (JPG, PNG, WebP). Tampil sebagai thumbnail di katalog dan rak buku.')
                            ->image()
                            ->disk('public')
                            ->directory('books/covers')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('3:4')
                            ->columnSpanFull(),
                    ]),

                Section::make('Informasi Buku PDF')
                    ->description('Masukkan judul dan kurikulum untuk memudahkan pencarian siswa')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Buku')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state) . '-' . Str::lower(Str::random(4)))),

                        TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Grid::make(4)
                            ->schema([
                                Select::make('grade_id')
                                    ->label('Jenjang / Kelas')
                                    ->relationship('grade', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('program_id')
                                    ->label('Program Belajar')
                                    ->relationship('program', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Umum (Semua Program)'),

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
                            ->label('Sinopsis / Deskripsi Singkat')
                            ->placeholder('Tuliskan ringkasan materi atau sinopsis buku PDF ini...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Metadata Penerbit & Pengaturan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('author')
                                    ->label('Penulis / Penyusun')
                                    ->maxLength(255),

                                TextInput::make('publisher')
                                    ->label('Penerbit')
                                    ->maxLength(255),

                                TextInput::make('publication_year')
                                    ->label('Tahun Terbit')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(2100),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('isbn')
                                    ->label('Nomor ISBN')
                                    ->maxLength(50),

                                TextInput::make('total_pages')
                                    ->label('Jumlah Halaman PDF')
                                    ->numeric()
                                    ->placeholder('Misal: 120')
                                    ->suffix(' Halaman'),

                                TextInput::make('estimated_read_time')
                                    ->label('Estimasi Waktu Baca')
                                    ->numeric()
                                    ->default(20)
                                    ->suffix(' Menit'),
                            ]),

                        Toggle::make('is_published')
                            ->label('Publikasikan Buku PDF Ini')
                            ->helperText('Buku dapat langsung ditemukan dan dibaca oleh siswa di perpustakaan')
                            ->default(true),
                    ]),
            ]);
    }
}
