<?php

namespace App\Filament\Admin\Resources\Books\Pages;

use App\Filament\Admin\Resources\Books\BookResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBook extends CreateRecord
{
    protected static string $resource = BookResource::class;
}
