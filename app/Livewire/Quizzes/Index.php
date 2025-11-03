<?php

namespace App\Livewire\Quizzes;

use App\Models\Quiz;
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
        $query = Quiz::query();

        // Role-based filtering
        if (Auth::user()->hasRole('student')) {
            $query->whereHas('lesson.course.enrollments', fn($q) => $q->where('user_id', Auth::id()))
                  ->where('is_published', true);
        } elseif (Auth::user()->hasRole('teacher')) {
            $query->whereHas('lesson.course', fn($q) => $q->where('instructor_id', Auth::id()));
        }

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        if ($this->courseId) {
            $query->whereHas('lesson', fn($q) => $q->where('course_id', $this->courseId));
        }

        $quizzes = $query->with(['lesson.course'])
            ->orderByDesc('created_at')
            ->paginate(12);

        // Get user attempts for students
        $attempts = collect([]); // Always a Collection, even if empty
        if (Auth::user()->hasRole('student')) {
            $quizIds = $quizzes->pluck('id')->toArray();
            if (!empty($quizIds)) {
            $attempts = \App\Models\QuizAttempt::where('user_id', Auth::id())
                ->whereIn('quiz_id', $quizIds)
                ->get()
                ->keyBy('quiz_id');
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
            $allQuizIds = Quiz::whereHas('lesson.course.enrollments', fn($q) => $q->where('user_id', Auth::id()))
                ->where('is_published', true)
                ->pluck('id');
            
            $stats['total'] = $allQuizIds->count();
            
            $allAttempts = \App\Models\QuizAttempt::where('user_id', Auth::id())
                ->whereIn('quiz_id', $allQuizIds)
                ->get();
            
            $completedQuizzes = $allAttempts->groupBy('quiz_id')->keys();
            $stats['completed'] = $completedQuizzes->count();
            
            if ($allAttempts->isNotEmpty()) {
                $stats['average_score'] = round($allAttempts->avg('percentage_score'), 1);
                $stats['perfect_scores'] = $allAttempts->where('percentage_score', 100)->groupBy('quiz_id')->count();
            }
        } elseif (Auth::user()->hasRole('teacher')) {
            $stats['total'] = Quiz::whereHas('lesson.course', fn($q) => $q->where('instructor_id', Auth::id()))->count();
        }

        return view('livewire.quizzes.index', [
            'quizzes' => $quizzes,
            'stats' => $stats,
        ]);
    }
}
