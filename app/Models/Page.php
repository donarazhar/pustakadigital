<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'page_number',
        'title',
        'content',
        'audio_narration_url',
        'featured_image',
        'video_embed_url',
    ];

    protected $casts = [
        'page_number' => 'integer',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function annotations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentBookAnnotation::class);
    }
}
