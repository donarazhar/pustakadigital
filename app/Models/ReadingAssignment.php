<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReadingAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'teacher_id',
        'book_id',
        'target_chapter_id',
        'grade_id',
        'due_date',
        'is_active',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function targetChapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'target_chapter_id');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function assignmentStudents(): HasMany
    {
        return $this->hasMany(ReadingAssignmentStudent::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'reading_assignment_students', 'reading_assignment_id', 'student_id')
            ->withPivot(['status', 'progress_percent', 'completed_at', 'notes'])
            ->withTimestamps();
    }

    public function totalStudentsCount(): int
    {
        return $this->assignmentStudents()->count();
    }

    public function completedStudentsCount(): int
    {
        return $this->assignmentStudents()->where('status', 'completed')->count();
    }

    public function inProgressStudentsCount(): int
    {
        return $this->assignmentStudents()->where('status', 'in_progress')->count();
    }

    public function unreadStudentsCount(): int
    {
        return $this->assignmentStudents()->where('status', 'assigned')->count();
    }

    public function completionRate(): float
    {
        $total = $this->totalStudentsCount();
        if ($total === 0) {
            return 0.0;
        }

        return round(($this->completedStudentsCount() / $total) * 100, 1);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }
}
