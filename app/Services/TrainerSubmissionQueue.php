<?php

namespace App\Services;

use App\Models\AssessmentAttempt;
use App\Models\AssignmentSubmission;
use App\Models\User;
use App\Support\ProgramScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TrainerSubmissionQueue
{
    public const CACHE_TTL_SECONDS = 60;

    /** Assessment types trainers grade from the submissions queue (excludes surveys). */
    public const ASSESSMENT_TYPES = [
        'assignment',
        'quiz',
        'pre_project_test',
        'post_project_test',
        'rubric_assessment',
        'peer_review',
        'self_assessment',
    ];

    public static function cacheKey(User $user): string
    {
        return "user_{$user->id}_pending_submissions";
    }

    public static function forgetCache(User $user): void
    {
        Cache::forget(self::cacheKey($user));
    }

    public function pendingCount(User $user): int
    {
        return $this->pendingAssignmentQuery($user)->count()
            + $this->pendingAssessmentQuery($user)->count();
    }

    public function cachedPendingCount(User $user): int
    {
        return (int) Cache::remember(
            self::cacheKey($user),
            self::CACHE_TTL_SECONDS,
            fn () => $this->pendingCount($user)
        );
    }

    /**
     * Newest ungraded submissions a trainer can mark, for dashboard overview.
     *
     * @return Collection<int, object{
     *     type: string,
     *     id: int,
     *     title: string,
     *     courseTitle: string,
     *     studentName: string,
     *     typeLabel: string,
     *     submittedAt: ?Carbon,
     *     submission: AssignmentSubmission|AssessmentAttempt
     * }>
     */
    public function recentPending(User $user, int $limit = 8): Collection
    {
        $assignments = $this->pendingAssignmentQuery($user)
            ->with(['assignment.course', 'user'])
            ->latest('submitted_at')
            ->limit($limit)
            ->get()
            ->map(fn (AssignmentSubmission $submission) => (object) [
                'type' => 'assignment',
                'id' => $submission->id,
                'title' => $submission->assignment?->title ?? 'Assignment',
                'courseTitle' => $submission->assignment?->course?->title ?? 'Course',
                'studentName' => $submission->user?->name ?? 'Student',
                'typeLabel' => 'Assignment',
                'submittedAt' => $submission->submitted_at ?? $submission->created_at,
                'submission' => $submission,
            ]);

        $assessments = $this->pendingAssessmentQuery($user)
            ->with(['assessment.course', 'user'])
            ->latest('completed_at')
            ->limit($limit)
            ->get()
            ->map(function (AssessmentAttempt $attempt) {
                $answers = $attempt->answers ?? [];
                $submittedAt = isset($answers['submitted_at'])
                    ? Carbon::parse($answers['submitted_at'])
                    : ($attempt->completed_at ?? $attempt->updated_at);

                return (object) [
                    'type' => 'assessment',
                    'id' => $attempt->id,
                    'title' => $attempt->assessment?->title ?? 'Assessment',
                    'courseTitle' => $attempt->assessment?->course?->title ?? 'Course',
                    'studentName' => $attempt->user?->name ?? 'Student',
                    'typeLabel' => $this->assessmentTypeLabel($attempt->assessment?->assessment_type),
                    'submittedAt' => $submittedAt,
                    'submission' => $attempt,
                ];
            });

        return $assignments
            ->concat($assessments)
            ->sortByDesc(fn ($item) => $item->submittedAt?->timestamp ?? 0)
            ->take($limit)
            ->values();
    }

    private function pendingAssignmentQuery(User $user): Builder
    {
        $query = AssignmentSubmission::query()
            ->where('status', 'submitted')
            ->whereNull('graded_at');

        $this->applyStaffVisibility($query, $user, 'assignment.course');

        return $query;
    }

    private function pendingAssessmentQuery(User $user): Builder
    {
        $query = AssessmentAttempt::query()
            ->whereHas('assessment', fn ($q) => $q->whereIn('assessment_type', self::ASSESSMENT_TYPES))
            ->where(function ($q) {
                $q->where('status', 'completed')
                    ->orWhereNotNull('completed_at');
            })
            ->whereNull('score');

        if ($user->isAdmin() || $user->isSupervisor()) {
            return $query;
        }

        if (ProgramScope::isClubFacilitatorContext($user)) {
            $clubStudentIds = ProgramScope::clubStudentUserIds($user);

            return $query->whereIn('user_id', $clubStudentIds ?: [-1]);
        }

        if ($user->isTeacher()) {
            return $query->where(function ($q) use ($user) {
                $q->where(function ($st) {
                    $st->whereNull('student_type')
                        ->orWhere('student_type', '!=', 'ict');
                })->whereHas('assessment.course', fn ($courseQuery) => $courseQuery->accessibleBy($user));
            });
        }

        return $query->whereRaw('1 = 0');
    }

    private function applyStaffVisibility(Builder $query, User $user, string $courseRelation): void
    {
        if ($user->isAdmin() || $user->isSupervisor()) {
            return;
        }

        if (ProgramScope::isClubFacilitatorContext($user)) {
            $clubStudentIds = ProgramScope::clubStudentUserIds($user);
            $query->whereIn('user_id', $clubStudentIds ?: [-1]);

            return;
        }

        if ($user->isTeacher()) {
            $query->whereHas($courseRelation, fn ($q) => $q->accessibleBy($user));

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function assessmentTypeLabel(?string $type): string
    {
        return match ($type) {
            'assignment' => 'Assignment',
            'quiz' => 'Quiz',
            'pre_project_test' => 'Pre-project test',
            'post_project_test' => 'Post-project test',
            'rubric_assessment' => 'Rubric',
            'peer_review' => 'Peer review',
            'self_assessment' => 'Self-assessment',
            default => 'Assessment',
        };
    }
}
