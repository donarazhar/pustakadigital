<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingAssignmentStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'reading_assignment_id',
        'student_id',
        'status', // 'assigned', 'in_progress', 'completed'
        'progress_percent',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'progress_percent' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ReadingAssignment::class, 'reading_assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markAsCompleted(?string $notes = null): void
    {
        $this->update([
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
            'notes' => $notes ?: $this->notes,
        ]);
    }

    public function updateProgress(int $percent): void
    {
        $percent = min(100, max(0, $percent));

        if ($percent >= 100) {
            $this->markAsCompleted();
        } else {
            $this->update([
                'status' => 'in_progress',
                'progress_percent' => $percent,
            ]);
        }
    }
}
