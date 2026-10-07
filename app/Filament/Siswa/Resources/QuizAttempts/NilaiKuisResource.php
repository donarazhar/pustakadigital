<?php

namespace App\Filament\Siswa\Resources\QuizAttempts;

use App\Filament\Siswa\Resources\QuizAttempts\Pages\ListNilaiKuis;
use App\Filament\Siswa\Resources\QuizAttempts\Tables\NilaiKuisTable;
use App\Models\StudentQuizAttempt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class NilaiKuisResource extends Resource
{
    protected static ?string $model = StudentQuizAttempt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'Aktivitas Belajar';

    protected static ?string $navigationLabel = 'Nilai & Evaluasi Kuis';

    protected static ?string $modelLabel = 'Hasil Kuis';

    protected static ?string $pluralModelLabel = 'Nilai & Evaluasi Kuis';

    protected static ?string $slug = 'nilai-kuis';

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
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id())
            ->with(['quiz.chapter.book']);
    }

    public static function table(Table $table): Table
    {
        return NilaiKuisTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNilaiKuis::route('/'),
        ];
    }
}
