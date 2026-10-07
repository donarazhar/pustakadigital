<?php

namespace App\Filament\Siswa\Resources\QuizAttempts\Pages;

use App\Filament\Siswa\Resources\QuizAttempts\NilaiKuisResource;
use Filament\Resources\Pages\ListRecords;

class ListNilaiKuis extends ListRecords
{
    protected static string $resource = NilaiKuisResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
