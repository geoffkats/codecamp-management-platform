<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Question extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public const DIFFICULTIES = ['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'];

    public const STATUSES = ['draft' => 'Draft', 'active' => 'Active', 'archived' => 'Archived'];

    /** Changing any of these produces a new version; attempts keep the version they snapshotted. */
    public const CONTENT_FIELDS = ['question_text', 'question_type', 'points', 'explanation', 'media_url', 'image_url', 'image_alt_text', 'image_position', 'media_type', 'settings'];

    protected $fillable = [
        'quiz_id',
        'assessment_id',
        'question_text',
        'question_type',
        'points',
        'difficulty',
        'status',
        'order',
        'explanation',
        'media_url',
        'image_url',
        'image_alt_text',
        'image_position',
        'media_type',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $attributes = [
        'difficulty' => 'medium',
        'status' => 'active',
        'version' => 1,
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Question $question) {
            $question->created_by ??= Auth::id();
            $question->updated_by ??= Auth::id();
        });

        // Legacy code creates questions with assessment_id; keep that assessment using them.
        static::created(function (Question $question) {
            if ($question->assessment_id && Assessment::withTrashed()->whereKey($question->assessment_id)->exists()) {
                $position = (int) $question->order > 0
                    ? (int) $question->order
                    : (int) DB::table('assessment_question')->where('assessment_id', $question->assessment_id)->max('position') + 1;

                $question->assessments()->syncWithoutDetaching([$question->assessment_id => ['position' => $position]]);
            }
        });

        static::updating(function (Question $question) {
            if ($question->isDirty(self::CONTENT_FIELDS)) {
                $question->version = (int) $question->getOriginal('version', 1) + 1;
            }
            if (Auth::id()) {
                $question->updated_by = Auth::id();
            }
        });
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * The assessment the question was originally written for. Use assessments() for every
     * assessment that currently includes it.
     */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function assessments(): BelongsToMany
    {
        return $this->belongsToMany(Assessment::class, 'assessment_question')
            ->withPivot('position')
            ->withTimestamps();
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('name');
    }

    public function placements(): HasMany
    {
        return $this->hasMany(QuestionPlacement::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Admins see the whole bank; teachers see questions in courses they teach or collaborate on,
     * plus anything they wrote. Everyone else sees nothing.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if (! $user->can('edit_courses')) {
            return $query->whereRaw('1 = 0');
        }

        $courseIds = Course::query()
            ->where('instructor_id', $user->id)
            ->orWhereHas('collaborators', fn ($q) => $q->where('user_id', $user->id))
            ->pluck('id');

        return $query->where(function (Builder $q) use ($user, $courseIds) {
            $q->where('questions.created_by', $user->id)
                ->orWhereHas('placements', fn ($p) => $p->whereIn('course_id', $courseIds))
                ->orWhereHas('assessments', fn ($a) => $a->whereIn('course_id', $courseIds));
        });
    }

    public function isVisibleTo(User $user): bool
    {
        return static::query()->visibleTo($user)->whereKey($this->getKey())->exists();
    }

    public function getDisplayName(): ?string
    {
        return Str::limit(trim(strip_tags((string) $this->question_text)), 80);
    }
}
