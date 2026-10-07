<?php

namespace App\Filament\Admin\Resources\SchoolPrograms\Pages;

use App\Filament\Admin\Resources\SchoolPrograms\SchoolProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSchoolPrograms extends ListRecords
{
    protected static string $resource = SchoolProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Program Baru'),
        ];
    }
}
