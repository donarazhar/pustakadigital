<?php

namespace App\Filament\Admin\Resources\ReadingAssignments\Pages;

use App\Filament\Admin\Resources\ReadingAssignments\ReadingAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReadingAssignments extends ListRecords
{
    protected static string $resource = ReadingAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Penugasan Baru')
                ->icon('heroicon-m-plus'),
        ];
    }
}
