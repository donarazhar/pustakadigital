<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'badge_color',
        'category',
        'criteria_type',
        'criteria_value',
        'points_reward',
        'order',
    ];

    protected $casts = [
        'criteria_value' => 'integer',
        'points_reward' => 'integer',
        'order' => 'integer',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot(['awarded_at', 'notes'])
            ->withTimestamps();
    }

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    public function getColorBadgeClass(): string
    {
        return match ($this->badge_color) {
            'emerald' => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950 dark:text-emerald-300',
            'blue' => 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-950 dark:text-blue-300',
            'purple' => 'bg-purple-100 text-purple-800 border-purple-300 dark:bg-purple-950 dark:text-purple-300',
            'rose' => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-950 dark:text-rose-300',
            'cyan' => 'bg-cyan-100 text-cyan-800 border-cyan-300 dark:bg-cyan-950 dark:text-cyan-300',
            default => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950 dark:text-amber-300',
        };
    }
}
