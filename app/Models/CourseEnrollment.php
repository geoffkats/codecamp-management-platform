<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'camp_id',
        'club_id',
        'enrolled_at',
        'completed_at',
        'progress_percentage',
        'lessons_completed',
        'quizzes_completed',
        'average_quiz_score',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
            'progress_percentage' => 'decimal:2',
            'average_quiz_score' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function camp(): BelongsTo
    {
        return $this->belongsTo(CodeCamp::class, 'camp_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(CodeClub::class, 'club_id');
    }

    public function scopeCurrentClass($query, ?int $campId = null, ?int $courseId = null)
    {
        $query->whereNull('completed_at')->whereNotNull('course_id');

        if ($campId) {
            // Legacy rows often have null camp_id; still treat them as this camp's open class.
            $query->where(function ($q) use ($campId) {
                $q->where('camp_id', $campId)->orWhereNull('camp_id');
            });
        }

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        return $query;
    }

    /**
     * Count only XP from a student's current (unfinished) class.
     * Old / finished courses still award career XP, but they do not rank here.
     */
    public static function constrainProgressToCurrentClass($query, ?int $campId = null, ?int $courseId = null): void
    {
        $query->whereExists(function ($sub) use ($campId, $courseId) {
            $sub->selectRaw('1')
                ->from('course_enrollments')
                ->whereColumn('course_enrollments.user_id', 'user_progress.user_id')
                ->whereColumn('course_enrollments.course_id', 'user_progress.course_id')
                ->whereNull('course_enrollments.completed_at');

            if ($campId) {
                $sub->where(function ($q) use ($campId) {
                    $q->where('course_enrollments.camp_id', $campId)
                        ->orWhereNull('course_enrollments.camp_id');
                });
            }

            if ($courseId) {
                $sub->where('course_enrollments.course_id', $courseId);
            }
        });
    }

    /**
     * Mark unfinished classes in this camp as done so the student cannot keep
     * ranking there after they transfer, drop, or move to another camp.
     */
    public static function closeOpenClassesInCamp(int $userId, int $campId): void
    {
        static::query()
            ->where('user_id', $userId)
            ->whereNull('completed_at')
            ->where(function ($q) use ($campId) {
                $q->where('camp_id', $campId)->orWhereNull('camp_id');
            })
            ->update(['completed_at' => now()]);
    }

    protected static function booted(): void
    {
        static::creating(function (self $enrollment) {
            $user = User::find($enrollment->user_id);

            if (empty($enrollment->club_id) && $user?->studentProfile?->program_type === 'codeclub') {
                $activeClubId = CodeClubMembership::where('student_id', $enrollment->user_id)
                    ->where('status', 'active')
                    ->value('code_club_id');
                if ($activeClubId) {
                    $enrollment->club_id = $activeClubId;
                }
            }

            if (empty($enrollment->camp_id) && $user?->studentProfile?->program_type !== 'codeclub') {
                $activeCampId = CampEnrollment::where('student_id', $enrollment->user_id)
                    ->where('status', 'active')
                    ->value('camp_id');
                if ($activeCampId) {
                    $enrollment->camp_id = $activeCampId;
                }
            }
        });
    }
}

