<?php

namespace App\Models;

use App\Support\ProgramScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'user_id',
        'school_id',
        'teacher_id',
        'student_type',
        'auto_scored',
        'is_locked',
        'started_at',
        'completed_at',
        'score',
        'time_spent',
        'is_passed',
        'answers',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'score' => 'decimal:2',
            'is_passed' => 'boolean',
            'auto_scored' => 'boolean',
            'is_locked' => 'boolean',
            'answers' => 'array',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasAnyRole(['admin', 'supervisor'])) {
            return $query;
        }

        if (ProgramScope::isClubFacilitatorContext($user)) {
            $clubStudentIds = ProgramScope::clubStudentUserIds($user);

            return $query->whereIn('user_id', $clubStudentIds ?: [-1]);
        }

        if ($user->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();

            return $query
                ->where('student_type', 'ict')
                ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));
        }

        if ($user->isTeacher()) {
            return $query
                ->where('student_type', 'codecamp')
                ->whereHas('assessment.course', function ($q) use ($user) {
                    $q->where('instructor_id', $user->id)
                        ->orWhereHas('collaborators', fn ($c) => $c->where('user_id', $user->id))
                        ->orWhereHas('enrollments', fn ($e) => $e->where('user_id', $user->id));
                });
        }

        if ($user->isStudent()) {
            return $query->where('user_id', $user->id);
        }

        return $query->whereRaw('1=0');
    }

    public function isGraded(): bool
    {
        return $this->score !== null;
    }

    public function isPendingReview(): bool
    {
        return $this->status === 'completed' && ! $this->auto_scored && $this->score === null;
    }

    public function maxScore(): float
    {
        $assessment = $this->relationLoaded('assessment')
            ? $this->assessment
            : $this->assessment()->with('questions')->first();

        if (! $assessment) {
            return 100;
        }

        $fromQuestions = $assessment->questions?->sum('points') ?? 0;

        return (float) ($fromQuestions > 0 ? $fromQuestions : ($assessment->max_points ?? 100));
    }

    /**
     * Unified percentage. Attempt.score is always stored as points (0..maxScore).
     */
    public function scorePercentage(): ?float
    {
        if ($this->score === null) {
            return null;
        }

        $max = $this->maxScore();

        return $max > 0 ? min(round(((float) $this->score / $max) * 100, 2), 100) : 0;
    }

    /**
     * Normalize legacy rows that may have stored percentage instead of points.
     */
    public function scoreAsPoints(): ?float
    {
        if ($this->score === null) {
            return null;
        }

        $score = (float) $this->score;
        $max = $this->maxScore();

        // Legacy manual grades sometimes stored percentage (0–100) while max points ≠ 100.
        if (! $this->auto_scored && $max > 0 && $score > $max && $score <= 100) {
            return round(($score / 100) * $max, 2);
        }

        return $score;
    }

    public function submissionText(): string
    {
        $answers = $this->answers ?? [];

        return trim((string) ($answers['submission_text'] ?? $answers['text'] ?? ''));
    }

    /**
     * @return array<int, array{path: string, name: string, question_id?: int, question_text?: string}>
     */
    public function submissionFiles(): array
    {
        $answers = $this->answers ?? [];
        $files = [];

        foreach ($answers['files'] ?? [] as $file) {
            $path = is_array($file) ? ($file['path'] ?? '') : (string) $file;
            if ($path === '') {
                continue;
            }
            $name = is_array($file)
                ? ($file['name'] ?? $file['original_name'] ?? basename($path))
                : basename($path);
            $files[] = [
                'path' => $path,
                'name' => is_string($name) && $name !== '' ? $name : basename($path),
            ];
        }

        $assessment = $this->relationLoaded('assessment')
            ? $this->assessment
            : $this->assessment()->with('questions')->first();

        if ($assessment) {
            foreach ($assessment->questions ?? [] as $question) {
                $type = str_replace(' ', '_', strtolower((string) $question->question_type));
                if ($type !== 'file_upload') {
                    continue;
                }
                $questionAnswer = $answers[$question->id] ?? null;
                if (! is_array($questionAnswer)) {
                    continue;
                }
                foreach ($questionAnswer['files'] ?? [] as $file) {
                    $path = is_array($file) ? ($file['path'] ?? '') : (string) $file;
                    if ($path === '') {
                        continue;
                    }
                    $name = is_array($file)
                        ? ($file['name'] ?? $file['original_name'] ?? basename($path))
                        : basename($path);
                    $files[] = [
                        'path' => $path,
                        'name' => is_string($name) && $name !== '' ? $name : basename($path),
                        'question_id' => $question->id,
                        'question_text' => $question->question_text,
                    ];
                }
            }
        }

        return $files;
    }

    public function graderFeedback(): ?string
    {
        $answers = $this->answers ?? [];

        return $answers['feedback'] ?? null;
    }
}

