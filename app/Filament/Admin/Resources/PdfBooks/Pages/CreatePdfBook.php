<?php

namespace App\Filament\Admin\Resources\PdfBooks\Pages;

use App\Filament\Admin\Resources\PdfBooks\PdfBookResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePdfBook extends CreateRecord
{
    protected static string $resource = PdfBookResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['book_type'] = 'pdf';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
