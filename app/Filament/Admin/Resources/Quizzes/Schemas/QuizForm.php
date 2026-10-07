<?php

namespace App\Filament\Admin\Resources\Quizzes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuizForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('chapter_id')
                    ->relationship('chapter', 'title')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('passing_score')
                    ->required()
                    ->numeric()
                    ->default(70),
                TextInput::make('time_limit_minutes')
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
