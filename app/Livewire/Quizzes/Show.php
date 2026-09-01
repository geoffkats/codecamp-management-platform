<?php

namespace App\Livewire\Quizzes;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Assessment $assessment;
    public $userAttempts = [];
    public $hasTaken = false;
    public $bestScore = null;
    public $bestAttempt = null;
    public $attemptsRemaining = null;

    public function mount(Assessment $assessment)
    {
        // Ensure this is a quiz type assessment
        if ($assessment->assessment_type !== 'quiz') {
            abort(404, 'Assessment not found.');
        }

        $this->assessment = $assessment->load([
            'lesson.course',
            'course',
            'questions',
        ]);

        // Check access
        $this->checkAccess();

        // Load user attempts
        $this->userAttempts = AssessmentAttempt::where('user_id', Auth::id())
            ->where('assessment_id', $this->assessment->id)
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
        $attemptCount = AssessmentAttempt::where('user_id', Auth::id())
            ->where('assessment_id', $this->assessment->id)
            ->count();

        if ($this->assessment->max_attempts && $this->assessment->max_attempts > 0) {
            $this->attemptsRemaining = max(0, $this->assessment->max_attempts - $attemptCount);
        } else {
            $this->attemptsRemaining = 'unlimited';
        }
    }

    protected function checkAccess()
    {
        $user = Auth::user();
        
        if ($user->hasRole('student')) {
            // Check if quiz is locked
            if ($this->assessment->is_locked || $this->assessment->lesson?->is_locked) {
                abort(403, 'This quiz is currently locked and cannot be accessed.');
            }

            // Check if quiz is approved
            if ($this->assessment->approval_status !== 'approved') {
                abort(403, 'This quiz is not yet available.');
            }

            // Students must be enrolled in the course OR course must be open
            $course = $this->assessment->course ?? $this->assessment->lesson->course ?? null;
            
            if ($course) {
                $isEnrolled = $course->enrollments()
                    ->where('user_id', $user->id)
                    ->exists();
                
                $isOpen = $course->enrollment_type === 'open';
                
                if (!$isEnrolled && !$isOpen) {
                    abort(403, 'You must be enrolled in this course to view the quiz.');
                }
            }
        } elseif ($user->hasRole('teacher')) {
            // Teachers can only view quizzes from their own courses
            $course = $this->assessment->course ?? $this->assessment->lesson->course ?? null;
            if (!$course || $course->instructor_id !== $user->id) {
                abort(403, 'You can only view quizzes from your own courses.');
            }
        }
        // Admins and supervisors can view all quizzes
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
        if (!Auth::user()->hasAnyRole(['teacher', 'admin', 'supervisor'])) {
            return null;
        }

        $allAttempts = AssessmentAttempt::visibleTo(Auth::user())
            ->where('assessment_id', $this->assessment->id)
            ->get();

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
