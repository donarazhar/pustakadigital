<?php

namespace App\Filament\Admin\Resources\ReadingAssignments;

use App\Filament\Admin\Resources\ReadingAssignments\Pages\CreateReadingAssignment;
use App\Filament\Admin\Resources\ReadingAssignments\Pages\EditReadingAssignment;
use App\Filament\Admin\Resources\ReadingAssignments\Pages\ListReadingAssignments;
use App\Filament\Admin\Resources\ReadingAssignments\RelationManagers\StudentsRelationManager;
use App\Filament\Admin\Resources\ReadingAssignments\Schemas\ReadingAssignmentForm;
use App\Filament\Admin\Resources\ReadingAssignments\Tables\ReadingAssignmentsTable;
use App\Models\ReadingAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReadingAssignmentResource extends Resource
{
    protected static ?string $model = ReadingAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Evaluasi & Penugasan';

    protected static ?string $navigationLabel = 'Penugasan Membaca';

    protected static ?string $modelLabel = 'Tugas Membaca';

    protected static ?string $pluralModelLabel = 'Penugasan Membaca';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ReadingAssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReadingAssignmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReadingAssignments::route('/'),
            'create' => CreateReadingAssignment::route('/create'),
            'edit' => EditReadingAssignment::route('/{record}/edit'),
        ];
    }
}
