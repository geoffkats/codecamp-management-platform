<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Course;
use App\Models\Question;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    use WithPagination, WithFileUploads;

    public Assessment $assessment;
    public bool $embedded = false;
    public $title;
    public $description;
    public $assessment_type;
    public $max_attempts = 1;
    public $time_limit_minutes;
    public $passing_score = 70;
    public $xp_reward = 50;
    public $is_required = false;
    public $show_results_immediately = true;
    public $is_randomized = false;
    public $shuffle_options = false;
    public $questions_per_attempt = null;
    public $show_correct_answers = true;
    public $allow_review = true;
    
    // Question editor (hosted QuestionEditor component)
    public $showQuestionModal = false;
    public $editingQuestionId = null;
    public int $questionEditorKey = 0;

    // Add from Question Bank
    public bool $showBankPicker = false;
    public string $bankSearch = '';
    public string $bankType = '';
    public string $bankDifficulty = '';
    public string $bankTag = '';
    public string $bankCourse = 'this';
    public bool $bankUnusedOnly = false;
    public int $bankLimit = 50;
    /** @var array<int, int|string> */
    public array $bankSelected = [];

    // Student submissions view
    public $showSubmissions = false;

    /** questions | settings | submissions */
    public string $tab = 'questions';
    public $selectedAttempt = null;

    // Assignment-specific fields
    public string $assignment_instructions = '';
    public ?string $assignment_due_date = null;
    public int $assignment_max_points = 100;
    public bool $assignment_allow_text = true;
    public bool $assignment_allow_files = true;
    /** @var array<int, array{path: string, name: string}> */
    public array $assignment_existing_attachments = [];
    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $assignmentBriefFiles = [];

    protected $rules = [
        'title' => 'required|string|max:255',
        'assessment_type' => 'required|string',
        'max_attempts' => 'nullable|integer|min:1',
        'time_limit_minutes' => 'nullable|integer|min:1',
        'passing_score' => 'nullable|integer|min:0|max:100',
        'xp_reward' => 'nullable|integer|min:0',
        'questions_per_attempt' => 'nullable|integer|min:1|max:500',
    ];

    public function mount(Assessment $assessment, $attempt = null)
    {
        // Check authorization
        if (!$assessment->course) {
            abort(404);
        }
        
        $user = Auth::user();
        $hasGlobalEdit = $user->hasPermission('edit_courses') || $user->isAdmin();

        if ($user->isTeacher() && !$hasGlobalEdit && $assessment->course->instructor_id !== $user->id) {
            abort(403, 'You can only edit assessments for your own courses.');
        }

        $this->assessment = $assessment;
        $this->title = $assessment->title;
        $this->description = $assessment->description;
        $this->assessment_type = $assessment->assessment_type;
        $this->max_attempts = $assessment->max_attempts;
        $this->time_limit_minutes = $assessment->time_limit_minutes;
        $this->passing_score = $assessment->passing_score;
        $this->xp_reward = $assessment->xp_reward;
        $this->is_required = (bool) $assessment->is_required;
        $this->show_results_immediately = (bool) $assessment->show_results_immediately;
        $this->is_randomized = $assessment->is_randomized ?? false;
        $this->shuffle_options = $assessment->shuffle_options ?? false;
        $this->questions_per_attempt = $assessment->questions_per_attempt;
        $this->show_correct_answers = $assessment->show_correct_answers ?? true;
        $this->allow_review = $assessment->allow_review ?? true;

        if ($assessment->assessment_type === 'assignment') {
            $assignmentData = $assessment->assignment_data ?? [];
            $this->assignment_instructions = (string) ($assignmentData['instructions'] ?? '');
            $this->assignment_due_date = ! empty($assignmentData['due_date'])
                ? \Illuminate\Support\Carbon::parse($assignmentData['due_date'])->format('Y-m-d')
                : null;
            $this->assignment_max_points = (int) ($assignmentData['max_points'] ?? 100);
            $this->assignment_allow_text = (bool) ($assignmentData['allow_text'] ?? true);
            $this->assignment_allow_files = (bool) ($assignmentData['allow_files'] ?? true);
            $this->assignment_existing_attachments = $assessment->assignmentAttachments();
            $this->tab = 'settings';
        }
        
        // Auto-open attempt if provided
        if ($attempt) {
            $this->tab = 'submissions';
            $this->startGrading($attempt);
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['questions', 'settings', 'submissions'], true)) {
            $this->tab = $tab;
            $this->showSubmissions = $tab === 'submissions';
        }
    }

    public function toggleLock(): void
    {
        $this->assessment->update(['is_locked' => ! $this->assessment->is_locked]);
        session()->flash('message', $this->assessment->is_locked
            ? 'Locked: students can no longer open this assessment.'
            : 'Unlocked: students can open this assessment.');
    }

    public function savePool(?int $count = null): void
    {
        $this->questions_per_attempt = $count;
        $this->validateOnly('questions_per_attempt');

        $this->assessment->update(['questions_per_attempt' => $this->questions_per_attempt ?: null]);
        session()->flash('message', $this->questions_per_attempt
            ? "Each attempt now draws {$this->questions_per_attempt} random questions from the pool."
            : 'Every attempt now gets all questions.');
    }

    public function updateAssessment()
    {
        $rules = $this->rules;

        if ($this->assessment_type === 'assignment') {
            $rules['assignment_instructions'] = 'nullable|string';
            $rules['assignment_due_date'] = 'nullable|date';
            $rules['assignment_max_points'] = 'required|integer|min:1|max:1000';
            $rules['assignment_allow_text'] = 'boolean';
            $rules['assignment_allow_files'] = 'boolean';
            $rules['assignmentBriefFiles.*'] = 'nullable|file|max:10240';
        }

        $this->validate($rules);

        if ($this->assessment_type === 'assignment' && ! $this->assignment_allow_text && ! $this->assignment_allow_files) {
            $this->addError('assignment_allow_files', 'Enable text response, file upload, or both.');

            return;
        }

        $payload = [
            'title' => $this->title,
            'description' => $this->description,
            'assessment_type' => $this->assessment_type,
            'max_attempts' => $this->max_attempts,
            'time_limit_minutes' => $this->time_limit_minutes,
            'passing_score' => $this->passing_score,
            'xp_reward' => $this->xp_reward,
            'is_required' => $this->is_required,
            'show_results_immediately' => $this->show_results_immediately,
            'is_randomized' => $this->is_randomized,
            'shuffle_options' => $this->shuffle_options,
            'questions_per_attempt' => $this->questions_per_attempt ?: null,
            'show_correct_answers' => $this->show_correct_answers,
            'allow_review' => $this->allow_review,
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ];

        if ($this->assessment_type === 'assignment') {
            $attachments = $this->assignment_existing_attachments;

            foreach ($this->assignmentBriefFiles as $file) {
                $path = $file->store('assessments/briefs', 'public');
                $attachments[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                ];
            }

            $payload['assignment_data'] = [
                'instructions' => trim($this->assignment_instructions) ?: null,
                'due_date' => $this->assignment_due_date ?: null,
                'max_points' => $this->assignment_max_points,
                'submission_format' => $this->assignment_allow_files ? 'file' : 'text',
                'file_types' => ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png'],
                'max_file_size' => 10,
                'allow_text' => $this->assignment_allow_text,
                'allow_files' => $this->assignment_allow_files,
                'attachments' => $attachments,
            ];
        }

        $this->assessment->update($payload);

        if ($this->assessment_type === 'assignment') {
            $this->assignmentBriefFiles = [];
            $this->assignment_existing_attachments = $this->assessment->fresh()->assignmentAttachments();
        }

        session()->flash('message', 'Assessment updated and approved successfully!');
    }

    public function removeAssignmentBriefFile(int $index): void
    {
        unset($this->assignmentBriefFiles[$index]);
        $this->assignmentBriefFiles = array_values($this->assignmentBriefFiles);
    }

    public function removeExistingAssignmentAttachment(int $index): void
    {
        $attachment = $this->assignment_existing_attachments[$index] ?? null;
        if (! $attachment) {
            return;
        }

        if (! empty($attachment['path']) && Storage::disk('public')->exists($attachment['path'])) {
            Storage::disk('public')->delete($attachment['path']);
        }

        unset($this->assignment_existing_attachments[$index]);
        $this->assignment_existing_attachments = array_values($this->assignment_existing_attachments);
    }

    public function openQuestionModal($questionId = null)
    {
        if ($questionId && ! $this->assessment->questions()->whereKey($questionId)->exists()) {
            return;
        }

        $this->editingQuestionId = $questionId;
        $this->questionEditorKey++;
        $this->showQuestionModal = true;
    }

    #[On('question-editor-cancelled')]
    public function closeQuestionModal()
    {
        $this->showQuestionModal = false;
        $this->editingQuestionId = null;
    }

    #[On('question-saved')]
    public function questionSaved(): void
    {
        $this->closeQuestionModal();
        $this->assessment->unsetRelation('questions');
        session()->flash('message', 'Question saved successfully!');
    }

    public function openBankPicker(): void
    {
        $this->reset(['bankSearch', 'bankType', 'bankDifficulty', 'bankTag', 'bankSelected', 'bankUnusedOnly', 'bankLimit']);
        $this->bankCourse = 'this';
        $this->showBankPicker = true;
    }

    public function clearBankFilters(): void
    {
        $this->reset(['bankSearch', 'bankType', 'bankDifficulty', 'bankTag', 'bankUnusedOnly', 'bankLimit']);
    }

    public function showMoreBank(): void
    {
        $this->bankLimit += 50;
    }

    /**
     * Selects every question currently listed, or clears them if they are all selected already.
     */
    public function toggleAllBank(): void
    {
        $listed = $this->bankQuery()->latest('questions.id')->limit($this->bankLimit)->pluck('questions.id')->map(fn ($id) => (string) $id)->all();
        $selected = array_map('strval', $this->bankSelected);

        $this->bankSelected = array_diff($listed, $selected) === []
            ? array_values(array_diff($selected, $listed))
            : array_values(array_unique(array_merge($selected, $listed)));
    }

    public function updated($property): void
    {
        if (in_array($property, ['bankSearch', 'bankType', 'bankDifficulty', 'bankTag', 'bankCourse', 'bankUnusedOnly'], true)) {
            $this->bankLimit = 50;
        }
    }

    public function closeBankPicker(): void
    {
        $this->showBankPicker = false;
        $this->bankSelected = [];
    }

    /**
     * Questions this user may add here, before any picker filter or course scope.
     */
    protected function bankBaseQuery()
    {
        return Question::query()
            ->visibleTo(Auth::user())
            ->active()
            ->whereNull('questions.quiz_id')
            ->whereNotIn('questions.id', $this->assessment->questions()->pluck('questions.id'))
            ->whereIn('question_type', array_keys($this->getAvailableQuestionTypes()));
    }

    /**
     * "this" = the course being edited, "all" = every course the user can see, or a course id.
     */
    protected function scopeBankToCourse($query, string $scope)
    {
        $courseId = $scope === 'this' ? $this->assessment->course_id : (is_numeric($scope) ? (int) $scope : null);

        return $query->when($courseId, fn ($q) => $q->where(fn ($w) => $w
            ->whereHas('placements', fn ($p) => $p->where('course_id', $courseId))
            ->orWhereHas('assessments', fn ($a) => $a->where('assessments.course_id', $courseId))));
    }

    protected function bankQuery()
    {
        return $this->scopeBankToCourse($this->bankBaseQuery(), $this->bankCourse)
            ->when($this->bankSearch !== '', fn ($q) => $q->where('question_text', 'like', '%'.$this->bankSearch.'%'))
            ->when($this->bankType !== '', fn ($q) => $q->where('question_type', $this->bankType))
            ->when($this->bankDifficulty !== '', fn ($q) => $q->where('difficulty', $this->bankDifficulty))
            ->when($this->bankTag !== '', fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('tags.id', $this->bankTag)))
            ->when($this->bankUnusedOnly, fn ($q) => $q->whereDoesntHave('assessments'));
    }

    public function addSelectedFromBank(): void
    {
        $ids = $this->bankQuery()->whereIn('questions.id', array_map('intval', $this->bankSelected))->pluck('questions.id');

        if ($ids->isEmpty()) {
            $this->addError('bankSelected', 'Select at least one question.');

            return;
        }

        $added = $this->assessment->pinQuestions($ids);
        $this->assessment->unsetRelation('questions');
        $this->closeBankPicker();
        session()->flash('message', $added === 1 ? '1 question added from the bank.' : "{$added} questions added from the bank.");
    }

    /**
     * Removes the question from this assessment only. The question stays in the bank and in any
     * other assessment that uses it.
     */
    public function deleteQuestion($questionId)
    {
        $this->assessment->questions()->detach($questionId);
        $this->assessment->unsetRelation('questions');
        session()->flash('message', 'Question removed from this assessment. It is still in the Question Bank.');
    }

    public function moveQuestion(int $questionId, int $direction): void
    {
        $ids = $this->assessment->questions()->pluck('questions.id')->map(fn ($id) => (int) $id)->values()->all();
        $index = array_search($questionId, $ids, true);
        $target = $index === false ? false : $index + ($direction < 0 ? -1 : 1);

        if ($target === false || ! isset($ids[$target])) {
            return;
        }

        [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];
        $this->reorderQuestions($ids);
    }

    public function reorderQuestions($questionIds)
    {
        $current = $this->assessment->questions()->pluck('questions.id')->map(fn ($id) => (int) $id)->all();

        foreach (array_values($questionIds) as $index => $questionId) {
            if (in_array((int) $questionId, $current, true)) {
                $this->assessment->questions()->updateExistingPivot((int) $questionId, ['position' => $index + 1]);
            }
        }
        $this->assessment->unsetRelation('questions');
    }

    public function getDefaultQuestionType()
    {
        // Return default question type based on assessment type
        return match($this->assessment_type) {
            'quiz' => 'multiple_choice',
            'assignment' => 'essay',
            'pre_project_test' => 'multiple_choice',
            'post_project_test' => 'multiple_choice',
            'unit_survey' => 'rating',
            'rubric_assessment' => 'rubric_criteria',
            'peer_review' => 'rating',
            'self_assessment' => 'rating',
            default => 'multiple_choice',
        };
    }

    public function getAvailableQuestionTypes()
    {
        // Return available question types based on assessment type
        return match($this->assessment_type) {
            'quiz' => [
                'multiple_choice' => 'Multiple Choice (pick one)',
                'multiple_select' => 'Multiple Select (pick all that apply)',
                'true_false' => 'True/False',
                'short_answer' => 'Short Answer',
                'essay' => 'Essay',
                'matching' => 'Matching',
                'fill_blank' => 'Fill in the Blank',
                'ordering' => 'Ordering',
            ],
            'assignment' => [
                'essay' => 'Essay Question',
                'file_upload' => 'File Upload',
                'code_submission' => 'Code Submission',
                'rubric_criteria' => 'Rubric Criteria',
            ],
            'pre_project_test' => [
                'multiple_choice' => 'Multiple Choice (pick one)',
                'multiple_select' => 'Multiple Select (pick all that apply)',
                'true_false' => 'True/False',
                'short_answer' => 'Short Answer',
                'essay' => 'Essay',
            ],
            'post_project_test' => [
                'multiple_choice' => 'Multiple Choice (pick one)',
                'multiple_select' => 'Multiple Select (pick all that apply)',
                'true_false' => 'True/False',
                'short_answer' => 'Short Answer',
                'essay' => 'Essay',
                'matching' => 'Matching',
                'fill_blank' => 'Fill in the Blank',
            ],
            'unit_survey' => [
                'rating' => 'Rating Scale',
                'choice' => 'Single Choice',
                'multiple_choice' => 'Multiple Choice',
                'short_answer' => 'Text Response',
            ],
            'rubric_assessment' => [
                'rubric_criteria' => 'Rubric Criteria',
                'rating' => 'Rating Scale',
            ],
            'peer_review' => [
                'rating' => 'Rating',
                'choice' => 'Choice',
                'multiple_choice' => 'Multiple Choice',
                'short_answer' => 'Text Feedback',
            ],
            'self_assessment' => [
                'rating' => 'Self-Rating',
                'choice' => 'Single Choice',
                'short_answer' => 'Text Response',
                'essay' => 'Reflection',
            ],
            default => [
                'multiple_choice' => 'Multiple Choice',
                'essay' => 'Essay',
            ],
        };
    }

    public $gradingAttempt = false;
    public $attemptScore = 0;
    public $attemptFeedback = '';
    public $attemptMaxScore = 100;

    public function viewAttempt($attemptId)
    {
        $attempt = AssessmentAttempt::with(['user', 'assessment'])
            ->find($attemptId);
        
        if ($attempt) {
            \App\Support\SubmissionAccess::authorizeView(Auth::user(), $attempt);
        }
        
        $this->selectedAttempt = $attempt;
        
        // Initialize grading fields if assessment type is assignment
        if ($attempt && $attempt->assessment->assessment_type === 'assignment') {
            $this->attemptMaxScore = $attempt->maxScore();
            $this->attemptScore = $attempt->scoreAsPoints() ?? 0;
            $answers = $attempt->answers ?? [];
            $this->attemptFeedback = $answers['feedback'] ?? '';
        }
    }

    public function startGrading($attemptId)
    {
        $this->viewAttempt($attemptId);
        $this->gradingAttempt = true;
    }

    public function closeAttemptView()
    {
        $this->selectedAttempt = null;
        $this->gradingAttempt = false;
        $this->attemptScore = 0;
        $this->attemptFeedback = '';
    }

    public function saveAttemptGrade()
    {
        if (!$this->selectedAttempt) {
            return;
        }

        $this->validate([
            'attemptScore' => 'required|numeric|min:0|max:' . $this->attemptMaxScore,
            'attemptFeedback' => 'nullable|string',
        ]);

        $attempt = $this->selectedAttempt;
        $assessment = $attempt->assessment;

        $result = app(\App\Services\Assessments\AttemptGradeRecorder::class)->record(
            $attempt,
            (float) $this->attemptScore,
            $this->attemptFeedback,
            Auth::user(),
            null,
            (float) $this->attemptMaxScore,
        );
        $percentage = $result['percentage'];
        $passed = $result['passed'];

        // Award XP if passed
        if ($passed && $assessment->xp_reward) {
            $user = $attempt->user;
            if (!$user->points) {
                \App\Models\UserPoint::create([
                    'user_id' => $user->id,
                    'total_points' => 0,
                    'level' => 1,
                ]);
            }
            $user->points->addPoints((int) $assessment->xp_reward);
        }

        // Send notification
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyAssessmentResult(
                $attempt->user,
                $assessment,
                $passed,
                round($percentage, 1)
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send grade notification', ['error' => $e->getMessage()]);
        }

        session()->flash('message', 'Grade saved successfully!');
        $this->closeAttemptView();
        $this->assessment->refresh();
    }

    public function backToBuilder(): void
    {
        $this->dispatch('close-form')->to(\App\Livewire\Curriculum\NewBuilder::class);
    }

    public function render()
    {
        $questions = $this->assessment->questions()
            ->with(['options', 'tags'])
            ->withCount('assessments')
            ->get();
        $totalPoints = $questions->sum('points');

        $bank = null;
        if ($this->showBankPicker) {
            $user = Auth::user();
            $bank = [
                'questions' => $this->bankQuery()
                    ->with(['tags', 'options', 'placements.course:id,title', 'placements.module:id,title', 'creator:id,name'])
                    ->withCount('assessments')
                    ->latest('questions.id')
                    ->limit($this->bankLimit)
                    ->get(),
                'total' => $this->bankQuery()->count(),
                'thisCourseCount' => $this->scopeBankToCourse($this->bankBaseQuery(), 'this')->count(),
                'allCount' => $this->bankBaseQuery()->count(),
                'tags' => Tag::whereHas('questions', fn ($q) => $this->scopeBankToCourse($q->visibleTo($user), $this->bankCourse))->orderBy('name')->get(['id', 'name']),
                'courses' => Course::query()
                    ->when(! $user->isAdmin(), fn ($q) => $q->where(fn ($w) => $w
                        ->where('instructor_id', $user->id)
                        ->orWhereHas('collaborators', fn ($c) => $c->where('user_id', $user->id))))
                    ->orderBy('title')
                    ->get(['id', 'title']),
            ];
        }
        
        // Get all attempts for teachers/admins - scoped by visibility
        $attempts = AssessmentAttempt::with(['user', 'questionSet'])
            ->visibleTo(Auth::user())
            ->where('assessment_id', $this->assessment->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->paginate(10);

        $finished = AssessmentAttempt::visibleTo(Auth::user())
            ->where('assessment_id', $this->assessment->id)
            ->where('status', 'completed');
        $graded = (clone $finished)->whereNotNull('score')->with('questionSet')->get();
        $percents = $graded->map(fn ($a) => $a->scorePercentage())->filter(fn ($p) => $p !== null);
        $submissionStats = [
            'total' => (clone $finished)->count(),
            'pending' => (clone $finished)->whereNull('score')->count(),
            'average' => $percents->isNotEmpty() ? (int) round($percents->avg()) : null,
            'passRate' => $graded->isNotEmpty() ? (int) round($graded->where('is_passed', true)->count() / $graded->count() * 100) : null,
        ];

        return view('livewire.assessments.edit', [
            'submissionStats' => $submissionStats,
            'typeMeta' => Manage::TYPES[$this->assessment_type] ?? ['label' => ucfirst(str_replace('_', ' ', (string) $this->assessment_type)), 'icon' => 'clipboard-document-check', 'tile' => 'bg-gray-100 text-gray-600'],
            'questions' => $questions,
            'totalPoints' => $totalPoints,
            'availableQuestionTypes' => $this->getAvailableQuestionTypes(),
            'bank' => $bank,
            'attempts' => $attempts,
            'selectedAttemptQuestions' => $this->selectedAttempt
                ? app(\App\Services\Assessments\AttemptQuestionModels::class)->for($this->selectedAttempt)
                : collect(),
        ]);
    }
}
