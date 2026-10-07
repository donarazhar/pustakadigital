<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // 'admin', 'teacher', 'student'
        'grade_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Admin dan Guru dapat mengakses panel admin Filament
        if ($panel->getId() === 'admin') {
            return in_array($this->role, ['admin', 'teacher']);
        }

        // Siswa dan Admin dapat mengakses panel siswa
        if ($panel->getId() === 'siswa') {
            return in_array($this->role, ['student', 'admin']);
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function readingLogs(): HasMany
    {
        return $this->hasMany(StudentReadingLog::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(StudentQuizAttempt::class);
    }

    public function createdReadingAssignments(): HasMany
    {
        return $this->hasMany(ReadingAssignment::class, 'teacher_id');
    }

    public function assignedReadings(): HasMany
    {
        return $this->hasMany(ReadingAssignmentStudent::class, 'student_id');
    }

    public function assignedReadingAssignments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ReadingAssignment::class, 'reading_assignment_students', 'student_id', 'reading_assignment_id')
            ->withPivot(['status', 'progress_percent', 'completed_at', 'notes'])
            ->withTimestamps();
    }

    public function bookAnnotations(): HasMany
    {
        return $this->hasMany(StudentBookAnnotation::class);
    }
}
