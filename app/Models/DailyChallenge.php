<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyChallenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'created_by',
        'title',
        'description',
        'type',
        'requirements',
        'reward_points',
        'date',
        'is_active',
        'difficulty_level',
        'category',
        'is_competition',
        'competition_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'requirements'        => 'array',
            'date'                => 'date',
            'is_active'           => 'boolean',
            'is_competition'      => 'boolean',
            'competition_ends_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->is_competition
            ? $this->competition_ends_at === null || $this->competition_ends_at->isFuture()
            : true;
    }

    /** Top-N completions ordered by completion time (fastest first). */
    public function competitionLeaderboard(int $limit = 10): \Illuminate\Support\Collection
    {
        return $this->attempts()
            ->where('is_completed', true)
            ->with('user:id,name')
            ->orderBy('completed_at')
            ->take($limit)
            ->get()
            ->map(fn ($a, $i) => [
                'rank'         => $i + 1,
                'user'         => $a->user,
                'completed_at' => $a->completed_at,
                'points'       => $a->points_earned,
            ]);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function canBeEditedBy(\App\Models\User $user): bool
    {
        if ($user->isAdmin() || $user->isSupervisor()) {
            return true;
        }

        if ($this->created_by === $user->id) {
            return true;
        }

        // Teachers may edit challenges tied to a course they instruct
        if ($user->isTeacher() && $this->course_id) {
            return Course::where('id', $this->course_id)
                ->where('instructor_id', $user->id)
                ->exists();
        }

        return false;
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(DailyChallengeAttempt::class, 'challenge_id');
    }
}

