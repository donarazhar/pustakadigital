<?php

namespace App\Filament\Admin\Resources\Chapters\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesRelationManager extends RelationManager
{
    protected static string $relationship = 'pages';

    protected static ?string $title = 'Halaman Interaktif';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('page_number')
                            ->label('Nomor Halaman')
                            ->required()
                            ->numeric()
                            ->default(1),
                        TextInput::make('title')
                            ->label('Judul / Sub-topik Halaman'),
                    ]),

                RichEditor::make('content')
                    ->label('Konten Pembelajaran Interaktif (Teks, Poin & Penjelasan)')
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])
                    ->columnSpanFull(),

                Grid::make(3)
                    ->schema([
                        FileUpload::make('featured_image')
                            ->label('Ilustrasi / Gambar')
                            ->image()
                            ->directory('pages/images')
                            ->disk('public'),
                        TextInput::make('audio_narration_url')
                            ->label('URL Audio Narasi (MP3 / Audio URL)')
                            ->placeholder('https://.../narasi.mp3'),
                        TextInput::make('video_embed_url')
                            ->label('URL Video Edukasi (YouTube / Video)')
                            ->placeholder('https://www.youtube.com/watch?v=...'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('page_number', 'asc')
            ->columns([
                TextColumn::make('page_number')
                    ->label('Hal')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                ImageColumn::make('featured_image')
                    ->label('Ilustrasi')
                    ->disk('public')
                    ->square(),
                TextColumn::make('title')
                    ->label('Judul Halaman')
                    ->searchable()
                    ->weight('bold')
                    ->placeholder('(Tanpa Judul)'),
                TextColumn::make('audio_narration_url')
                    ->label('Audio Narasi')
                    ->formatStateUsing(fn ($state) => $state ? '🔊 Tersedia' : '-')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                TextColumn::make('video_embed_url')
                    ->label('Video')
                    ->formatStateUsing(fn ($state) => $state ? '🎥 Tersedia' : '-')
                    ->color(fn ($state) => $state ? 'info' : 'gray'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Halaman'),
            ])
            ->recordActions([
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
