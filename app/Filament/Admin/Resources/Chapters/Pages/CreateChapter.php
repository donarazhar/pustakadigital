<?php

namespace App\Filament\Admin\Resources\Chapters\Pages;

use App\Filament\Admin\Resources\Chapters\ChapterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChapter extends CreateRecord
{
    protected static string $resource = ChapterResource::class;
}
