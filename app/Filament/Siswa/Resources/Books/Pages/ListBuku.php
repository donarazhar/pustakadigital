<?php

namespace App\Filament\Siswa\Resources\Books\Pages;

use App\Filament\Siswa\Resources\Books\BukuResource;
use Filament\Resources\Pages\ListRecords;

class ListBuku extends ListRecords
{
    protected static string $resource = BukuResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
