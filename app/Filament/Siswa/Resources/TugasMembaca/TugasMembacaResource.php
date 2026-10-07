<?php

namespace App\Filament\Siswa\Resources\TugasMembaca;

use App\Filament\Siswa\Resources\TugasMembaca\Pages\ListTugasMembaca;
use App\Filament\Siswa\Resources\TugasMembaca\Tables\TugasMembacaTable;
use App\Models\ReadingAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TugasMembacaResource extends Resource
{
    protected static ?string $model = ReadingAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Belajar & Membaca';

    protected static ?string $navigationLabel = 'Tugas Membaca';

    protected static ?string $modelLabel = 'Tugas Membaca';

    protected static ?string $pluralModelLabel = 'Tugas Membaca Siswa';

    protected static ?string $slug = 'tugas-membaca';

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
            ->where('is_active', true)
            ->whereHas('assignmentStudents', fn ($q) => $q->where('student_id', Auth::id()))
            ->with([
                'book.grade',
                'book.subject',
                'targetChapter',
                'teacher',
                'assignmentStudents' => fn ($q) => $q->where('student_id', Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TugasMembacaTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTugasMembaca::route('/'),
        ];
    }
}
