<?php

namespace App\Filament\Admin\Resources\SchoolPrograms\Pages;

use App\Filament\Admin\Resources\SchoolPrograms\SchoolProgramResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSchoolProgram extends EditRecord
{
    protected static string $resource = SchoolProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
