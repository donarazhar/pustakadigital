<?php

namespace App\Filament\Admin\Resources\Quizzes\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Daftar Pertanyaan & Pilihan Jawaban';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('question_text')
                    ->label('Kalimat Soal / Pertanyaan')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Grid::make(3)
                    ->schema([
                        Select::make('question_type')
                            ->label('Tipe Soal')
                            ->options([
                                'multiple_choice' => 'Pilihan Ganda',
                                'true_false' => 'Benar / Salah',
                            ])
                            ->default('multiple_choice')
                            ->required(),

                        TextInput::make('score_weight')
                            ->label('Bobot Nilai')
                            ->numeric()
                            ->default(10)
                            ->required(),

                        TextInput::make('order')
                            ->label('Urutan Soal')
                            ->numeric()
                            ->default(1)
                            ->required(),
                    ]),

                FileUpload::make('question_image')
                    ->label('Gambar Pendukung Soal (Opsional)')
                    ->image()
                    ->directory('quizzes/questions')
                    ->disk('public'),

                Textarea::make('explanation')
                    ->label('Pembahasan / Penjelasan Jawaban')
                    ->placeholder('Penjelasan yang akan muncul setelah siswa menjawab...')
                    ->columnSpanFull(),

                Repeater::make('options')
                    ->label('Pilihan Jawaban')
                    ->relationship('options')
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                TextInput::make('option_text')
                                    ->label('Teks Pilihan')
                                    ->required()
                                    ->columnSpan(9),
                                Toggle::make('is_correct')
                                    ->label('Kunci Benar')
                                    ->inline(false)
                                    ->columnSpan(3),
                            ]),
                    ])
                    ->defaultItems(4)
                    ->orderColumn('order')
                    ->reorderable()
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_text')
            ->defaultSort('order', 'asc')
            ->columns([
                TextColumn::make('order')
                    ->label('No')
                    ->badge()
                    ->sortable(),
                ImageColumn::make('question_image')
                    ->label('Gambar')
                    ->disk('public')
                    ->square(),
                TextColumn::make('question_text')
                    ->label('Pertanyaan')
                    ->searchable()
                    ->limit(60)
                    ->weight('bold'),
                TextColumn::make('options_count')
                    ->label('Jml Pilihan')
                    ->counts('options')
                    ->badge()
                    ->color('info'),
                TextColumn::make('score_weight')
                    ->label('Bobot')
                    ->suffix(' Poin')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Pertanyaan'),
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
