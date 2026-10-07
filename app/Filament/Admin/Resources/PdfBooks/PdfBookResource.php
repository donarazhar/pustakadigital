<?php

namespace App\Filament\Admin\Resources\PdfBooks;

use App\Filament\Admin\Resources\PdfBooks\Pages\CreatePdfBook;
use App\Filament\Admin\Resources\PdfBooks\Pages\EditPdfBook;
use App\Filament\Admin\Resources\PdfBooks\Pages\ListPdfBooks;
use App\Filament\Admin\Resources\PdfBooks\Schemas\PdfBookForm;
use App\Filament\Admin\Resources\PdfBooks\Tables\PdfBooksTable;
use App\Models\Book;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PdfBookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowUp;

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog & Konten';

    protected static ?string $navigationLabel = 'Buku PDF (E-Book)';

    protected static ?string $modelLabel = 'Buku PDF';

    protected static ?string $pluralModelLabel = 'Buku PDF (E-Book)';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $slug = 'buku-pdf';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('book_type', 'pdf');
    }

    public static function form(Schema $schema): Schema
    {
        return PdfBookForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PdfBooksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPdfBooks::route('/'),
            'create' => CreatePdfBook::route('/create'),
            'edit' => EditPdfBook::route('/{record}/edit'),
        ];
    }
}
