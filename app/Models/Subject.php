<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'program_id',
        'level',
        'icon',
        'color',
        'description',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(SchoolProgram::class, 'program_id');
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function isGeneral(): bool
    {
        return $this->program_id === null;
    }
}
