<?php

namespace App\Livewire\Dashboard;

use App\Models\CourseEnrollment;
use App\Models\School;
use App\Models\SchoolCourse;
use App\Models\StudentProfile;
use App\Models\AssessmentAttempt;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class IctTeacherDashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $schoolId = $user->ictSchoolId();
        $school = $schoolId ? School::find($schoolId) : null;

        if (!$schoolId) {
            return view('livewire.dashboard.ict-teacher-dashboard', [
                'user' => $user,
                'school' => null,
                'stats' => $this->emptyStats(),
                'modules' => collect(),
                'recentActivity' => collect(),
            ]);
        }

        $schoolCourses = SchoolCourse::query()
            ->with('course')
            ->where('school_id', $schoolId)
            ->get();

        $courseIds = $schoolCourses->pluck('course_id')->filter()->unique();

        $enrollmentStats = $courseIds->isEmpty()
            ? collect()
            : CourseEnrollment::query()
                ->selectRaw('course_id, COUNT(*) as students_count, AVG(progress_percentage) as avg_progress')
                ->whereIn('course_id', $courseIds)
                ->whereHas('user.studentProfile', function ($query) use ($schoolId) {
                    $query->where('program_type', 'ict')
                        ->where('school_id', $schoolId);
                })
                ->groupBy('course_id')
                ->get()
                ->keyBy('course_id');

        $modules = $schoolCourses->map(function ($schoolCourse) use ($enrollmentStats) {
            $course = $schoolCourse->course;
            $stats = $course ? $enrollmentStats->get($course->id) : null;

            return (object) [
                'id' => $course?->id ?? $schoolCourse->course_id,
                'name' => $course?->title ?? 'Module',
                'is_active' => (bool) $schoolCourse->is_active,
                'students_count' => (int) ($stats->students_count ?? 0),
                'test_progress' => (int) round($stats->avg_progress ?? 0),
            ];
        });

        $activeCourseIds = SchoolCourse::where('school_id', $schoolId)
            ->where('is_active', true)
            ->pluck('course_id');

        $studentQuery = StudentProfile::query()
            ->where('program_type', 'ict')
            ->where('school_id', $schoolId)
            ->where('is_active', true);

        $stats = [
            'totalStudents' => (clone $studentQuery)->count(),
            'examReady' => (clone $studentQuery)->where('exam_readiness_status', 'teacher_approved')->count(),
            'needsPractice' => (clone $studentQuery)->where('exam_readiness_status', 'needs_practice')->count(),
            'activeModules' => $activeCourseIds->count(),
            'modulesNearCompletion' => $this->countModulesNearCompletion($schoolId, $activeCourseIds),
            'pendingExamRequests' => (clone $studentQuery)->where('exam_readiness_status', 'student_requested')->count(),
            'approvedExamSessions' => (clone $studentQuery)->where('exam_readiness_status', 'teacher_approved')->count(),
            'studentsAwaitingResults' => (clone $studentQuery)->where('exam_readiness_status', 'exam_completed')->count(),
            'outstandingBalances' => (clone $studentQuery)->where('uniform_paid', false)->count(),
        ];

        $recentActivity = $this->getRecentActivity($schoolId, $activeCourseIds);

        $recentAssessmentResults = AssessmentAttempt::visibleTo($user)
            ->where('status', 'completed')
            ->with(['assessment.lesson', 'assessment.course', 'user', 'assessment.questions'])
            ->orderByDesc('completed_at')
            ->take(8)
            ->get();

        return view('livewire.dashboard.ict-teacher-dashboard', [
            'user' => $user,
            'school' => $school,
            'stats' => $stats,
            'modules' => $modules,
            'recentActivity' => $recentActivity,
            'recentAssessmentResults' => $recentAssessmentResults,
        ]);
    }

    private function countModulesNearCompletion(int $schoolId, Collection $activeCourseIds): int
    {
        if ($activeCourseIds->isEmpty()) {
            return 0;
        }

        return CourseEnrollment::query()
            ->whereIn('course_id', $activeCourseIds)
            ->where('progress_percentage', '>=', 80)
            ->where('progress_percentage', '<', 100)
            ->whereHas('user.studentProfile', function ($query) use ($schoolId) {
                $query->where('program_type', 'ict')
                    ->where('school_id', $schoolId);
            })
            ->count();
    }

    private function getRecentActivity(int $schoolId, Collection $activeCourseIds): Collection
    {
        $activity = collect();

        if ($activeCourseIds->isNotEmpty()) {
            $recentEnrollments = CourseEnrollment::query()
                ->with(['user', 'course'])
                ->whereIn('course_id', $activeCourseIds)
                ->whereHas('user.studentProfile', function ($query) use ($schoolId) {
                    $query->where('program_type', 'ict')
                        ->where('school_id', $schoolId);
                })
                ->latest('created_at')
                ->take(5)
                ->get()
                ->map(function ($enrollment) {
                    return [
                        'message' => sprintf('%s enrolled in %s', $enrollment->user?->name ?? 'Student', $enrollment->course?->title ?? 'Module'),
                        'time' => $enrollment->created_at,
                    ];
                });

            $activity = $activity->merge($recentEnrollments);
        }

        $recentReadinessUpdates = StudentProfile::query()
            ->where('program_type', 'ict')
            ->where('school_id', $schoolId)
            ->whereIn('exam_readiness_status', [
                'student_requested',
                'teacher_approved',
                'needs_practice',
                'exam_completed',
            ])
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(function ($student) {
                $statusLabel = match ($student->exam_readiness_status) {
                    'student_requested' => 'requested an exam session',
                    'teacher_approved' => 'was marked Exam Ready',
                    'needs_practice' => 'needs more practice',
                    'exam_completed' => 'completed an exam session',
                    default => 'updated readiness status',
                };

                return [
                    'message' => sprintf('%s %s', $student->full_name, $statusLabel),
                    'time' => $student->updated_at,
                ];
            });

        $activity = $activity->merge($recentReadinessUpdates);

        return $activity
            ->sortByDesc('time')
            ->take(8)
            ->values();
    }

    private function emptyStats(): array
    {
        return [
            'totalStudents' => 0,
            'examReady' => 0,
            'needsPractice' => 0,
            'activeModules' => 0,
            'modulesNearCompletion' => 0,
            'pendingExamRequests' => 0,
            'approvedExamSessions' => 0,
            'studentsAwaitingResults' => 0,
            'outstandingBalances' => 0,
        ];
    }
}
