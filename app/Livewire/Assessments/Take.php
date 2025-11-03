<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Take extends Component
{
    use WithFileUploads;

    public Assessment $assessment;
    public $currentQuestionIndex = 0;
    public $answers = [];
    public $submissionText = '';
    public $submissionFiles = [];
    public $tempFiles = [];
    public $startedAt;
    public $timeRemaining = null;
    public $showResults = false;
    public $showReviewScreen = false;
    public $attempt = null;
    public $score = 0;
    public $passed = false;
    public $percentage = 0;
    public $isPassed = false;
    public $bookmarkedQuestions = [];
    public $flaggedQuestions = [];
    public $autoSaveEnabled = true;
    public $lastSavedAt = null;
    public $randomizedQuestions = null; // Store randomized questions for consistency

    public function mount(Assessment $assessment)
    {
        $this->assessment = $assessment->load(['questions.options', 'course', 'lesson']);
        
        // Check if user can take this assessment
        $this->checkAccess();
        
        // Check existing attempts
        $existingAttempt = AssessmentAttempt::where('user_id', Auth::id())
            ->where('assessment_id', $this->assessment->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if ($existingAttempt && !$existingAttempt->completed_at) {
            // Resume existing attempt
            $this->attempt = $existingAttempt;
            $this->answers = $existingAttempt->answers ?? [];
            $this->startedAt = $existingAttempt->started_at;
            
            // Restore submission data for assignments
            if ($this->assessment->assessment_type === 'assignment' && isset($this->answers['submission_text'])) {
                $this->submissionText = $this->answers['submission_text'];
            }
        } else {
            // Check max attempts
            $attemptCount = AssessmentAttempt::where('user_id', Auth::id())
                ->where('assessment_id', $this->assessment->id)
                ->where('status', 'completed')
                ->count();
            
            if ($this->assessment->max_attempts && $attemptCount >= $this->assessment->max_attempts) {
                session()->flash('error', 'You have reached the maximum number of attempts for this assessment.');
                return redirect()->route('assessments.show', $this->assessment);
            }

            // Start new attempt
            $this->startNewAttempt($attemptCount + 1);
            
            // Randomize questions/options once at start of attempt
            $this->randomizedQuestions = $this->getQuestions();
        }

        if ($this->assessment->time_limit_minutes) {
            $this->timeRemaining = $this->assessment->time_limit_minutes * 60; // Convert to seconds
        }
    }

    public function hydrate()
    {
        // Reload assessment with relationships after Livewire hydration
        // This is needed because Livewire doesn't preserve relationships when serializing
        if ($this->assessment && $this->assessment->id) {
            $this->assessment = Assessment::with(['questions.options', 'course', 'lesson'])
                ->findOrFail($this->assessment->id);
        }
    }

    protected function checkAccess()
    {
        // Check if student is enrolled
        $isEnrolled = $this->assessment->course->enrollments()
            ->where('user_id', Auth::id())
            ->exists();

        if (!$isEnrolled && !Auth::user()->hasRole('admin')) {
            abort(403, 'You must be enrolled in this course to take the assessment.');
        }

        if ($this->assessment->is_locked && !Auth::user()->hasAnyRole(['admin', 'teacher'])) {
            abort(403, 'This assessment is locked.');
        }

        if ($this->assessment->approval_status !== 'approved' && !Auth::user()->hasAnyRole(['admin', 'teacher'])) {
            abort(403, 'This assessment is not yet approved.');
        }
    }

    protected function startNewAttempt($attemptNumber)
    {
        $this->attempt = AssessmentAttempt::create([
            'user_id' => Auth::id(),
            'assessment_id' => $this->assessment->id,
            'started_at' => now(),
            'status' => 'in_progress',
            'answers' => [],
        ]);

        $this->startedAt = $this->attempt->started_at;
    }

    public function updateAnswer($questionId, $value)
    {
        $this->answers[$questionId] = $value;
        
        // Auto-save to attempt
        if ($this->attempt) {
            $this->attempt->update(['answers' => $this->answers]);
            $this->lastSavedAt = now();
            $this->dispatch('progress-saved');
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
            $this->lastSavedAt = now();
            $this->dispatch('progress-saved');
        }
    }

    public function nextQuestion()
    {
        $questions = $this->getQuestions();
        if ($this->currentQuestionIndex < $questions->count() - 1) {
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
        $questions = $this->getQuestions();
        if ($index >= 0 && $index < $questions->count()) {
            $this->currentQuestionIndex = $index;
        }
    }

    public function toggleBookmark($index)
    {
        if (in_array($index, $this->bookmarkedQuestions)) {
            $this->bookmarkedQuestions = array_values(array_filter($this->bookmarkedQuestions, fn($i) => $i !== $index));
        } else {
            $this->bookmarkedQuestions[] = $index;
            $this->bookmarkedQuestions = array_unique($this->bookmarkedQuestions);
            sort($this->bookmarkedQuestions);
        }
    }

    public function toggleFlag($index)
    {
        if (in_array($index, $this->flaggedQuestions)) {
            $this->flaggedQuestions = array_values(array_filter($this->flaggedQuestions, fn($i) => $i !== $index));
        } else {
            $this->flaggedQuestions[] = $index;
            $this->flaggedQuestions = array_unique($this->flaggedQuestions);
            sort($this->flaggedQuestions);
        }
    }

    public function showReview()
    {
        $this->showReviewScreen = true;
    }

    public function hideReview()
    {
        $this->showReviewScreen = false;
    }

    public function confirmSubmit()
    {
        $this->hideReview();
        $this->submitAssessment();
    }

    public function removeFile($questionId, $fileIndex)
    {
        if (isset($this->tempFiles[$questionId][$fileIndex])) {
            unset($this->tempFiles[$questionId][$fileIndex]);
            $this->tempFiles[$questionId] = array_values($this->tempFiles[$questionId]);
        }
    }

    public function saveProgress()
    {
        if ($this->attempt) {
            $this->attempt->update(['answers' => $this->answers]);
            $this->lastSavedAt = now();
            $this->dispatch('progress-saved');
        }
    }

    public function updateTimer()
    {
        if ($this->timeRemaining !== null && $this->timeRemaining > 0) {
            $this->timeRemaining--;
            
            if ($this->timeRemaining <= 0) {
                // Time's up - auto-submit
                $this->submitAssessment();
            }
        }
    }

    public function submitAssessment()
    {
        if (!$this->attempt) {
            return;
        }

        // Handle assignment submission
        if ($this->assessment->assessment_type === 'assignment') {
            $this->validate([
                'submissionText' => 'required|min:10',
                'submissionFiles.*' => 'nullable|file|max:10240', // 10MB max
            ]);

            $filePaths = [];
            if (!empty($this->submissionFiles)) {
                foreach ($this->submissionFiles as $file) {
                    $path = $file->store('assessments/submissions', 'public');
                    $filePaths[] = $path;
                }
            }

            $this->answers = [
                'submission_text' => $this->submissionText,
                'files' => $filePaths,
            ];

            // For assignments, score is null until graded
            $this->attempt->update([
                'answers' => $this->answers,
                'completed_at' => now(),
                'status' => 'completed',
                'score' => null,
                'time_spent' => $this->startedAt ? now()->diffInMinutes($this->startedAt) : 0,
            ]);

            $this->showResults = true;
            $this->dispatch('assessment-completed');
            session()->flash('message', 'Assignment submitted successfully! Waiting for instructor review.');
            return;
        }

        // Handle quiz-type assessments
        $questions = $this->getQuestions();
        $totalPoints = $questions->sum('points');
        $earnedPoints = 0;

        foreach ($questions as $question) {
            $earnedPoints += $this->calculateQuestionScore($question);
        }

        $this->score = $earnedPoints;
        $this->percentage = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
        $this->isPassed = $this->percentage >= $this->assessment->passing_score;
        $this->passed = $this->isPassed;

        $timeSpent = $this->startedAt ? now()->diffInMinutes($this->startedAt) : 0;

        $this->attempt->update([
            'answers' => $this->answers,
            'score' => $this->score,
            'is_passed' => $this->isPassed,
            'completed_at' => now(),
            'status' => 'completed',
            'time_spent' => $timeSpent,
        ]);

        // Award XP if passed
        if ($this->isPassed && $this->assessment->xp_reward) {
            $user = Auth::user();
            if (!$user->points) {
                \App\Models\UserPoint::create([
                    'user_id' => $user->id,
                    'total_points' => 0,
                    'level' => 1,
                ]);
            }
            $user->points->increment('total_points', $this->assessment->xp_reward);
        }

        // Check and award badges for perfect scores
        if ($this->percentage >= 100) {
            $badgeService = app(\App\Services\BadgeAwardingService::class);
            $badgeService->checkPerfectQuizBadges(Auth::user());
        }

        $this->showResults = true;
        $this->dispatch('assessment-completed');

        session()->flash('message', $this->isPassed 
            ? 'Congratulations! You passed the assessment.' 
            : 'You did not pass. Keep studying!');
    }

    protected function calculateQuestionScore($question)
    {
        $userAnswer = $this->answers[$question->id] ?? null;
        
        if (!$userAnswer) {
            return 0;
        }

        // For multiple choice/select questions
        if (in_array($question->question_type, ['multiple_choice', 'multiple_select', 'choice', 'true_false'])) {
            $correctOptions = $question->options->where('is_correct', true)->pluck('id')->toArray();
            
            if (is_array($userAnswer)) {
                sort($userAnswer);
                sort($correctOptions);
                return $userAnswer === $correctOptions ? $question->points : 0;
            } else {
                return in_array($userAnswer, $correctOptions) ? $question->points : 0;
            }
        }

        // For fill_blank questions
        if ($question->question_type === 'fill_blank') {
            return $this->scoreFillBlankQuestion($question);
        }

        // For ordering questions
        if ($question->question_type === 'ordering') {
            return $this->scoreOrderingQuestion($question);
        }

        // For matching questions
        if ($question->question_type === 'matching') {
            return $this->scoreMatchingQuestion($question);
        }

        // For rating questions - award points if answered
        if ($question->question_type === 'rating') {
            return $question->points;
        }

        // For essay, short_answer, code_submission, file_upload - manual grading required
        // Return 0 for now, teacher will grade manually
        return 0;
    }

    protected function scoreFillBlankQuestion($question)
    {
        $settings = $question->settings ?? [];
        $blanks = $settings['fill_blank']['blanks'] ?? [];
        $userAnswers = $this->answers[$question->id] ?? [];
        
        // Fallback for legacy data stored in options
        if (empty($blanks) && $question->options->isNotEmpty()) {
            $blanks = [];
            foreach ($question->options as $option) {
                $blanks[] = [
                    'correct_answer' => $option->option_text,
                    'case_sensitive' => false,
                    'alternative_answers' => []
                ];
            }
        }
        
        if (empty($blanks)) {
            return 0;
        }
        
        $correct = 0;
        foreach ($blanks as $index => $blank) {
            $userAnswer = trim($userAnswers[$index] ?? '');
            $correctAnswer = trim($blank['correct_answer'] ?? '');
            
            if (empty($userAnswer) || empty($correctAnswer)) {
                continue;
            }
            
            $caseSensitive = $blank['case_sensitive'] ?? false;
            $matched = $caseSensitive 
                ? $userAnswer === $correctAnswer
                : strtolower($userAnswer) === strtolower($correctAnswer);
            
            // Check alternatives
            if (!$matched && !empty($blank['alternative_answers'])) {
                foreach ($blank['alternative_answers'] as $alt) {
                    $matched = $caseSensitive 
                        ? $userAnswer === trim($alt)
                        : strtolower($userAnswer) === strtolower(trim($alt));
                    if ($matched) break;
                }
            }
            
            if ($matched) $correct++;
        }
        
        $totalBlanks = count($blanks);
        return $totalBlanks > 0 ? ($correct / $totalBlanks) * $question->points : 0;
    }

    protected function scoreOrderingQuestion($question)
    {
        $settings = $question->settings ?? [];
        $items = $settings['ordering_items'] ?? [];
        
        // Fallback for legacy data stored in options
        if (empty($items) && $question->options->isNotEmpty()) {
            $items = [];
            foreach ($question->options->sortBy('order') as $option) {
                $items[] = [
                    'item_text' => $option->option_text,
                    'correct_order' => $option->order + 1
                ];
            }
        }
        
        if (empty($items)) {
            return 0;
        }
        
        $userAnswers = $this->answers[$question->id] ?? [];
        
        if (empty($userAnswers) || count($userAnswers) !== count($items)) {
            return 0;
        }
        
        // Check if order matches
        $correct = true;
        foreach ($items as $index => $item) {
            $expectedText = $item['item_text'];
            $actualText = $userAnswers[$index] ?? '';
            
            if ($actualText !== $expectedText) {
                $correct = false;
                break;
            }
        }
        
        return $correct ? $question->points : 0;
    }

    protected function scoreMatchingQuestion($question)
    {
        $settings = $question->settings ?? [];
        $pairs = $settings['matching_pairs'] ?? [];
        
        // Fallback for legacy data stored in options
        if (empty($pairs) && $question->options->isNotEmpty()) {
            $pairs = [];
            foreach ($question->options as $option) {
                $parts = explode('|', $option->option_text);
                if (count($parts) === 2) {
                    $pairs[] = [
                        'left_item' => $parts[0],
                        'right_item' => $parts[1]
                    ];
                }
            }
        }
        
        if (empty($pairs)) {
            return 0;
        }
        
        $userAnswers = $this->answers[$question->id] ?? [];
        
        $correct = 0;
        foreach ($pairs as $index => $pair) {
            $expectedMatch = $pair['right_item'];
            $actualMatch = $userAnswers[$index] ?? '';
            
            if ($actualMatch === $expectedMatch) {
                $correct++;
            }
        }
        
        $totalPairs = count($pairs);
        return $totalPairs > 0 ? ($correct / $totalPairs) * $question->points : 0;
    }

    protected function getQuestions()
    {
        // Return cached randomized questions if available (for consistency during attempt)
        if ($this->randomizedQuestions !== null) {
            return $this->randomizedQuestions;
        }
        
        $questions = $this->assessment->questions()->with('options')->orderBy('order')->get();
        
        // Randomize questions if enabled
        if ($this->assessment->is_randomized) {
            $questions = $questions->shuffle();
        }
        
        // Shuffle options if enabled
        if ($this->assessment->shuffle_options) {
            foreach ($questions as $question) {
                if ($question->relationLoaded('options')) {
                    $question->setRelation('options', $question->options->shuffle());
                }
            }
        }
        
        return $questions;
    }

    public function render()
    {
        // Always reload questions relationship to ensure it's available in the view
        // Livewire doesn't preserve relationships through serialization
        $questions = $this->getQuestions();
        
        // Explicitly set the relationship on the assessment model for the view
        $this->assessment->setRelation('questions', $questions);
        
        // Ensure course and lesson are also loaded
        if (!$this->assessment->relationLoaded('course')) {
            $this->assessment->load('course');
        }
        if (!$this->assessment->relationLoaded('lesson')) {
            $this->assessment->load('lesson');
        }
        
        $currentQuestion = $questions->get($this->currentQuestionIndex);
        $totalQuestions = $questions->count();
        $answeredCount = count(array_filter($this->answers));

        return view('livewire.assessments.take', [
            'questions' => $questions,
            'currentQuestion' => $currentQuestion,
            'totalQuestions' => $totalQuestions,
            'answeredCount' => $answeredCount,
            'progress' => $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100, 1) : 0,
        ]);
    }
}

