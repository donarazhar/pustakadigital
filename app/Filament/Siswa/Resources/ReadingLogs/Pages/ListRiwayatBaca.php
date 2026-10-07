<?php

namespace App\Filament\Siswa\Resources\ReadingLogs\Pages;

use App\Filament\Siswa\Resources\ReadingLogs\RiwayatBacaResource;
use Filament\Resources\Pages\ListRecords;

class ListRiwayatBaca extends ListRecords
{
    protected static string $resource = RiwayatBacaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
