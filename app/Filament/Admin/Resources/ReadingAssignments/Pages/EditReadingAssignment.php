<?php

namespace App\Filament\Admin\Resources\ReadingAssignments\Pages;

use App\Filament\Admin\Resources\ReadingAssignments\ReadingAssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReadingAssignment extends EditRecord
{
    protected static string $resource = ReadingAssignmentResource::class;

    protected static ?string $title = 'Ubah Penugasan Membaca';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
