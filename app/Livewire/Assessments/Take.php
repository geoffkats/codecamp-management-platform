<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
    public $attemptId = null;
    public $score = 0;
    public $passed = false;
    public $percentage = 0;
    public $isPassed = false;
    public $bookmarkedQuestions = [];
    public $flaggedQuestions = [];
    public $autoSaveEnabled = true;
    public $lastSavedAt = null;
    public $randomizedQuestions = null; // Store randomized questions for consistency
    public $shuffledQuestionData = []; // Store shuffled question-specific data
    public $shuffleSeed = null; // Seed for consistent shuffling

    public function mount(Assessment $assessment)
    {
        $this->assessment = $assessment->load(['questions.options', 'course', 'lesson']);
        
        // Check if assessment is locked for students
        $user = Auth::user();

        if ($user->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();
            $hasAccess = $schoolId
                && $this->assessment->course
                && $this->assessment->course->schools
                    ->where('id', (int) $schoolId)
                    ->where('pivot.is_active', true)
                    ->isNotEmpty();

            if ($hasAccess) {
                return redirect()->route('assessments.show', $this->assessment);
            }

            abort(403, 'Unauthorized assessment access.');
        }
        $isInstructor = $this->assessment->course->instructor_id === $user->id || 
                       $user->hasRole('admin') || 
                       $user->hasRole('supervisor');
        
        if (!$isInstructor && $this->assessment->is_locked) {
            // Assessment is locked - will show locked view
            return;
        }
        
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
            $this->attemptId = $existingAttempt->id;
            $this->answers = $existingAttempt->answers ?? [];
            $this->startedAt = $existingAttempt->started_at;
            
            // Use consistent seed based on attempt ID for reproducible shuffling
            $this->shuffleSeed = $existingAttempt->id;
            
            // Restore submission data for assignments
            if ($this->assessment->assessment_type === 'assignment' && isset($this->answers['submission_text'])) {
                $this->submissionText = $this->answers['submission_text'];
            }
            
            // Restore randomized questions for consistency
            $this->randomizedQuestions = $this->getQuestions();
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
            $this->attemptId = $this->attempt->id;
            
            // Set seed for consistent shuffling based on attempt ID
            $this->shuffleSeed = $this->attempt->id;
            
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
        
        // Reload attempt if we have an attemptId
        if ($this->attemptId && !$this->attempt) {
            $this->attempt = AssessmentAttempt::find($this->attemptId);
        }
    }

    protected function checkAccess()
    {
        $user = Auth::user();
        $userId = Auth::id();

        // Check if student is enrolled
        $isEnrolled = $this->assessment->course->enrollments()
            ->where('user_id', $userId)
            ->exists();

        if (!$isEnrolled && !$user->hasRole('admin')) {
            Log::warning('Assessment access denied: User not enrolled', [
                'user_id' => $userId,
                'assessment_id' => $this->assessment->id,
                'course_id' => $this->assessment->course_id,
                'reason' => 'not_enrolled'
            ]);
            abort(403, 'You must be enrolled in the course "' . $this->assessment->course->title . '" to take this assessment.');
        }

        // Only enforce lock; do NOT enforce approval status for student access
        if ($this->assessment->is_locked && !$user->hasAnyRole(['admin', 'teacher'])) {
            Log::warning('Assessment access denied: Assessment locked', [
                'user_id' => $userId,
                'assessment_id' => $this->assessment->id,
                'reason' => 'locked'
            ]);
            abort(403, 'This assessment is currently locked and unavailable.');
        }
    }

    protected function startNewAttempt($attemptNumber)
    {
        $studentType = $this->getStudentTypeForAttempt();
        $schoolId = $this->getSchoolIdForAttempt();
        $teacherId = $this->getTeacherIdForAttempt();
        $autoScored = $this->assessment->assessment_type !== 'assignment';

        $this->attempt = AssessmentAttempt::create([
            'user_id' => Auth::id(),
            'assessment_id' => $this->assessment->id,
            'school_id' => $schoolId,
            'teacher_id' => $teacherId,
            'student_type' => $studentType,
            'auto_scored' => $autoScored,
            'is_locked' => false,
            'started_at' => now(),
            'status' => 'in_progress',
            'answers' => [],
        ]);

        $this->startedAt = $this->attempt->started_at;
    }

    protected function getStudentTypeForAttempt(): string
    {
        $user = Auth::user();

        return $user?->student_type ?? 'codecamp';
    }

    protected function getSchoolIdForAttempt(): ?int
    {
        $user = Auth::user();
        $schoolId = $user?->studentProfile?->school_id;

        return $schoolId ? (int) $schoolId : null;
    }

    protected function getTeacherIdForAttempt(): ?int
    {
        $course = $this->assessment->course ?? $this->assessment->lesson?->course;

        return $course?->instructor_id ? (int) $course->instructor_id : null;
    }

    public function updated($property)
    {
        // Auto-save whenever answers change
        if (str_starts_with($property, 'answers.')) {
            $this->saveProgress();
        }
    }

    public function updateAnswer($questionId, $value)
    {
        $this->answers[$questionId] = $value;
        
        // Auto-save to attempt
        $attempt = $this->getAttempt();
        if ($attempt) {
            $attempt->update(['answers' => $this->answers]);
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
        
        $attempt = $this->getAttempt();
        if ($attempt) {
            $attempt->update(['answers' => $this->answers]);
            $this->lastSavedAt = now();
            $this->dispatch('progress-saved');
        }
    }

    public function nextQuestion()
    {
        $questions = $this->getQuestions();
        
        // Allow skipping questions - no validation required
        if ($this->currentQuestionIndex < $questions->count() - 1) {
            $this->currentQuestionIndex++;
        }
        
        // Auto-save progress when navigating
        $this->saveProgress();
    }

    public function isQuestionAnswered($question)
    {
        $userAnswer = $this->answers[$question->id] ?? null;
        
        // Check if answer exists and is not empty
        if ($userAnswer === null || $userAnswer === '') {
            return false;
        }
        
        // For array answers (multiple select, matching, ordering, fill_blank)
        if (is_array($userAnswer)) {
            // Filter out empty values
            $filtered = array_filter($userAnswer, function($value) {
                return $value !== null && $value !== '' && $value !== [];
            });
            return !empty($filtered);
        }
        
        return true;
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
        $attempt = $this->getAttempt();
        if ($attempt) {
            $attempt->update(['answers' => $this->answers]);
            $this->lastSavedAt = now();
            $this->dispatch('progress-saved');
        }
    }
    
    protected function getAttempt()
    {
        if (!$this->attempt && $this->attemptId) {
            $this->attempt = AssessmentAttempt::find($this->attemptId);
        }
        return $this->attempt;
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
        try {
            \Log::info('Submit assessment started', ['assessment_id' => $this->assessment->id, 'attemptId' => $this->attemptId]);
            
            // Get the attempt reliably
            $attempt = $this->getAttempt();
            
            if (!$attempt) {
                \Log::error('No attempt found', ['attemptId' => $this->attemptId]);
                session()->flash('error', 'No active attempt found. Please refresh the page.');
                return;
            }
            
            // Check if attempt is still in progress
            if ($attempt->status !== 'in_progress') {
                \Log::error('Attempt already completed', ['status' => $attempt->status]);
                session()->flash('error', 'This assessment has already been submitted.');
                $this->showResults = true;
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
            $attempt->update([
                'answers' => $this->answers,
                'completed_at' => now(),
                'status' => 'completed',
                'score' => null,
                'auto_scored' => false,
                'is_locked' => false,
                'time_spent' => $this->startedAt ? now()->diffInMinutes($this->startedAt) : 0,
            ]);

            $this->showResults = true;
            $this->dispatch('assessment-completed');
            session()->flash('message', 'Assignment submitted successfully! Waiting for instructor review.');

            return $this->redirect(
                route('assessments.results', ['assessment' => $this->assessment->id, 'attempt' => $attempt->id]),
                navigate: true
            );
        }

        // Handle quiz-type assessments
        // Use fresh questions for scoring to avoid shuffling issues
        $questions = $this->assessment->questions()->with('options')->get();

        // Log all answers before scoring
        \Log::info('All answers at submission:', ['answers' => $this->answers]);

        // Validate short_answer and essay length constraints before scoring
        foreach ($questions as $question) {
            if (in_array($question->question_type, ['short_answer', 'essay'])) {
                $answer = trim($this->answers[$question->id] ?? '');
                $settings = $question->settings ?? [];

                $charCount = mb_strlen($answer);
                $wordCount = $answer === '' ? 0 : count(preg_split('/\s+/', $answer, -1, PREG_SPLIT_NO_EMPTY));

                if (isset($settings['max_chars']) && $settings['max_chars'] !== null && $charCount > $settings['max_chars']) {
                    session()->flash('error', "Your answer to question '{$question->question_text}' exceeds the maximum characters ({$settings['max_chars']}). Please shorten it before submitting.");
                    return;
                }

                if (isset($settings['max_words']) && $settings['max_words'] !== null && $wordCount > $settings['max_words']) {
                    session()->flash('error', "Your answer to question '{$question->question_text}' exceeds the maximum words ({$settings['max_words']}). Please shorten it before submitting.");
                    return;
                }

                if (isset($settings['min_chars']) && $settings['min_chars'] !== null && $charCount < $settings['min_chars']) {
                    session()->flash('error', "Your answer to question '{$question->question_text}' must be at least {$settings['min_chars']} characters.");
                    return;
                }

                if (isset($settings['min_words']) && $settings['min_words'] !== null && $wordCount < $settings['min_words']) {
                    session()->flash('error', "Your answer to question '{$question->question_text}' must be at least {$settings['min_words']} words.");
                    return;
                }
            }
        }
        $totalPoints = $questions->sum('points');
        $earnedPoints = 0;

        foreach ($questions as $question) {
            $earnedPoints += $this->calculateQuestionScore($question);
        }

        \Log::info('Final scoring summary', [
            'total_questions' => $questions->count(),
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            'percentage' => $this->percentage,
            'passed' => $this->isPassed
        ]);

        $this->score = $earnedPoints;
        $this->percentage = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
        $this->isPassed = $this->percentage >= $this->assessment->passing_score;
        $this->passed = $this->isPassed;

        $timeSpent = $this->startedAt ? now()->diffInMinutes($this->startedAt) : 0;

        $attempt->update([
            'answers' => $this->answers,
            'score' => $this->score,
            'is_passed' => $this->isPassed,
            'completed_at' => now(),
            'status' => 'completed',
            'time_spent' => $timeSpent,
            'auto_scored' => true,
            'is_locked' => true,
        ]);

        // Award XP if passed
        if ($this->isPassed && $this->assessment->xp_reward) {
            $user = Auth::user();
            $points = $user->points()->firstOrCreate([
                'user_id' => $user->id,
            ], [
                'total_points' => 0,
                'level' => 1,
            ]);

            $points->increment('total_points', $this->assessment->xp_reward);
        }

        // Check and award badges for perfect scores
        if ($this->percentage >= 100) {
            $badgeService = app(\App\Services\BadgeAwardingService::class);
            $badgeService->checkPerfectQuizBadges(Auth::user());
        }

        \Log::info('Assessment completed successfully', [
            'score' => $this->score,
            'percentage' => $this->percentage,
            'passed' => $this->isPassed
        ]);

        $this->showResults = true;
        $this->dispatch('assessment-completed');

        session()->flash('message', $this->isPassed 
            ? 'Congratulations! You passed the assessment.' 
            : 'You did not pass. Keep studying!');

        return $this->redirect(
            route('assessments.results', ['assessment' => $this->assessment->id, 'attempt' => $attempt->id]),
            navigate: true
        );
            
        } catch (\Exception $e) {
            \Log::error('Assessment submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'An error occurred while submitting: ' . $e->getMessage());
        }
    }

    protected function calculateQuestionScore($question)
    {
        $userAnswer = $this->answers[$question->id] ?? null;
        
        // Check if answer exists and is not empty/null
        if ($userAnswer === null || $userAnswer === '' || (is_array($userAnswer) && empty(array_filter($userAnswer)))) {
            return 0;
        }

        // For multiple choice/select questions
        if (in_array($question->question_type, ['multiple_choice', 'multiple_select', 'choice', 'true_false'])) {
            $correctOptions = $question->options->where('is_correct', true)->pluck('id')->toArray();
            
            // Ensure all correct options are integers
            $correctOptions = array_map(function($opt) {
                return is_numeric($opt) ? (int)$opt : $opt;
            }, $correctOptions);
            sort($correctOptions);
            
            // Debug logging
            \Log::info("Scoring Question {$question->id}:", [
                'question_type' => $question->question_type,
                'user_answer' => $userAnswer,
                'user_answer_type' => gettype($userAnswer),
                'correct_options' => $correctOptions,
                'question_points' => $question->points
            ]);
            
            if (is_array($userAnswer)) {
                // Multiple select - compare arrays
                $userAnswerArray = array_map(function($val) {
                    return is_numeric($val) ? (int)$val : $val;
                }, array_filter($userAnswer));
                sort($userAnswerArray);
                
                $score = $userAnswerArray === $correctOptions ? $question->points : 0;
                \Log::info("Array comparison result:", [
                    'question_id' => $question->id,
                    'score' => $score,
                    'user' => $userAnswerArray,
                    'correct' => $correctOptions,
                    'user_count' => count($userAnswerArray),
                    'correct_count' => count($correctOptions),
                    'arrays_match' => $userAnswerArray === $correctOptions
                ]);
                return $score;
            } else {
                // Single choice - convert to int and check
                $userAnswerInt = is_numeric($userAnswer) ? (int)$userAnswer : $userAnswer;
                $score = in_array($userAnswerInt, $correctOptions, true) ? $question->points : 0;
                \Log::info("Single comparison result:", [
                    'question_id' => $question->id,
                    'score' => $score,
                    'user' => $userAnswerInt,
                    'correct' => $correctOptions,
                    'found' => in_array($userAnswerInt, $correctOptions, true)
                ]);
                return $score;
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
        
        // Use seed for consistent shuffling if available
        if ($this->shuffleSeed) {
            mt_srand($this->shuffleSeed);
        }
        
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
        
        // Reset random seed
        if ($this->shuffleSeed) {
            mt_srand();
        }
        
        return $questions;
    }

    public function getShuffledQuestionData($questionId, $questionType)
    {
        // Return cached data if available
        if (isset($this->shuffledQuestionData[$questionId])) {
            return $this->shuffledQuestionData[$questionId];
        }

        $question = $this->getQuestions()->firstWhere('id', $questionId);
        if (!$question) {
            return null;
        }

        $data = null;

        if ($questionType === 'matching') {
            $settings = $question->settings ?? [];
            $pairs = $settings['matching_pairs'] ?? [];
            
            // Fallback for legacy data stored in options
            if (empty($pairs) && $question->options->isNotEmpty()) {
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
            
            $rightItems = collect($pairs)->pluck('right_item');
            
            // Shuffle only if assessment has shuffle_options enabled
            if ($this->assessment->shuffle_options) {
                if ($this->shuffleSeed) {
                    mt_srand($this->shuffleSeed + $questionId); // Use question-specific seed
                }
                $rightItems = $rightItems->shuffle();
                if ($this->shuffleSeed) {
                    mt_srand();
                }
            }
            
            $data = [
                'pairs' => $pairs,
                'rightItems' => $rightItems->toArray()
            ];
        } elseif ($questionType === 'ordering') {
            $settings = $question->settings ?? [];
            $items = $settings['ordering_items'] ?? [];
            
            // Fallback for legacy data stored in options
            if (empty($items) && $question->options->isNotEmpty()) {
                foreach ($question->options as $option) {
                    $items[] = [
                        'item_text' => $option->option_text,
                        'correct_order' => $option->order + 1
                    ];
                }
            }
            
            $shuffledItems = collect($items);
            
            // Shuffle only if assessment has shuffle_options enabled
            if ($this->assessment->shuffle_options) {
                if ($this->shuffleSeed) {
                    mt_srand($this->shuffleSeed + $questionId); // Use question-specific seed
                }
                $shuffledItems = $shuffledItems->shuffle();
                if ($this->shuffleSeed) {
                    mt_srand();
                }
            }
            
            $data = [
                'items' => $items,
                'shuffledItems' => $shuffledItems->toArray()
            ];
        }

        // Cache the data
        $this->shuffledQuestionData[$questionId] = $data;
        
        return $data;
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

