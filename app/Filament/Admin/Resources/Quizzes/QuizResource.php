<?php

namespace App\Filament\Admin\Resources\Quizzes;

use App\Filament\Admin\Resources\Quizzes\Pages\CreateQuiz;
use App\Filament\Admin\Resources\Quizzes\Pages\EditQuiz;
use App\Filament\Admin\Resources\Quizzes\Pages\ListQuizzes;
use App\Filament\Admin\Resources\Quizzes\RelationManagers\QuestionsRelationManager;
use App\Filament\Admin\Resources\Quizzes\Schemas\QuizForm;
use App\Filament\Admin\Resources\Quizzes\Tables\QuizzesTable;
use App\Models\Quiz;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'Evaluasi & Penugasan';

    protected static ?string $navigationLabel = 'Kuis Interaktif';

    protected static ?string $modelLabel = 'Kuis';

    protected static ?string $pluralModelLabel = 'Kuis Interaktif';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return QuizForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuizzesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuizzes::route('/'),
            'create' => CreateQuiz::route('/create'),
            'edit' => EditQuiz::route('/{record}/edit'),
        ];
    }
}
