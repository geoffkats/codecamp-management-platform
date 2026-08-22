<?php

namespace App\Services\Reports;

use App\Models\AssessmentAttempt;
use App\Models\CodeCamp;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\StudentLessonProgress;
use App\Models\StudentProfile;
use App\Models\UserProgress;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StudentProgressReportService
{
    /** Assessment types counted toward progress stats (excludes assignments & surveys). */
    private const GRADABLE_ASSESSMENT_TYPES = [
        'quiz',
        'pre_project_test',
        'post_project_test',
        'rubric_assessment',
        'peer_review',
        'self_assessment',
    ];

    public function invalidateReportCache(): void
    {
        Cache::forget('student_progress:course_options:all');
        Cache::forget('student_progress:course_options:codecamp');
        Cache::forget('student_progress:course_options:ict');
    }

    public function getCourseOptions(?string $programType = null, ?int $campId = null): Collection
    {
        $cacheKey = 'student_progress:course_options:' . ($programType ?: 'all') . ':' . ($campId ?: 'all');

        return Cache::remember($cacheKey, 3600, function () use ($programType, $campId) {
            return Course::query()
                ->select('id', 'title')
                ->whereHas('enrollments', function ($query) use ($campId) {
                    if ($campId) {
                        $query->where('camp_id', $campId);
                    }
                })
                ->when($programType, function ($query) use ($programType) {
                    $query->whereHas('enrollments.user.studentProfile', fn ($profileQuery) => $profileQuery->where('program_type', $programType));
                })
                ->orderBy('title')
                ->get();
        });
    }

    public function getCampOptions(): Collection
    {
        return CodeCamp::query()
            ->select('id', 'name', 'status', 'start_date')
            ->orderByDesc('start_date')
            ->get();
    }

    public function getFilterSummary(array $filters): array
    {
        $total = $this->buildFilteredStudentsQuery($filters)->count();

        return [
            'total' => $total,
        ];
    }

    public function getStudentList(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $page = max(1, (int) ($filters['page'] ?? 1));

        return $this->buildFilteredStudentsQuery($filters)->paginate($perPage, ['*'], 'page', $page);
    }

    public function chunkFilteredStudents(array $filters, int $chunkSize, callable $callback): void
    {
        $this->buildFilteredStudentsQuery($filters)
            ->with(['user:id,name,email,student_id', 'school:id,name'])
            ->chunkById($chunkSize, function ($students) use ($callback) {
                $callback($students);
            });
    }

    public function getListMetricsForUsers(array $userIds, ?int $courseId = null, ?int $campId = null): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));

        if (empty($userIds)) {
            return [];
        }

        $enrollmentQuery = CourseEnrollment::query()
            ->selectRaw('user_id, COUNT(*) as courses_enrolled, AVG(CASE WHEN completed_at IS NOT NULL THEN 100 ELSE COALESCE(progress_percentage, 0) END) as avg_progress')
            ->whereIn('user_id', $userIds);

        if ($courseId) {
            $enrollmentQuery->where('course_id', $courseId);
        }

        if ($campId) {
            $enrollmentQuery->where('camp_id', $campId);
        }

        $enrollmentRows = $enrollmentQuery
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $assessmentQuery = AssessmentAttempt::query()
            ->join('assessments', 'assessments.id', '=', 'assessment_attempts.assessment_id')
            ->leftJoin('lessons as metric_lessons', 'metric_lessons.id', '=', 'assessments.lesson_id')
            ->selectRaw('assessment_attempts.user_id, COUNT(*) as assessments_attempted, AVG(assessment_attempts.score) as avg_assessment_score, MAX(assessment_attempts.completed_at) as last_assessment_at')
            ->whereIn('assessment_attempts.user_id', $userIds)
            ->where('assessment_attempts.status', 'completed')
            ->whereNotNull('assessment_attempts.score')
            ->whereIn('assessments.assessment_type', self::GRADABLE_ASSESSMENT_TYPES);

        if ($courseId) {
            $assessmentQuery->where(function ($query) use ($courseId) {
                $query->where('assessments.course_id', $courseId)
                    ->orWhere('metric_lessons.course_id', $courseId);
            });
        }

        if ($campId) {
            $assessmentQuery->whereExists(function ($query) use ($campId) {
                $query->select(DB::raw(1))
                    ->from('course_enrollments')
                    ->whereColumn('course_enrollments.user_id', 'assessment_attempts.user_id')
                    ->where('course_enrollments.camp_id', $campId)
                    ->where(function ($courseMatch) {
                        $courseMatch->whereColumn('course_enrollments.course_id', 'assessments.course_id')
                            ->orWhereColumn('course_enrollments.course_id', 'metric_lessons.course_id');
                    });
            });
        }

        $assessmentRows = $assessmentQuery
            ->groupBy('assessment_attempts.user_id')
            ->get()
            ->keyBy('user_id');

        $lessonQuery = StudentLessonProgress::query()
            ->join('lessons as metric_lesson_rows', 'metric_lesson_rows.id', '=', 'student_lesson_progress.lesson_id')
            ->selectRaw('student_lesson_progress.user_id, COUNT(CASE WHEN student_lesson_progress.status = ? THEN 1 END) as lessons_completed, MAX(student_lesson_progress.last_accessed_at) as last_lesson_at', ['completed'])
            ->whereIn('student_lesson_progress.user_id', $userIds);

        if ($courseId) {
            $lessonQuery->where('metric_lesson_rows.course_id', $courseId);
        }

        if ($campId) {
            $lessonQuery->whereExists(function ($query) use ($campId) {
                $query->select(DB::raw(1))
                    ->from('course_enrollments')
                    ->whereColumn('course_enrollments.user_id', 'student_lesson_progress.user_id')
                    ->whereColumn('course_enrollments.course_id', 'metric_lesson_rows.course_id')
                    ->where('course_enrollments.camp_id', $campId);
            });
        }

        $lessonRows = $lessonQuery
            ->groupBy('student_lesson_progress.user_id')
            ->get()
            ->keyBy('user_id');

        $badgeRows = DB::table('user_badges')
            ->selectRaw('user_id, COUNT(*) as badges_earned, MAX(earned_at) as last_badge_at')
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $activityQuery = UserProgress::query()
            ->selectRaw('user_id, MAX(created_at) as last_activity_at')
            ->whereIn('user_id', $userIds);

        if ($courseId) {
            $activityQuery->where('course_id', $courseId);
        }

        if ($campId && ! $courseId) {
            $activityQuery->whereIn('course_id', function ($query) use ($campId, $userIds) {
                $query->select('course_id')
                    ->from('course_enrollments')
                    ->whereIn('user_id', $userIds)
                    ->where('camp_id', $campId)
                    ->whereNotNull('course_id');
            });
        }

        $activityRows = $activityQuery
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $metrics = [];

        foreach ($userIds as $userId) {
            $enrollment = $enrollmentRows->get($userId);
            $assessment = $assessmentRows->get($userId);
            $lesson = $lessonRows->get($userId);
            $badge = $badgeRows->get($userId);
            $activity = $activityRows->get($userId);

            $timestamps = array_filter([
                $assessment?->last_assessment_at,
                $lesson?->last_lesson_at,
                $badge?->last_badge_at,
                $activity?->last_activity_at,
            ]);

            $metrics[$userId] = [
                'courses_enrolled' => (int) ($enrollment?->courses_enrolled ?? 0),
                'completion_rate' => round((float) ($enrollment?->avg_progress ?? 0), 1),
                'lessons_completed' => (int) ($lesson?->lessons_completed ?? 0),
                'assessments_attempted' => (int) ($assessment?->assessments_attempted ?? 0),
                'avg_assessment_score' => round((float) ($assessment?->avg_assessment_score ?? 0), 1),
                'badges_earned' => (int) ($badge?->badges_earned ?? 0),
                'last_activity_at' => ! empty($timestamps) ? max($timestamps) : null,
            ];
        }

        return $metrics;
    }

    public function getStudentDetail(int $studentProfileId): ?array
    {
        $profile = StudentProfile::query()
            ->with(['user:id,name,email,student_id', 'school:id,name'])
            ->find($studentProfileId);

        if (! $profile || ! $profile->user_id) {
            return null;
        }

        $userId = (int) $profile->user_id;
        $summary = $this->getListMetricsForUsers([$userId])[$userId] ?? [
            'courses_enrolled' => 0,
            'completion_rate' => 0,
            'lessons_completed' => 0,
            'assessments_attempted' => 0,
            'avg_assessment_score' => 0,
            'badges_earned' => 0,
            'last_activity_at' => null,
        ];

        $courseProgress = CourseEnrollment::query()
            ->with('course:id,title,slug')
            ->where('user_id', $userId)
            ->orderByDesc(DB::raw('CASE WHEN completed_at IS NOT NULL THEN 100 ELSE COALESCE(progress_percentage, 0) END'))
            ->orderByDesc('updated_at')
            ->get();

        $courseIds = $courseProgress->pluck('course_id')->filter()->unique()->values();

        $lessonCountsByCourse = collect();
        $assessmentCountsByCourse = collect();

        if ($courseIds->isNotEmpty()) {
            $lessonCountsByCourse = StudentLessonProgress::query()
                ->join('lessons', 'lessons.id', '=', 'student_lesson_progress.lesson_id')
                ->where('student_lesson_progress.user_id', $userId)
                ->where('student_lesson_progress.status', 'completed')
                ->whereIn('lessons.course_id', $courseIds)
                ->groupBy('lessons.course_id')
                ->selectRaw('lessons.course_id, COUNT(*) as lessons_completed')
                ->pluck('lessons_completed', 'course_id');

            $assessmentCountsByCourse = AssessmentAttempt::query()
                ->join('assessments', 'assessments.id', '=', 'assessment_attempts.assessment_id')
                ->leftJoin('lessons', 'lessons.id', '=', 'assessments.lesson_id')
                ->where('assessment_attempts.user_id', $userId)
                ->where('assessment_attempts.status', 'completed')
                ->whereIn('assessments.assessment_type', self::GRADABLE_ASSESSMENT_TYPES)
                ->where(function ($query) use ($courseIds) {
                    $query->whereIn('assessments.course_id', $courseIds)
                        ->orWhereIn('lessons.course_id', $courseIds);
                })
                ->groupBy(DB::raw('COALESCE(assessments.course_id, lessons.course_id)'))
                ->selectRaw('COALESCE(assessments.course_id, lessons.course_id) as course_id, COUNT(*) as assessments_completed')
                ->pluck('assessments_completed', 'course_id');
        }

        $courseProgress->each(function ($enrollment) use ($lessonCountsByCourse, $assessmentCountsByCourse) {
            $courseId = (int) $enrollment->course_id;
            $enrollment->lessons_completed = (int) ($lessonCountsByCourse[$courseId] ?? 0);
            $enrollment->assessments_completed = (int) ($assessmentCountsByCourse[$courseId] ?? 0);
        });

        $assessmentBreakdown = AssessmentAttempt::query()
            ->leftJoin('assessments', 'assessments.id', '=', 'assessment_attempts.assessment_id')
            ->leftJoin('lessons', 'lessons.id', '=', 'assessments.lesson_id')
            ->leftJoin('courses as direct_courses', 'direct_courses.id', '=', 'assessments.course_id')
            ->leftJoin('courses as lesson_courses', 'lesson_courses.id', '=', 'lessons.course_id')
            ->selectRaw('assessment_attempts.user_id, assessment_attempts.assessment_id, COUNT(*) as attempts, AVG(assessment_attempts.score) as average_score, MAX(assessment_attempts.score) as best_score, MAX(COALESCE(assessment_attempts.completed_at, assessment_attempts.started_at, assessment_attempts.created_at)) as last_attempt_at, MAX(assessments.title) as assessment_title, MAX(assessments.assessment_type) as assessment_type, MAX(COALESCE(direct_courses.title, lesson_courses.title)) as course_title')
            ->where('assessment_attempts.user_id', $userId)
            ->where('assessment_attempts.status', 'completed')
            ->whereIn('assessments.assessment_type', self::GRADABLE_ASSESSMENT_TYPES)
            ->groupBy('assessment_attempts.user_id', 'assessment_attempts.assessment_id')
            ->orderByDesc('last_attempt_at')
            ->limit(30)
            ->get();

        $badges = DB::table('user_badges')
            ->join('badges', 'badges.id', '=', 'user_badges.badge_id')
            ->where('user_badges.user_id', $userId)
            ->orderByDesc('user_badges.earned_at')
            ->limit(25)
            ->get([
                'badges.id',
                'badges.name',
                'badges.slug',
                'badges.color',
                'badges.icon',
                'user_badges.earned_at',
            ]);

        $recentActivity = UserProgress::query()
            ->with(['course:id,title', 'lesson:id,title'])
            ->where('user_id', $userId)
            ->latest()
            ->limit(20)
            ->get();

        return [
            'profile' => $profile,
            'summary' => $summary,
            'courseProgress' => $courseProgress,
            'assessmentBreakdown' => $assessmentBreakdown,
            'badges' => $badges,
            'recentActivity' => $recentActivity,
        ];
    }

    private function buildFilteredStudentsQuery(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $course = (string) ($filters['course'] ?? 'all');
        $program = (string) ($filters['program'] ?? 'all');
        $camp = (string) ($filters['camp'] ?? 'all');
        $campStatus = (string) ($filters['campStatus'] ?? 'all');

        return StudentProfile::query()
            ->with([
                'user:id,name,email,student_id',
                'user.currentCampEnrollment.camp:id,name,status',
                'school:id,name',
            ])
            ->whereNotNull('user_id')
            ->when($program !== 'all', fn ($query) => $query->where('program_type', $program))
            ->when($camp !== 'all', function ($query) use ($camp, $campStatus) {
                $query->whereHas('user.campEnrollments', function ($campQuery) use ($camp, $campStatus) {
                    $campQuery->where('camp_id', (int) $camp);
                    if ($campStatus !== 'all') {
                        $campQuery->where('status', $campStatus);
                    }
                });
            })
            ->when($course !== 'all', function ($query) use ($course, $camp) {
                $query->whereHas('user.enrollments', function ($enrollmentQuery) use ($course, $camp) {
                    $enrollmentQuery->where('course_id', (int) $course);
                    if ($camp !== 'all') {
                        $enrollmentQuery->where('camp_id', (int) $camp);
                    }
                });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('full_name', 'like', '%' . $search . '%')
                        ->orWhere('student_profiles.student_id', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%')
                                ->orWhere('student_id', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByRaw('CASE WHEN class_grade IS NULL OR class_grade = "" THEN 1 ELSE 0 END')
            ->orderBy('class_grade')
            ->orderByRaw('COALESCE(full_name, "") ASC');
    }
}
