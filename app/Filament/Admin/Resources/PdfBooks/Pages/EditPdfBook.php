<?php

namespace App\Filament\Admin\Resources\PdfBooks\Pages;

use App\Filament\Admin\Resources\PdfBooks\PdfBookResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPdfBook extends EditRecord
{
    protected static string $resource = PdfBookResource::class;

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
