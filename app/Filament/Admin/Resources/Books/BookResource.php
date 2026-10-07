<?php

namespace App\Filament\Admin\Resources\Books;

use App\Filament\Admin\Resources\Books\Pages\CreateBook;
use App\Filament\Admin\Resources\Books\Pages\EditBook;
use App\Filament\Admin\Resources\Books\Pages\ListBooks;
use App\Filament\Admin\Resources\Books\RelationManagers\ChaptersRelationManager;
use App\Filament\Admin\Resources\Books\Schemas\BookForm;
use App\Filament\Admin\Resources\Books\Tables\BooksTable;
use App\Models\Book;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog & Konten';

    protected static ?string $navigationLabel = 'Buku Interaktif 3D';

    protected static ?string $modelLabel = 'Buku Interaktif';

    protected static ?string $pluralModelLabel = 'Buku Interaktif 3D';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($q) {
                $q->where('book_type', 'interactive')
                    ->orWhereNull('book_type');
            });
    }

    public static function form(Schema $schema): Schema
    {
        return BookForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BooksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ChaptersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBooks::route('/'),
            'create' => CreateBook::route('/create'),
            'edit' => EditBook::route('/{record}/edit'),
        ];
    }
}
