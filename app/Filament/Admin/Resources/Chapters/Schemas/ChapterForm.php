<?php

namespace App\Filament\Admin\Resources\Chapters\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ChapterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('book_id')
                    ->relationship('book', 'title')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('chapter_number')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('summary')
                    ->columnSpanFull(),
            ]);
    }
}
