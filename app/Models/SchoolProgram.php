<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'level',
        'icon',
        'color',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Murid / Siswa yang terdaftar pada program ini.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'program_id');
    }

    /**
     * Mata pelajaran khusus untuk program ini.
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'program_id');
    }

    /**
     * Buku digital khusus yang diperuntukkan bagi program ini.
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'program_id');
    }

    /**
     * Penugasan membaca yang ditargetkan untuk program ini.
     */
    public function readingAssignments(): HasMany
    {
        return $this->hasMany(ReadingAssignment::class, 'program_id');
    }

    /**
     * Label jenjang yang mudah dibaca.
     */
    public function getLevelLabel(): string
    {
        return match ($this->level) {
            'SD' => 'Sekolah Dasar (SD)',
            'SMP' => 'Sekolah Menengah Pertama (SMP)',
            'SMA' => 'Sekolah Menengah Atas/Kejuruan (SMA/SMK)',
            default => 'Semua Jenjang',
        };
    }
}
