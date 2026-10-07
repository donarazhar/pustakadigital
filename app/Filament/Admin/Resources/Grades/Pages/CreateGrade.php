<?php

namespace App\Filament\Admin\Resources\Grades\Pages;

use App\Filament\Admin\Resources\Grades\GradeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGrade extends CreateRecord
{
    protected static string $resource = GradeResource::class;
}
