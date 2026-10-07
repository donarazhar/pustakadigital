<?php

namespace App\Filament\Admin\Resources\Grades;

use App\Filament\Admin\Resources\Grades\Pages\CreateGrade;
use App\Filament\Admin\Resources\Grades\Pages\EditGrade;
use App\Filament\Admin\Resources\Grades\Pages\ListGrades;
use App\Filament\Admin\Resources\Grades\Schemas\GradeForm;
use App\Filament\Admin\Resources\Grades\Tables\GradesTable;
use App\Models\Grade;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|\UnitEnum|null $navigationGroup = 'Kurikulum & Taksonomi';

    protected static ?string $navigationLabel = 'Jenjang & Kelas';

    protected static ?string $modelLabel = 'Jenjang / Kelas';

    protected static ?string $pluralModelLabel = 'Jenjang & Kelas';

    public static function form(Schema $schema): Schema
    {
        return GradeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GradesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGrades::route('/'),
            'create' => CreateGrade::route('/create'),
            'edit' => EditGrade::route('/{record}/edit'),
        ];
    }
}
