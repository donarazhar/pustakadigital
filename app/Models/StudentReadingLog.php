<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentReadingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'last_chapter_id',
        'last_page_id',
        'progress_percent',
        'is_completed',
        'last_read_at',
    ];

    protected $casts = [
        'progress_percent' => 'integer',
        'is_completed' => 'boolean',
        'last_read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function lastChapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'last_chapter_id');
    }

    public function lastPage(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'last_page_id');
    }
}
