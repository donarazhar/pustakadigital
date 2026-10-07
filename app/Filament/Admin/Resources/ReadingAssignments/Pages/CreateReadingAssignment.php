<?php

namespace App\Filament\Admin\Resources\ReadingAssignments\Pages;

use App\Filament\Admin\Resources\ReadingAssignments\ReadingAssignmentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateReadingAssignment extends CreateRecord
{
    protected static string $resource = ReadingAssignmentResource::class;

    protected static ?string $title = 'Buat Penugasan Membaca';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['teacher_id'])) {
            $data['teacher_id'] = Auth::id() ?? 1;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
