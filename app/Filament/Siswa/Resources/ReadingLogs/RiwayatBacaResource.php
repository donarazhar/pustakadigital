<?php

namespace App\Filament\Siswa\Resources\ReadingLogs;

use App\Filament\Siswa\Resources\ReadingLogs\Pages\ListRiwayatBaca;
use App\Filament\Siswa\Resources\ReadingLogs\Tables\RiwayatBacaTable;
use App\Models\StudentReadingLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RiwayatBacaResource extends Resource
{
    protected static ?string $model = StudentReadingLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|\UnitEnum|null $navigationGroup = 'Aktivitas Belajar';

    protected static ?string $navigationLabel = 'Riwayat Membaca';

    protected static ?string $modelLabel = 'Riwayat Membaca';

    protected static ?string $pluralModelLabel = 'Riwayat Membaca';

    protected static ?string $slug = 'riwayat-baca';

    protected static ?int $navigationSort = 2;

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
            ->where('user_id', Auth::id())
            ->with(['book.grade', 'book.subject', 'lastChapter', 'lastPage']);
    }

    public static function table(Table $table): Table
    {
        return RiwayatBacaTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRiwayatBaca::route('/'),
        ];
    }
}
