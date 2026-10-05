<?php

namespace App\Models;

use App\Support\ProgramScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'score_unit',
        'time_spent',
        'is_passed',
        'answers',
        'status',
        'question_set_generated_at',
        'question_set_version',
        'question_set_source',
        'question_set_meta',
        'question_set_review_required',
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
            'question_set_generated_at' => 'datetime',
            'question_set_meta' => 'array',
            'question_set_review_required' => 'boolean',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function questionSet(): HasMany
    {
        return $this->hasMany(AssessmentAttemptQuestion::class)->orderBy('position');
    }

    /**
     * True once the attempt's questions are frozen; from then on the snapshot, not the live
     * question tables, is the source of truth for this attempt.
     */
    public function hasQuestionSet(): bool
    {
        return $this->question_set_generated_at !== null;
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
        if ($this->hasQuestionSet()) {
            $fromSnapshot = $this->relationLoaded('questionSet')
                ? (float) $this->questionSet->sum('points')
                : (float) $this->questionSet()->sum('points');

            if ($fromSnapshot > 0) {
                return $fromSnapshot;
            }

            $assessment = $this->relationLoaded('assessment') ? $this->assessment : $this->assessment()->first();

            return (float) ($assessment && $assessment->assessment_type === 'assignment'
                ? $assessment->max_points
                : 100);
        }

        $assessment = $this->relationLoaded('assessment')
            ? $this->assessment
            : $this->assessment()->with('questions')->first();

        if (! $assessment) {
            return 100;
        }

        $fromQuestions = $assessment->questions?->sum('points') ?? 0;

        return (float) ($fromQuestions > 0 ? $fromQuestions : ($assessment->max_points ?? 100));
    }

    public function getPercentageScoreAttribute(): ?float
    {
        return $this->scorePercentage();
    }

    /**
     * SQL for an attempt's percentage, mirroring maxScore(): frozen question set, else the
     * assessment's live questions, else the assignment's max points, else 100.
     */
    public static function percentageSql(string $table = 'assessment_attempts'): string
    {
        $max = "COALESCE("
            ."NULLIF((SELECT SUM(aaq.points) FROM assessment_attempt_questions aaq WHERE aaq.assessment_attempt_id = {$table}.id), 0), "
            ."NULLIF((SELECT SUM(pq.points) FROM assessment_question apq JOIN questions pq ON pq.id = apq.question_id WHERE apq.assessment_id = {$table}.assessment_id), 0), "
            ."(SELECT NULLIF(CAST(JSON_UNQUOTE(JSON_EXTRACT(pa.assignment_data, '$.max_points')) AS DECIMAL(10,2)), 0) FROM assessments pa WHERE pa.id = {$table}.assessment_id AND pa.assessment_type = 'assignment'), "
            ."100)";

        return "LEAST(100, {$table}.score / {$max} * 100)";
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

        $questions = app(\App\Services\Assessments\AssessmentGradingService::class)->questionsFor($this);

        foreach ($questions as $question) {
            if ($question['type'] !== 'file_upload') {
                continue;
            }
            $questionAnswer = $answers[$question['question_id']] ?? null;
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
                    'question_id' => $question['question_id'],
                    'question_text' => $question['text'],
                ];
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

