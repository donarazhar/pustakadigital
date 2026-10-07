<?php

namespace App\Filament\Siswa\Resources\TugasMembaca\Pages;

use App\Filament\Siswa\Resources\TugasMembaca\TugasMembacaResource;
use Filament\Resources\Pages\ListRecords;

class ListTugasMembaca extends ListRecords
{
    protected static string $resource = TugasMembacaResource::class;

    protected static ?string $title = 'Daftar Tugas Membaca Saya';
}
