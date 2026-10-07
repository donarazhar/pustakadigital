<?php

namespace App\Filament\Admin\Resources\Subjects\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code'),
                TextInput::make('icon'),
                TextInput::make('color'),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
