<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Question;
use App\Services\LessonCompletionService;
use App\Support\SubmissionFile;
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
    /** @var array<int, int> Stable question id order for this attempt */
    public array $questionOrder = [];
    /** @var array<int, array<int, int>> Stable option id order per question for this attempt */
    public array $optionOrder = [];

    public function mount(Assessment $assessment)
    {
        $this->assessment = $assessment->load(['course', 'lesson']);
        
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
        
        if (! $isInstructor) {
            $this->checkAccess();
        }
        
        $completedAttempts = AssessmentAttempt::where('user_id', Auth::id())
            ->where('assessment_id', $this->assessment->id)
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->get();

        $attemptCount = $completedAttempts->count();

        if ($this->assessment->max_attempts && $attemptCount >= $this->assessment->max_attempts) {
            $latest = $completedAttempts->first();
            if ($latest) {
                return redirect()->route('assessments.results', [
                    'assessment' => $this->assessment->id,
                    'attempt' => $latest->id,
                ]);
            }

            session()->flash('error', 'You have reached the maximum number of attempts for this assessment.');

            return redirect()->route('assessments.show', $this->assessment);
        }

        // Check existing in-progress attempt
        $existingAttempt = AssessmentAttempt::where('user_id', Auth::id())
            ->where('assessment_id', $this->assessment->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if ($existingAttempt && ! $existingAttempt->completed_at) {
            $this->attempt = $existingAttempt;
            $this->attemptId = $existingAttempt->id;
            $this->answers = $existingAttempt->answers ?? [];
            $this->startedAt = $existingAttempt->started_at;
            $this->shuffleSeed = $existingAttempt->id;

            if ($this->assessment->assessment_type === 'assignment' && isset($this->answers['submission_text'])) {
                $this->submissionText = (string) $this->answers['submission_text'];
            }
        } else {
            $this->questionOrder = [];
            $this->optionOrder = [];
            $this->startNewAttempt($attemptCount + 1);
            $this->attemptId = $this->attempt->id;
            $this->shuffleSeed = $this->attempt->id;
        }

        $this->randomizedQuestions = null;

        if ($this->assessment->time_limit_minutes) {
            $totalSeconds = $this->assessment->time_limit_minutes * 60;
            $elapsed = $this->startedAt
                ? (int) abs($this->startedAt->diffInSeconds(now()))
                : 0;
            $this->timeRemaining = max(0, $totalSeconds - $elapsed);
        }
    }

    public function hydrate()
    {
        // Reload assessment with relationships after Livewire hydration
        // This is needed because Livewire doesn't preserve relationships when serializing
        if ($this->assessment && $this->assessment->id) {
            $this->assessment = Assessment::with(['course', 'lesson'])
                ->findOrFail($this->assessment->id);
        }

        // Livewire cannot reliably serialize Eloquent collections — always rebuild questions
        $this->randomizedQuestions = null;

        // Reload attempt if we have an attemptId
        if ($this->attemptId && !$this->attempt) {
            $this->attempt = AssessmentAttempt::find($this->attemptId);
        }

        if ($this->attempt && $this->attempt->status === 'completed') {
            $this->showResults = true;
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

        $accessCheck = app(LessonCompletionService::class)->canAccessAssessment($this->assessment, $user);

        if (! $accessCheck['can_access']) {
            $reason = collect($accessCheck['missing'])->first()['type'] ?? 'locked';

            Log::warning('Assessment access denied: progression or lock', [
                'user_id' => $userId,
                'assessment_id' => $this->assessment->id,
                'reason' => $reason,
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
        $type = $this->questionType($question);

        if ($type === 'file_upload') {
            if ($this->uploadsForQuestion($question->id) !== []) {
                return true;
            }

            $answer = $this->answers[$question->id] ?? null;

            return is_array($answer) && ! empty($answer['files']);
        }

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

    public function removeFile($questionId, $fileIndex = null)
    {
        if (isset($this->tempFiles[$questionId])) {
            if (is_array($this->tempFiles[$questionId])) {
                if ($fileIndex === null) {
                    unset($this->tempFiles[$questionId]);
                } else {
                    unset($this->tempFiles[$questionId][$fileIndex]);
                    $this->tempFiles[$questionId] = array_values($this->tempFiles[$questionId]);
                }
            } else {
                unset($this->tempFiles[$questionId]);
            }
        }

        if (! isset($this->answers[$questionId]) || ! is_array($this->answers[$questionId])) {
            return;
        }

        $files = $this->answers[$questionId]['files'] ?? [];
        if ($files === []) {
            unset($this->answers[$questionId]);

            return;
        }

        if ($fileIndex === null) {
            unset($this->answers[$questionId]);

            return;
        }

        unset($files[$fileIndex]);
        $files = array_values($files);

        if ($files === []) {
            unset($this->answers[$questionId]);
        } else {
            $this->answers[$questionId]['files'] = $files;
        }
    }

    public function updatedTempFiles($value, string $key): void
    {
        $questionId = (int) $key;
        $question = $this->getQuestions()->firstWhere('id', $questionId);

        if (! $question) {
            unset($this->tempFiles[$key]);

            return;
        }

        $settings = $question->settings ?? [];
        $maxSizeKb = ((int) ($settings['max_size'] ?? 10)) * 1024;

        $rules = (int) ($settings['max_files'] ?? 1) > 1
            ? ["tempFiles.{$key}.*" => "nullable|file|max:{$maxSizeKb}"]
            : ["tempFiles.{$key}" => "nullable|file|max:{$maxSizeKb}"];

        $this->validate($rules);

        if ($this->persistUploadedFilesForQuestion($questionId)) {
            $this->saveProgress();
        }
    }

    protected function persistUploadedFilesForQuestion(int $questionId): bool
    {
        $uploaded = $this->uploadsForQuestion($questionId);
        if ($uploaded === []) {
            return false;
        }

        $storedFiles = [];
        foreach ($uploaded as $file) {
            if ($file) {
                $storedFiles[] = SubmissionFile::store($file, 'assessments/submissions');
            }
        }

        if ($storedFiles === []) {
            return false;
        }

        $this->answers[$questionId] = ['files' => $storedFiles];
        unset($this->tempFiles[$questionId]);

        return true;
    }

    protected function persistAllFileUploadAnswers($questions): void
    {
        foreach ($questions as $question) {
            if ($this->questionType($question) !== 'file_upload') {
                continue;
            }

            $existing = $this->answers[$question->id]['files'] ?? [];
            if (! empty($existing)) {
                continue;
            }

            $this->persistUploadedFilesForQuestion($question->id);
        }
    }

    public function questionType(Question $question): string
    {
        return str_replace(' ', '_', strtolower(trim((string) $question->question_type)));
    }

    /**
     * @return array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile>
     */
    public function uploadsForQuestion(int $questionId): array
    {
        $uploaded = $this->tempFiles[$questionId] ?? null;

        if ($uploaded === null || $uploaded === '') {
            return [];
        }

        if (is_array($uploaded)) {
            return array_values(array_filter($uploaded));
        }

        return [$uploaded];
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
            if (!$this->attemptId) {
                $this->addError('submit', 'No active assessment attempt. Please start the assessment again.');

                return;
            }

            $attempt = $this->getAttempt();

            if (!$attempt) {
                $this->addError('submit', 'No active attempt found. Please refresh the page.');

                return;
            }

            if ($attempt->status !== 'in_progress') {
                $this->addError('submit', 'This assessment has already been submitted.');
                $this->showResults = true;

                return;
            }

        // Use fresh questions for scoring (not shuffled) to ensure accurate scoring
        $questions = $this->assessment->questions()->with('options')->orderBy('order')->get();

        $this->persistAllFileUploadAnswers($questions);

        if (! $this->validateAssignmentBeforeSubmit($questions)) {
            return;
        }

        if ($this->assessment->assessment_type === 'assignment' && $questions->isEmpty()) {
            $this->validate([
                'submissionText' => 'nullable|string',
                'submissionFiles.*' => 'nullable|file|max:10240',
            ]);

            $filePaths = [];
            foreach ($this->submissionFiles as $file) {
                $filePaths[] = SubmissionFile::store($file, 'assessments/submissions');
            }

            $this->answers = [
                'submission_text' => $this->submissionText,
                'files' => $filePaths,
            ];

            $attempt->update([
                'answers' => $this->answers,
                'completed_at' => now(),
                'status' => 'completed',
                'score' => null,
                'auto_scored' => false,
                'is_locked' => false,
                'time_spent' => $attempt->started_at ? (int) abs($attempt->started_at->diffInMinutes(now())) : 0,
            ]);

            $this->showResults = true;
            $this->dispatch('assessment-completed');
            session()->flash('message', 'Assignment submitted successfully! Waiting for instructor review.');

            return $this->redirect(
                route('assessments.results', ['assessment' => $this->assessment->id, 'attempt' => $attempt->id]),
                navigate: false
            );
        }

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

                if (isset($settings['min_chars']) && $settings['min_chars'] !== null && (int) $settings['min_chars'] > 0 && $charCount < (int) $settings['min_chars']) {
                    session()->flash('error', "Your answer to question '{$question->question_text}' must be at least {$settings['min_chars']} characters.");
                    return;
                }

                $minWords = isset($settings['min_words']) ? (int) $settings['min_words'] : 0;
                $maxWords = isset($settings['max_words']) && $settings['max_words'] !== '' && $settings['max_words'] !== null
                    ? (int) $settings['max_words']
                    : 0;

                // Assignment prompts (e.g. "Live, Upload, or both?") can be one word.
                if ($this->assessment->assessment_type === 'assignment' && $question->question_type === 'short_answer') {
                    $minWords = 0;
                }

                if ($maxWords > 0 && $wordCount > $maxWords) {
                    session()->flash('error', "Your answer to question '{$question->question_text}' exceeds the maximum words ({$maxWords}). Please shorten it before submitting.");
                    return;
                }

                if ($minWords > 0 && $wordCount < $minWords) {
                    session()->flash('error', "Your answer to question '{$question->question_text}' must be at least {$minWords} words.");
                    return;
                }
            }
        }
        // Persist any remaining file_upload attachments before scoring
        $this->persistAllFileUploadAnswers($questions);

        // Detect questions that always need manual grading
        $manualOnlyTypes = ['essay', 'code_submission', 'file_upload', 'rubric_criteria'];
        $needsManualGrading = $this->assessment->assessment_type === 'assignment';
        foreach ($questions as $question) {
            if (in_array($question->question_type, $manualOnlyTypes)) {
                $needsManualGrading = true;
                break;
            }
            if ($question->question_type === 'short_answer') {
                $s = $question->settings ?? [];
                $hasCorrect = !empty($s['correct_answer'] ?? $s['short_answer']['correct_answer'] ?? '');
                if (!$hasCorrect) {
                    $hasCorrect = $question->options->where('is_correct', true)->isNotEmpty();
                }
                if (!$hasCorrect) {
                    $needsManualGrading = true;
                    break;
                }
            }
        }

        $totalPoints = $questions->sum('points');
        $earnedPoints = 0;

        foreach ($questions as $question) {
            $earnedPoints += $this->calculateQuestionScore($question);
        }

        $this->score = $earnedPoints;
        $this->percentage = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
        $this->isPassed = !$needsManualGrading && $this->percentage >= $this->assessment->passing_score;
        $this->passed = $this->isPassed;

        $timeSpent = $attempt->started_at ? (int) abs($attempt->started_at->diffInMinutes(now())) : 0;

        $attempt->update([
            'answers' => $this->answers,
            'score' => $needsManualGrading ? null : $this->score,
            'is_passed' => $needsManualGrading ? false : $this->isPassed,
            'completed_at' => now(),
            'status' => 'completed',
            'time_spent' => $timeSpent,
            'auto_scored' => !$needsManualGrading,
            'is_locked' => !$needsManualGrading,
        ]);

        // Award XP only when fully auto-graded and passed
        if (!$needsManualGrading && $this->isPassed && $this->assessment->xp_reward) {
            $courseId = (int) ($this->assessment->course_id ?? 0);
            $lessonId = $this->assessment->lesson_id ? (int) $this->assessment->lesson_id : null;

            if ($courseId > 0) {
                app(\App\Services\PointsService::class)->awardTrackedCourseXp(
                    (int) Auth::id(),
                    $courseId,
                    (int) $this->assessment->xp_reward,
                    'quiz_completed',
                    $lessonId,
                    ['source' => 'assessment_auto']
                );
            } else {
                $user = Auth::user();
                $points = $user->points()->firstOrCreate([
                    'user_id' => $user->id,
                ], [
                    'total_points' => 0,
                    'level' => 1,
                ]);

                $points->addPoints((int) $this->assessment->xp_reward);
            }
        }

        // Check and award badges for perfect scores
        if (!$needsManualGrading && $this->percentage >= 100) {
            $badgeService = app(\App\Services\BadgeAwardingService::class);
            $badgeService->checkPerfectQuizBadges(Auth::user());
        }

        $this->showResults = true;
        $this->dispatch('assessment-completed');

        if ($needsManualGrading) {
            session()->flash('message', $this->assessment->assessment_type === 'assignment'
                ? 'Assignment submitted successfully! Waiting for instructor review.'
                : 'Submitted! Some questions need teacher review before your final score is set.');
        } else {
            session()->flash('message', $this->isPassed
                ? 'Congratulations! You passed the assessment.'
                : 'You did not pass. Keep studying!');
        }

        return $this->redirect(
            route('assessments.results', ['assessment' => $this->assessment->id, 'attempt' => $attempt->id]),
            navigate: false
        );

        } catch (\Throwable $e) {
            Log::error('Assessment submission failed', [
                'assessment_id' => $this->assessment->id ?? null,
                'attempt_id' => $this->attemptId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->addError('submit', 'An error occurred while submitting. Please try again.');
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
            
            if (is_array($userAnswer)) {
                $userAnswerArray = array_map(function($val) {
                    return is_numeric($val) ? (int)$val : $val;
                }, array_filter($userAnswer));
                sort($userAnswerArray);
                return $userAnswerArray === $correctOptions ? $question->points : 0;
            } else {
                $userAnswerInt = is_numeric($userAnswer) ? (int)$userAnswer : $userAnswer;
                return in_array($userAnswerInt, $correctOptions, true) ? $question->points : 0;
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

        // short_answer: auto-grade if a correct answer is defined; otherwise 0 (teacher grades)
        if ($question->question_type === 'short_answer') {
            return $this->scoreShortAnswerQuestion($question);
        }

        // essay, code_submission, file_upload — always 0 until teacher grades
        return 0;
    }

    protected function scoreShortAnswerQuestion($question): float
    {
        $userAnswer = trim($this->answers[$question->id] ?? '');
        if ($userAnswer === '') return 0;

        $settings = $question->settings ?? [];
        $correctAnswer = trim($settings['correct_answer'] ?? $settings['short_answer']['correct_answer'] ?? '');

        if (empty($correctAnswer)) {
            $correctOption = $question->options->where('is_correct', true)->first();
            $correctAnswer = $correctOption ? trim($correctOption->option_text) : '';
        }

        if (empty($correctAnswer)) return 0;

        $caseSensitive = $settings['case_sensitive'] ?? false;
        $userCmp    = $caseSensitive ? $userAnswer    : mb_strtolower($userAnswer);
        $correctCmp = $caseSensitive ? $correctAnswer : mb_strtolower($correctAnswer);

        if ($userCmp === $correctCmp) return $question->points;

        foreach ($settings['alternative_answers'] ?? [] as $alt) {
            $altCmp = $caseSensitive ? trim($alt) : mb_strtolower(trim($alt));
            if ($userCmp === $altCmp) return $question->points;
        }

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

    protected function questionCount(): int
    {
        return $this->assessment->questions()->count();
    }

    protected function validateAssignmentBeforeSubmit($questions): bool
    {
        if ($this->assessment->assessment_type !== 'assignment') {
            return true;
        }

        if ($questions->isEmpty()) {
            $assignmentData = $this->assessment->assignment_data ?? [];
            $allowText = (bool) ($assignmentData['allow_text'] ?? true);
            $allowFiles = (bool) ($assignmentData['allow_files'] ?? true);
            $hasText = filled(trim($this->submissionText));
            $hasFiles = ! empty($this->submissionFiles);

            if ($allowText && ! $hasText && (! $allowFiles || ! $hasFiles)) {
                $this->addError('submit', 'Please write a response before submitting.');

                return false;
            }

            if ($allowFiles && ! $hasFiles && (! $allowText || ! $hasText)) {
                $this->addError('submit', 'Please upload at least one file before submitting.');

                return false;
            }

            if (! $allowText && ! $allowFiles) {
                $this->addError('submit', 'This assignment is not configured for submissions.');

                return false;
            }

            return true;
        }

        $missing = [];

        foreach ($questions as $index => $question) {
            if ($this->isQuestionAnswered($question)) {
                continue;
            }

            $label = 'Task '.($index + 1);
            $missing[] = match ($this->questionType($question)) {
                'file_upload' => "{$label}: upload a file",
                'code_submission' => "{$label}: submit your code",
                'essay' => "{$label}: write your response",
                'short_answer' => "{$label}: answer the question",
                default => "{$label}: complete this question",
            };
        }

        if ($missing !== []) {
            $this->addError(
                'submit',
                'Please complete all required tasks before submitting: '.implode('; ', array_slice($missing, 0, 3))
                .(count($missing) > 3 ? '…' : '')
            );

            return false;
        }

        return true;
    }

    protected function getQuestions()
    {
        if ($this->randomizedQuestions instanceof \Illuminate\Support\Collection
            && $this->randomizedQuestions->isNotEmpty()
            && $this->randomizedQuestions->first() instanceof Question) {
            return $this->randomizedQuestions->values();
        }

        $questions = $this->assessment->questions()->with('options')->orderBy('order')->get();

        // Build a stable question order once per attempt (survives Livewire rehydration)
        if ($this->questionOrder === []) {
            $ordered = $questions;
            if ($this->assessment->is_randomized) {
                $ordered = $questions->shuffle($this->shuffleSeed ? (int) $this->shuffleSeed : null);
            }
            $this->questionOrder = $ordered->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        }

        $byId = $questions->keyBy('id');
        $orderedQuestions = collect($this->questionOrder)
            ->map(fn ($id) => $byId->get($id))
            ->filter()
            ->values();

        // Include any new questions not yet in the stored order
        foreach ($questions as $question) {
            if (! in_array((int) $question->id, $this->questionOrder, true)) {
                $orderedQuestions->push($question);
                $this->questionOrder[] = (int) $question->id;
            }
        }

        foreach ($orderedQuestions as $question) {
            $options = $question->relationLoaded('options')
                ? $question->options
                : $question->options()->orderBy('order')->get();

            if ($this->assessment->shuffle_options) {
                if (! isset($this->optionOrder[$question->id])) {
                    $shuffled = $options->shuffle(
                        $this->shuffleSeed
                            ? (int) $this->shuffleSeed + (int) $question->id
                            : null
                    );
                    $this->optionOrder[$question->id] = $shuffled->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
                }

                $optionById = $options->keyBy('id');
                $orderedOptions = collect($this->optionOrder[$question->id] ?? [])
                    ->map(fn ($id) => $optionById->get($id))
                    ->filter()
                    ->values();

                foreach ($options as $option) {
                    if (! in_array((int) $option->id, $this->optionOrder[$question->id] ?? [], true)) {
                        $orderedOptions->push($option);
                        $this->optionOrder[$question->id][] = (int) $option->id;
                    }
                }

                $question->setRelation('options', $orderedOptions);
            } else {
                $question->setRelation('options', $options->sortBy('order')->values());
            }
        }

        $this->randomizedQuestions = $orderedQuestions;

        return $this->randomizedQuestions;
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

    /**
     * @return array<int, array{path: string, name: string}|string>
     */
    public function savedUploadFilesForQuestion(int $questionId): array
    {
        $answer = $this->answers[$questionId] ?? null;

        if (! is_array($answer) || empty($answer['files'])) {
            return [];
        }

        return array_values(array_filter($answer['files'], function ($file) {
            if (is_string($file)) {
                return $file !== '';
            }

            return is_array($file) && is_string($file['path'] ?? null) && $file['path'] !== '';
        }));
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
        
        $indexedQuestions = $questions->values();
        $currentQuestion = $indexedQuestions->get($this->currentQuestionIndex);
        $totalQuestions = $indexedQuestions->count();
        $answeredCount = $indexedQuestions->filter(fn ($q) => $this->isQuestionAnswered($q))->count();
        $selectedUploadFiles = $currentQuestion
            ? $this->uploadsForQuestion($currentQuestion->id)
            : [];
        $savedUploadFiles = $currentQuestion
            ? $this->savedUploadFilesForQuestion($currentQuestion->id)
            : [];

        return view('livewire.assessments.take', [
            'questions' => $indexedQuestions,
            'currentQuestion' => $currentQuestion,
            'currentQuestionType' => $currentQuestion ? $this->questionType($currentQuestion) : null,
            'selectedUploadFiles' => $selectedUploadFiles,
            'savedUploadFiles' => $savedUploadFiles,
            'totalQuestions' => $totalQuestions,
            'answeredCount' => $answeredCount,
            'progress' => $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100, 1) : 0,
        ]);
    }
}

