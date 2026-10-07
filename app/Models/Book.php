<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'book_type',
        'author',
        'publisher',
        'publication_year',
        'isbn',
        'cover_image',
        'pdf_file',
        'description',
        'grade_id',
        'program_id',
        'subject_id',
        'category_id',
        'is_published',
        'estimated_read_time',
        'total_pages',
        'view_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'publication_year' => 'integer',
        'estimated_read_time' => 'integer',
        'total_pages' => 'integer',
        'view_count' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($book) {
            if (empty($book->slug)) {
                $book->slug = Str::slug($book->title) . '-' . Str::random(5);
            }
        });
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(SchoolProgram::class, 'program_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('order')->orderBy('chapter_number');
    }

    public function pages(): HasManyThrough
    {
        return $this->hasManyThrough(Page::class, Chapter::class);
    }

    public function readingLogs(): HasMany
    {
        return $this->hasMany(StudentReadingLog::class);
    }

    public function isPdf(): bool
    {
        return $this->book_type === 'pdf';
    }

    public function isInteractive(): bool
    {
        return $this->book_type === 'interactive' || empty($this->book_type);
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(StudentBookAnnotation::class);
    }
}
