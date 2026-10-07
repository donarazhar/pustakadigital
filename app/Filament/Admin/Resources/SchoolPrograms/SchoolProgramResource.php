<?php

namespace App\Filament\Admin\Resources\SchoolPrograms;

use App\Filament\Admin\Resources\SchoolPrograms\Pages\CreateSchoolProgram;
use App\Filament\Admin\Resources\SchoolPrograms\Pages\EditSchoolProgram;
use App\Filament\Admin\Resources\SchoolPrograms\Pages\ListSchoolPrograms;
use App\Filament\Admin\Resources\SchoolPrograms\Schemas\SchoolProgramForm;
use App\Filament\Admin\Resources\SchoolPrograms\Tables\SchoolProgramsTable;
use App\Models\SchoolProgram;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SchoolProgramResource extends Resource
{
    protected static ?string $model = SchoolProgram::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'Kurikulum & Taksonomi';

    protected static ?string $navigationLabel = 'Program Sekolah';

    protected static ?string $modelLabel = 'Program Sekolah';

    protected static ?string $pluralModelLabel = 'Program Sekolah';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return SchoolProgramForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SchoolProgramsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSchoolPrograms::route('/'),
            'create' => CreateSchoolProgram::route('/create'),
            'edit' => EditSchoolProgram::route('/{record}/edit'),
        ];
    }
}
