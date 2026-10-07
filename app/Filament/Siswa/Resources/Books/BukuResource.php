<?php

namespace App\Filament\Siswa\Resources\Books;

use App\Filament\Siswa\Resources\Books\Pages\ListBuku;
use App\Filament\Siswa\Resources\Books\Tables\BukuTable;
use App\Models\Book;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BukuResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Belajar & Membaca';

    protected static ?string $navigationLabel = 'Katalog Buku';

    protected static ?string $modelLabel = 'Buku';

    protected static ?string $pluralModelLabel = 'Katalog Buku';

    protected static ?string $slug = 'katalog-buku';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_published', true)
            ->with(['grade', 'subject', 'category', 'chapters']);
    }

    public static function table(Table $table): Table
    {
        return BukuTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBuku::route('/'),
        ];
    }
}
