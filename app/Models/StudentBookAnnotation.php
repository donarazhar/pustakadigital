<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentBookAnnotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'chapter_id',
        'page_id',
        'type', // 'highlight', 'sticky_note', 'highlight_note'
        'highlighted_text',
        'color', // yellow, green, blue, orange, purple, pink
        'note',
        'position_data',
    ];

    protected $casts = [
        'position_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function getColorHex(): string
    {
        return match ($this->color) {
            'green' => '#86efac',   // emerald/green highlight
            'blue' => '#93c5fd',    // sky/blue highlight
            'orange' => '#fdba74',  // amber/orange highlight
            'purple' => '#d8b4fe',  // purple highlight
            'pink' => '#f9a8d4',    // pink highlight
            default => '#fef08a',   // yellow highlight
        };
    }

    public function isHighlight(): bool
    {
        return in_array($this->type, ['highlight', 'highlight_note']);
    }

    public function isStickyNote(): bool
    {
        return in_array($this->type, ['sticky_note', 'highlight_note']) || ! empty($this->note);
    }
}
