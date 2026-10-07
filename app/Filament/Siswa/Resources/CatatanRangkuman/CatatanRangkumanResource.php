<?php

namespace App\Filament\Siswa\Resources\CatatanRangkuman;

use App\Filament\Siswa\Resources\CatatanRangkuman\Pages\ListCatatanRangkuman;
use App\Filament\Siswa\Resources\CatatanRangkuman\Tables\CatatanRangkumanTable;
use App\Models\StudentBookAnnotation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CatatanRangkumanResource extends Resource
{
    protected static ?string $model = StudentBookAnnotation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static string|\UnitEnum|null $navigationGroup = 'Aktivitas Belajar';

    protected static ?string $navigationLabel = 'Catatan & Stabilo';

    protected static ?string $modelLabel = 'Catatan Belajar';

    protected static ?string $pluralModelLabel = 'Catatan & Stabilo Saya';

    protected static ?string $slug = 'catatan-stabilo';

    protected static ?int $navigationSort = 3;

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
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id())
            ->with(['book', 'chapter', 'page'])
            ->latest();
    }

    public static function table(Table $table): Table
    {
        return CatatanRangkumanTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCatatanRangkuman::route('/'),
        ];
    }
}
