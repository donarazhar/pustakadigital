<?php

namespace App\Filament\Siswa\Resources\CatatanRangkuman\Pages;

use App\Filament\Siswa\Resources\CatatanRangkuman\CatatanRangkumanResource;
use Filament\Resources\Pages\ListRecords;

class ListCatatanRangkuman extends ListRecords
{
    protected static string $resource = CatatanRangkumanResource::class;

    protected static ?string $title = 'Daftar Stabilo & Catatan Rangkuman';
}
