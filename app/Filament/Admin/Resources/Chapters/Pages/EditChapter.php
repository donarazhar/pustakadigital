<?php

namespace App\Filament\Admin\Resources\Chapters\Pages;

use App\Filament\Admin\Resources\Chapters\ChapterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChapter extends EditRecord
{
    protected static string $resource = ChapterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
