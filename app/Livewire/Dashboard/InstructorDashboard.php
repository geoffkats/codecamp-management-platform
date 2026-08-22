<?php

namespace App\Livewire\Dashboard;

use App\Models\ContentApproval;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\DailyChallenge;
use App\Support\ProgramScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class InstructorDashboard extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        Cache::forget('instructor_dashboard_' . Auth::id());
    }

    public function render()
    {
        $user = Auth::user();

        $dashboardData = Cache::remember(
            'instructor_dashboard_' . $user->id,
            now()->addMinutes(5),
            function () use ($user) {
                return [
                    'stats' => $this->getStats($user),
                    'pendingApprovals' => $this->getPendingApprovals($user),
                    'recentEnrollments' => $this->getRecentEnrollments($user),
                    'topPerformingCourses' => $this->getTopPerformingCourses($user),
                ];
            }
        );

        // Get courses
        $courses = Course::where('instructor_id', $user->id)
            ->with(['enrollments', 'modules', 'lessons'])
            ->withCount(['enrollments', 'lessons'])
            ->orderBy('created_at', 'desc')
            ->paginate(6);

        // Recent submissions awaiting grading
        $recentSubmissions = $this->getRecentSubmissions($user);

        // Student analytics
        $studentAnalytics = $this->getStudentAnalytics($user);

        return view('livewire.dashboard.instructor-dashboard', [
            'user' => $user,
            'stats' => $dashboardData['stats'],
            'pendingApprovals' => $dashboardData['pendingApprovals'],
            'recentEnrollments' => $dashboardData['recentEnrollments'],
            'topPerformingCourses' => $dashboardData['topPerformingCourses'],
            'courses' => $courses,
            'recentSubmissions' => $recentSubmissions,
            'studentAnalytics' => $studentAnalytics,
        ]);
    }

    private function enrollmentQuery($user)
    {
        return ProgramScope::applyCourseEnrollmentScope(
            CourseEnrollment::query()->whereHas('course', fn ($q) => $q->where('instructor_id', $user->id)),
            $user
        );
    }

    private function getStats($user): array
    {
        $courses = Course::where('instructor_id', $user->id);
        $totalEnrollments = $this->enrollmentQuery($user)->count();
        
        return [
            'totalCourses' => $courses->count(),
            'publishedCourses' => $courses->where('is_published', true)->count(),
            'draftCourses' => $courses->where('approval_status', 'draft')->count(),
            'pendingApprovals' => $courses->where('approval_status', 'pending')->count(),
            'totalEnrollments' => $totalEnrollments,
            'activeStudents' => (int) ($this->enrollmentQuery($user)
                ->whereNotNull('enrolled_at')
                ->selectRaw('COUNT(DISTINCT user_id) as count')
                ->first()
                ?->count ?? 0),
        ];
    }

    private function getPendingApprovals($user)
    {
        // Get IDs of lessons from courses this user owns
        $lessonIds = \App\Models\Lesson::whereHas('module.course', function ($q) use ($user) {
            $q->where('instructor_id', $user->id);
        })->pluck('id');

        // Get IDs of courses this user owns
        $courseIds = Course::where('instructor_id', $user->id)->pluck('id');

        // Get pending approvals for lessons
        $lessonApprovals = ContentApproval::where('approvable_type', 'App\Models\Lesson')
            ->whereIn('approvable_id', $lessonIds)
            ->where('status', 'pending')
            ->with(['approvable', 'submitter'])
            ->get();

        // Get pending approvals for courses
        $courseApprovals = ContentApproval::where('approvable_type', 'App\Models\Course')
            ->whereIn('approvable_id', $courseIds)
            ->where('status', 'pending')
            ->with(['approvable', 'submitter'])
            ->get();

        // Combine, sort by submitted_at, and take 5
        return collect($lessonApprovals)
            ->concat($courseApprovals)
            ->sortByDesc('submitted_at')
            ->take(5)
            ->values();
    }

    private function getRecentEnrollments($user)
    {
        return $this->enrollmentQuery($user)
            ->with(['user', 'course'])
            ->latest('enrolled_at')
            ->take(10)
            ->get();
    }

    private function getTopPerformingCourses($user)
    {
        return Course::where('instructor_id', $user->id)
            ->withCount(['enrollments', 'lessons'])
            ->with(['enrollments' => function ($q) {
                $q->selectRaw('course_id, AVG(progress_percentage) as avg_progress, COUNT(*) as student_count')
                  ->groupBy('course_id');
            }])
            ->orderBy('enrollments_count', 'desc')
            ->take(5)
            ->get();
    }

    private function getRecentSubmissions($user)
    {
        $enrolledUserIds = $this->enrollmentQuery($user)->pluck('user_id');

        $assessmentSubmissions = \App\Models\AssessmentAttempt::query()
            ->whereHas('assessment', fn ($q) => $q
                ->where('assessment_type', 'assignment')
                ->whereHas('course', fn ($cq) => $cq->where('instructor_id', $user->id))
            )
            ->whereIn('user_id', $enrolledUserIds)
            ->where('status', 'completed')
            ->whereNull('score')
            ->with(['assessment', 'user'])
            ->latest('completed_at')
            ->take(10)
            ->get()
            ->map(fn ($attempt) => (object) [
                'title' => $attempt->assessment->title,
                'user' => $attempt->user,
                'submission' => $attempt,
            ]);

        $legacySubmissions = \App\Models\AssignmentSubmission::whereHas('assignment.course', fn ($q) => $q->where('instructor_id', $user->id))
            ->whereIn('user_id', $enrolledUserIds)
            ->whereNull('graded_at')
            ->with(['assignment', 'user'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($submission) => (object) [
                'title' => $submission->assignment->title,
                'user' => $submission->user,
                'submission' => $submission,
            ]);

        return $assessmentSubmissions
            ->concat($legacySubmissions)
            ->sortByDesc(fn ($item) => $item->submission->completed_at ?? $item->submission->created_at ?? now())
            ->take(10)
            ->values();
    }

    private function getStudentAnalytics($user)
    {
        $enrollments = $this->enrollmentQuery($user)->get();

        $total = $enrollments->count();
        $completed = $enrollments->whereNotNull('completed_at')->count();
        
        return [
            'averageProgress' => round($enrollments->avg('progress_percentage') ?? 0, 2),
            'completionRate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'averageScore' => round($enrollments->avg('average_quiz_score') ?? 0, 2),
            'totalLessonsCompleted' => $enrollments->sum('lessons_completed'),
            'activeStudents' => $enrollments->whereNull('completed_at')->where('progress_percentage', '>', 0)->count(),
            'totalStudents' => $enrollments->unique('user_id')->count(),
            'avgCompletionTime' => $this->getAverageCompletionTime($user),
            'enrollmentTrends' => $this->getEnrollmentTrends($user),
            'topPerformers' => $this->getTopPerformers($user),
        ];
    }

    private function getAverageCompletionTime($user)
    {
        $completions = $this->enrollmentQuery($user)
            ->whereNotNull('completed_at')
            ->whereNotNull('enrolled_at')
            ->get()
            ->map(function ($enrollment) {
                return $enrollment->enrolled_at->diffInDays($enrollment->completed_at);
            });

        return $completions->count() > 0 ? round($completions->avg(), 1) : 0;
    }

    private function getEnrollmentTrends($user)
    {
        return $this->enrollmentQuery($user)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => ['date' => $item->date, 'count' => $item->count]);
    }

    private function getTopPerformers($user)
    {
        return $this->enrollmentQuery($user)
            ->with('user')
            ->whereNotNull('completed_at')
            ->orderByDesc('average_quiz_score')
            ->take(5)
            ->get()
            ->map(function ($enrollment) {
                return [
                    'name' => $enrollment->user->name,
                    'score' => round($enrollment->average_quiz_score ?? 0, 2),
                    'progress' => round($enrollment->progress_percentage ?? 0, 2),
                    'course' => $enrollment->course->title,
                ];
            });
    }

    public function getApprovableTitle($approval): string
    {
        $approvable = $approval->approvable;
        if (!$approvable) return 'Unknown';
        
        return match(class_basename($approval->approvable_type)) {
            'Course' => $approvable->title,
            'CourseModule' => $approvable->title,
            'Lesson' => $approvable->title,
            'Assessment' => $approvable->title,
            default => 'Unknown Content',
        };
    }
}
