<?php

namespace App\Livewire\Quizzes;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Take extends Component
{
    public Assessment $assessment;
    public $currentQuestionIndex = 0;
    public $answers = [];
    public $startedAt;
    public $timeRemaining = null;
    public $showResults = false;
    public $attempt = null;
    public $score = 0;
    public $isPassed = false;

    public function mount(Assessment $assessment)
    {
        // Ensure this is a quiz type assessment
        if ($assessment->assessment_type !== 'quiz') {
            abort(404, 'Assessment not found.');
        }

        $this->assessment = $assessment->load(['questions', 'lesson.course', 'course']);
        
        // Check if user can take this quiz
        $this->checkAccess();
        
        // Check existing attempts
        $existingAttempt = AssessmentAttempt::where('user_id', Auth::id())
            ->where('assessment_id', $this->assessment->id)
            ->latest('created_at')
            ->first();

        if ($existingAttempt && !$existingAttempt->completed_at) {
            // Resume existing attempt
            $this->attempt = $existingAttempt;
            $this->answers = $existingAttempt->answers ?? [];
            $this->startedAt = $existingAttempt->started_at;
        } else {
            // Check max attempts
            $attemptCount = AssessmentAttempt::where('user_id', Auth::id())
                ->where('assessment_id', $this->assessment->id)
                ->count();
            
            if ($this->assessment->max_attempts && $attemptCount >= $this->assessment->max_attempts) {
                session()->flash('error', 'You have reached the maximum number of attempts for this quiz.');
                return redirect()->route('quizzes.index');
            }

            // Start new attempt
            $this->startNewAttempt($attemptCount + 1);
        }

        if ($this->assessment->time_limit_minutes) {
            $this->timeRemaining = $this->assessment->time_limit_minutes * 60; // Convert to seconds
        }
    }

    protected function checkAccess()
    {
        $user = Auth::user();
        
        // Check if quiz is locked
        if ($this->assessment->is_locked && !$user->hasAnyRole(['admin', 'supervisor'])) {
            abort(403, 'This quiz is currently locked.');
        }

        // Check if quiz is approved
        if ($this->assessment->approval_status !== 'approved' && !$user->hasAnyRole(['admin', 'supervisor'])) {
            abort(403, 'This quiz is not yet available.');
        }

        // Get the course
        $course = $this->assessment->course ?? $this->assessment->lesson->course ?? null;
        
        if ($course && !$user->hasAnyRole(['admin', 'supervisor'])) {
            // Check if student is enrolled OR course is open
            $isEnrolled = $course->enrollments()
                ->where('user_id', $user->id)
                ->exists();
            
            $isOpen = $course->enrollment_type === 'open';

            if (!$isEnrolled && !$isOpen) {
                abort(403, 'You must be enrolled in this course to take the quiz.');
            }
        }
    }

    protected function startNewAttempt($attemptNumber)
    {
        $this->attempt = AssessmentAttempt::create([
            'user_id' => Auth::id(),
            'assessment_id' => $this->assessment->id,
            'started_at' => now(),
            'answers' => [],
            'status' => 'in_progress',
        ]);

        $this->startedAt = $this->attempt->started_at;
    }

    public function updateAnswer($questionId, $value)
    {
        $this->answers[$questionId] = $value;
        
        // Auto-save to attempt
        if ($this->attempt) {
            $this->attempt->update(['answers' => $this->answers]);
        }
    }

    public function updateMultipleAnswer($questionId, $optionId, $checked)
    {
        if (!isset($this->answers[$questionId])) {
            $this->answers[$questionId] = [];
        }

        if ($checked) {
            $this->answers[$questionId][] = $optionId;
            $this->answers[$questionId] = array_unique($this->answers[$questionId]);
        } else {
            $this->answers[$questionId] = array_filter($this->answers[$questionId], fn($id) => $id != $optionId);
        }
        
        $this->answers[$questionId] = array_values($this->answers[$questionId]);
        
        if ($this->attempt) {
            $this->attempt->update(['answers' => $this->answers]);
        }
    }

    /**
     * Livewire hook called when the answers property is updated.
     * Auto-save the answers to the QuizAttempt so selections persist.
     */
    public function updatedAnswers($value)
    {
        if ($this->attempt) {
            $this->attempt->update(['answers' => $this->answers]);
        }
    }

    public function nextQuestion()
    {
        if ($this->currentQuestionIndex < $this->getQuestions()->count() - 1) {
            $this->currentQuestionIndex++;
        }
    }

    public function previousQuestion()
    {
        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
        }
    }

    public function goToQuestion($index)
    {
        if ($index >= 0 && $index < $this->getQuestions()->count()) {
            $this->currentQuestionIndex = $index;
        }
    }

    public function submitQuiz()
    {
        if (!$this->attempt) {
            return;
        }

        $questions = $this->getQuestions();
        $totalPoints = $questions->sum('points');
        $earnedPoints = 0;

        foreach ($questions as $question) {
            $earnedPoints += $this->calculateQuestionScore($question);
        }

        $this->score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
        $this->isPassed = $this->score >= $this->assessment->passing_score;

        $timeSpent = $this->startedAt ? now()->diffInMinutes($this->startedAt) : 0;

        $this->attempt->update([
            'answers' => $this->answers,
            'score' => $this->score,
            'is_passed' => $this->isPassed,
            'completed_at' => now(),
        ]);

        // Award XP if passed
        if ($this->isPassed && $this->assessment->lesson->xp_reward) {
            $user = Auth::user();
            if (!$user->points) {
                \App\Models\UserPoint::create([
                    'user_id' => $user->id,
                    'total_points' => 0,
                    'level' => 1,
                ]);
            }
            $user->points->increment('total_points', $this->assessment->lesson->xp_reward);
        }

        // Check and award badges for perfect quiz scores
        if ($this->score >= 100) {
            $badgeService = app(\App\Services\BadgeAwardingService::class);
            $badgeService->checkPerfectQuizBadges(Auth::user());
        }

        $this->showResults = true;

        session()->flash('message', $this->isPassed 
            ? 'Congratulations! You passed the quiz.' 
            : 'You did not pass. Keep studying!');
    }

    protected function calculateQuestionScore($question)
    {
        $userAnswer = $this->answers[$question->id] ?? null;
        
        if (!$userAnswer) {
            return 0;
        }

        $correctOptions = $question->options->where('is_correct', true)->pluck('id')->toArray();
        
        if ($question->question_type === 'multiple_choice') {
            if (is_array($userAnswer)) {
                sort($userAnswer);
                sort($correctOptions);
                return $userAnswer === $correctOptions ? $question->points : 0;
            } else {
                return in_array($userAnswer, $correctOptions) ? $question->points : 0;
            }
        }

        return 0;
    }

    protected function getQuestions()
    {
        $questions = $this->assessment->questions;
        
        // If no questions loaded, return empty collection
        if (!$questions || $questions->isEmpty()) {
            return collect([]);
        }
        
        if ($this->assessment->is_randomized) {
            return $questions->shuffle();
        }
        
        return $questions;
    }

    public function render()
    {
        $questions = $this->getQuestions();
        $currentQuestion = $questions->get($this->currentQuestionIndex);
        $totalQuestions = $questions->count();
        $answeredCount = count(array_filter($this->answers));

        return view('livewire.quizzes.take', [
            'questions' => $questions,
            'currentQuestion' => $currentQuestion,
            'totalQuestions' => $totalQuestions,
            'answeredCount' => $answeredCount,
            'progress' => $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100, 1) : 0,
        ]);
    }
}

