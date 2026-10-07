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
        'program_id',
        'avatar',
        'reading_streak_days',
        'longest_streak_days',
        'last_read_date',
        'literacy_points',
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
            'reading_streak_days' => 'integer',
            'longest_streak_days' => 'integer',
            'last_read_date' => 'datetime',
            'literacy_points' => 'integer',
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

    public function program(): BelongsTo
    {
        return $this->belongsTo(SchoolProgram::class, 'program_id');
    }

    /**
     * Label akademik lengkap siswa (misal: "Kelas 4 SD • Program Bilingual").
     */
    public function getAcademicBadge(): string
    {
        $parts = [];
        if ($this->grade) {
            $parts[] = $this->grade->name;
        }
        if ($this->program) {
            $parts[] = ($this->program->icon ? $this->program->icon . ' ' : '') . $this->program->name;
        }

        return ! empty($parts) ? implode(' • ', $parts) : 'Siswa Sekolah';
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

    public function badges(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot(['awarded_at', 'notes'])
            ->withTimestamps();
    }

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    public function hasBadge(string $slug): bool
    {
        return $this->badges()->where('slug', $slug)->exists();
    }

    public function getLeaderboardRank(): int
    {
        return static::where('role', 'student')
            ->where('literacy_points', '>', $this->literacy_points)
            ->count() + 1;
    }
}
