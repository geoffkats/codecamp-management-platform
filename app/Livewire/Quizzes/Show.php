<?php

namespace App\Livewire\Quizzes;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Quiz $quiz;
    public $userAttempts = [];
    public $hasTaken = false;
    public $bestScore = null;
    public $bestAttempt = null;
    public $attemptsRemaining = null;

    public function mount(Quiz $quiz)
    {
        $this->quiz = $quiz->load([
            'lesson.course',
            'questions' => function ($q) {
                $q->orderBy('order');
            },
            'questions.options',
        ]);

        // Check access
        $this->checkAccess();

        // Load user attempts
        $this->userAttempts = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $this->quiz->id)
            ->latest('completed_at')
            ->take(5)
            ->get();

        $this->hasTaken = $this->userAttempts->count() > 0;

        if ($this->hasTaken) {
            $completedAttempts = $this->userAttempts->whereNotNull('completed_at');
            if ($completedAttempts->isNotEmpty()) {
                $this->bestAttempt = $completedAttempts->sortByDesc('score')->first();
                $this->bestScore = $this->bestAttempt->score;
            }
        }

        // Calculate remaining attempts
        $attemptCount = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $this->quiz->id)
            ->count();

        if ($this->quiz->max_attempts && $this->quiz->max_attempts > 0) {
            $this->attemptsRemaining = max(0, $this->quiz->max_attempts - $attemptCount);
        } else {
            $this->attemptsRemaining = 'unlimited';
        }
    }

    protected function checkAccess()
    {
        if (Auth::user()->hasRole('student')) {
            // Students must be enrolled in the course
            $isEnrolled = $this->quiz->lesson->course->enrollments()
                ->where('user_id', Auth::id())
                ->exists();

            if (!$isEnrolled) {
                abort(403, 'You must be enrolled in this course to view the quiz.');
            }

            // Quiz must be published
            if (!$this->quiz->is_published) {
                abort(403, 'This quiz is not yet published.');
            }
        } elseif (Auth::user()->hasRole('teacher')) {
            // Teachers can only view quizzes from their own courses
            $course = $this->quiz->lesson->course ?? null;
            if (!$course || $course->instructor_id !== Auth::id()) {
                abort(403, 'You can only view quizzes from your own courses.');
            }
        }
    }

    public function render()
    {
        $stats = $this->getStatistics();

        return view('livewire.quizzes.show', [
            'stats' => $stats,
        ]);
    }

    private function getStatistics()
    {
        // Only show stats to teachers/admins
        if (!Auth::user()->hasAnyRole(['teacher', 'admin'])) {
            return null;
        }

        $allAttempts = QuizAttempt::where('quiz_id', $this->quiz->id)->get();

        if ($allAttempts->isEmpty()) {
            return [
                'total_attempts' => 0,
                'average_score' => 0,
                'pass_rate' => 0,
                'completion_rate' => 0,
            ];
        }

        $totalAttempts = $allAttempts->count();
        $completedAttempts = $allAttempts->whereNotNull('completed_at');
        $averageScore = $completedAttempts->avg('score') ?? 0;
        $passedCount = $completedAttempts->where('is_passed', true)->count();
        $passRate = $completedAttempts->count() > 0 ? ($passedCount / $completedAttempts->count()) * 100 : 0;
        $completionRate = ($completedAttempts->count() / $totalAttempts) * 100;

        return [
            'total_attempts' => $totalAttempts,
            'average_score' => round($averageScore, 2),
            'pass_rate' => round($passRate, 2),
            'completion_rate' => round($completionRate, 2),
        ];
    }
}
