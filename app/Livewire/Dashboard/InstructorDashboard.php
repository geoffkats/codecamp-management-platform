<?php

namespace App\Livewire\Dashboard;

use App\Models\ContentApproval;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Services\TrainerSubmissionQueue;
use App\Support\ProgramScope;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class InstructorDashboard extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $user = Auth::user();
        $queue = app(TrainerSubmissionQueue::class);

        $courses = Course::accessibleBy($user)
            ->withCount([
                'enrollments' => fn ($q) => ProgramScope::applyCourseEnrollmentScope($q, $user),
                'lessons',
                'modules',
            ])
            ->withAvg(['enrollments' => fn ($q) => ProgramScope::applyCourseEnrollmentScope($q, $user)], 'progress_percentage')
            ->latest('updated_at')
            ->paginate(6);

        return view('livewire.dashboard.instructor-dashboard', [
            'user' => $user,
            'stats' => $this->getStats($user),
            'courses' => $courses,
            'pendingGradingCount' => $queue->pendingCount($user),
            'recentSubmissions' => $queue->recentPending($user, 6),
            'pendingApprovals' => $this->getPendingApprovals($user),
            'recentEnrollments' => $this->enrollmentQuery($user)
                ->with(['user:id,name', 'course:id,title'])
                ->latest('enrolled_at')
                ->take(5)
                ->get(),
        ]);
    }

    private function enrollmentQuery($user)
    {
        return ProgramScope::applyCourseEnrollmentScope(
            CourseEnrollment::query()->whereHas('course', fn ($q) => $q->accessibleBy($user)),
            $user
        );
    }

    private function getStats($user): array
    {
        $courses = Course::accessibleBy($user);

        $enrollments = $this->enrollmentQuery($user)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(DISTINCT user_id) as students')
            ->selectRaw('SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed')
            ->selectRaw('SUM(CASE WHEN completed_at IS NULL AND progress_percentage > 0 THEN 1 ELSE 0 END) as active')
            ->selectRaw('AVG(progress_percentage) as avg_progress')
            ->first();

        $total = (int) ($enrollments->total ?? 0);

        return [
            'totalCourses' => (clone $courses)->count(),
            'publishedCourses' => (clone $courses)->where('is_published', true)->count(),
            'students' => (int) ($enrollments->students ?? 0),
            'activeLearners' => (int) ($enrollments->active ?? 0),
            'averageProgress' => round((float) ($enrollments->avg_progress ?? 0)),
            'completionRate' => $total > 0 ? round(((int) $enrollments->completed / $total) * 100) : 0,
            'completed' => (int) ($enrollments->completed ?? 0),
        ];
    }

    private function getPendingApprovals($user)
    {
        $lessonIds = Lesson::whereHas('module.course', fn ($q) => $q->accessibleBy($user))->pluck('id');
        $courseIds = Course::accessibleBy($user)->pluck('id');

        return ContentApproval::where('status', 'pending')
            ->where(function ($q) use ($lessonIds, $courseIds) {
                $q->where(fn ($l) => $l->where('approvable_type', Lesson::class)->whereIn('approvable_id', $lessonIds))
                    ->orWhere(fn ($c) => $c->where('approvable_type', Course::class)->whereIn('approvable_id', $courseIds));
            })
            ->with('approvable')
            ->latest('submitted_at')
            ->take(5)
            ->get();
    }
}
