<?php

namespace App\Livewire\Quizzes;

use App\Models\Assessment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filter = 'all'; // 'all', 'available', 'completed'
    public $courseId = null;

    public function render()
    {
        $user = Auth::user();
        $query = Assessment::where('assessment_type', 'quiz');

        // Role-based filtering
        if ($user->hasRole('student')) {
            // Students can see quizzes from:
            // 1. Courses they're enrolled in
            // 2. Open courses (enrollment_type = 'open')
            // AND the quiz must be approved AND not locked
            $query->where('approval_status', 'approved')
                  ->where('is_locked', false)
                  ->where(function ($lessonLock) {
                      $lessonLock->whereNull('lesson_id')
                          ->orWhereHas('lesson', fn ($lessonQuery) => $lessonQuery->where('is_locked', false));
                  })
                  ->where(function($q) use ($user) {
                      // From enrolled courses
                      $q->whereHas('course.enrollments', fn($enrollmentQuery) => $enrollmentQuery->where('user_id', $user->id))
                        // OR from open courses
                        ->orWhereHas('course', fn($courseQuery) => $courseQuery->where('enrollment_type', 'open'))
                        // OR from lessons in enrolled courses
                        ->orWhereHas('lesson.course.enrollments', fn($enrollmentQuery) => $enrollmentQuery->where('user_id', $user->id))
                        // OR from lessons in open courses
                        ->orWhereHas('lesson.course', fn($courseQuery) => $courseQuery->where('enrollment_type', 'open'));
                  });
        } elseif ($user->hasRole('teacher')) {
            // Teachers see quizzes from their courses
            $query->where(function($q) use ($user) {
                $q->whereHas('course', fn($courseQuery) => $courseQuery->where('instructor_id', $user->id))
                  ->orWhereHas('lesson.course', fn($courseQuery) => $courseQuery->where('instructor_id', $user->id));
            });
        } elseif ($user->hasAnyRole(['admin', 'supervisor'])) {
            // Admins and supervisors see all quizzes
            // No additional filtering needed
        }

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        if ($this->courseId) {
            $query->whereHas('lesson', fn($q) => $q->where('course_id', $this->courseId));
        }

        $quizzes = $query->with(['lesson.course', 'course'])
            ->orderByDesc('created_at')
            ->paginate(12);

        // Get user attempts for students
        $attempts = collect([]); // Always a Collection, even if empty
        if (Auth::user()->hasRole('student')) {
            $quizIds = $quizzes->pluck('id')->toArray();
            if (!empty($quizIds)) {
            $attempts = \App\Models\AssessmentAttempt::where('user_id', Auth::id())
                ->whereIn('assessment_id', $quizIds)
                ->get()
                ->keyBy('assessment_id');
            }
        }

        $quizzes->getCollection()->transform(function ($quiz) use ($attempts) {
            $quiz->best_attempt = $attempts->get($quiz->id);
            return $quiz;
        });

        // Calculate stats for students
        $stats = [
            'total' => 0,
            'completed' => 0,
            'average_score' => 0,
            'perfect_scores' => 0,
        ];

        if (Auth::user()->hasRole('student')) {
            $allQuizIds = Assessment::where('assessment_type', 'quiz')
                ->where('approval_status', 'approved')
                ->where('is_locked', false)
                ->where(function($q) use ($user) {
                    $q->whereHas('course.enrollments', fn($enrollmentQuery) => $enrollmentQuery->where('user_id', $user->id))
                      ->orWhereHas('course', fn($courseQuery) => $courseQuery->where('enrollment_type', 'open'))
                      ->orWhereHas('lesson.course.enrollments', fn($enrollmentQuery) => $enrollmentQuery->where('user_id', $user->id))
                      ->orWhereHas('lesson.course', fn($courseQuery) => $courseQuery->where('enrollment_type', 'open'));
                })
                ->pluck('id');
            
            $stats['total'] = $allQuizIds->count();
            
            $allAttempts = \App\Models\AssessmentAttempt::where('user_id', Auth::id())
                ->whereIn('assessment_id', $allQuizIds)
                ->get();
            
            $completedQuizzes = $allAttempts->groupBy('assessment_id')->keys();
            $stats['completed'] = $completedQuizzes->count();
            
            if ($allAttempts->isNotEmpty()) {
                $stats['average_score'] = round($allAttempts->avg('score'), 1);
                $stats['perfect_scores'] = $allAttempts->where('score', 100)->groupBy('assessment_id')->count();
            }
        } elseif (Auth::user()->hasRole('teacher')) {
            $stats['total'] = Assessment::where('assessment_type', 'quiz')
                ->where(function($q) use ($user) {
                    $q->whereHas('course', fn($courseQuery) => $courseQuery->where('instructor_id', $user->id))
                      ->orWhereHas('lesson.course', fn($courseQuery) => $courseQuery->where('instructor_id', $user->id));
                })
                ->count();
        }

        return view('livewire.quizzes.index', [
            'quizzes' => $quizzes,
            'stats' => $stats,
        ]);
    }
}
