<?php

namespace App\Filament\Admin\Resources\PdfBooks\Pages;

use App\Filament\Admin\Resources\PdfBooks\PdfBookResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPdfBooks extends ListRecords
{
    protected static string $resource = PdfBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Upload Buku PDF Baru'),
        ];
    }
}
