<?php

namespace App\Livewire\Dashboard;

use App\Models\ContentApproval;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\DailyChallenge;
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

    private function getStats($user): array
    {
        $courses = Course::where('instructor_id', $user->id);
        $totalEnrollments = CourseEnrollment::whereHas('course', fn($q) => $q->where('instructor_id', $user->id))->count();
        
        return [
            'totalCourses' => $courses->count(),
            'publishedCourses' => $courses->where('is_published', true)->count(),
            'draftCourses' => $courses->where('approval_status', 'draft')->count(),
            'pendingApprovals' => $courses->where('approval_status', 'pending')->count(),
            'totalEnrollments' => $totalEnrollments,
            'activeStudents' => (int) (CourseEnrollment::whereHas('course', fn($q) => $q->where('instructor_id', $user->id))
                ->whereNotNull('enrolled_at')
                ->selectRaw('COUNT(DISTINCT user_id) as count')
                ->first()
                ?->count ?? 0),
        ];
    }

    private function getPendingApprovals($user)
    {
        return ContentApproval::whereHas('approvable', function ($query) use ($user) {
            $query->where('instructor_id', $user->id);
        })
        ->where('status', 'pending')
        ->with(['approvable', 'submitter'])
        ->latest('submitted_at')
        ->take(5)
        ->get();
    }

    private function getRecentEnrollments($user)
    {
        return CourseEnrollment::whereHas('course', fn($q) => $q->where('instructor_id', $user->id))
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
        // Get assignment submissions that need grading
        return \App\Models\AssignmentSubmission::whereHas('assignment.course', fn($q) => $q->where('instructor_id', $user->id))
            ->whereNull('graded_at')
            ->with(['assignment', 'user'])
            ->latest()
            ->take(10)
            ->get();
    }

    private function getStudentAnalytics($user)
    {
        $enrollments = CourseEnrollment::whereHas('course', fn($q) => $q->where('instructor_id', $user->id))
            ->get();

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
        $completions = CourseEnrollment::whereHas('course', fn($q) => $q->where('instructor_id', $user->id))
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
        return CourseEnrollment::whereHas('course', fn($q) => $q->where('instructor_id', $user->id))
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => ['date' => $item->date, 'count' => $item->count]);
    }

    private function getTopPerformers($user)
    {
        return CourseEnrollment::whereHas('course', fn($q) => $q->where('instructor_id', $user->id))
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
