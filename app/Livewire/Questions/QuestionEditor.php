<?php

namespace App\Livewire\Questions;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Question;
use App\Services\Assessments\QuestionWriter;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * The single question editor. Used inside the assessment builder (modal) and on the Question
 * Bank create/edit pages.
 */
class QuestionEditor extends Component
{
    use WithFileUploads;

    public const BANK_TYPES = [
        'multiple_choice' => 'Multiple Choice (pick one)',
        'multiple_select' => 'Multiple Select (pick all that apply)',
        'true_false' => 'True/False',
        'short_answer' => 'Short Answer',
        'essay' => 'Essay',
        'fill_blank' => 'Fill in the Blank',
        'matching' => 'Matching',
        'ordering' => 'Ordering',
        'rating' => 'Rating Scale',
        'code_submission' => 'Code Submission',
        'file_upload' => 'File Upload',
        'rubric_criteria' => 'Rubric Criteria',
    ];

    /** @var array<string, array{icon: string, tile: string}> */
    public const TYPE_STYLES = [
        'multiple_choice' => ['icon' => 'list-bullet', 'tile' => 'bg-orange-100 text-orange-600 dark:bg-orange-900/40 dark:text-orange-300'],
        'choice' => ['icon' => 'list-bullet', 'tile' => 'bg-orange-100 text-orange-600 dark:bg-orange-900/40 dark:text-orange-300'],
        'multiple_select' => ['icon' => 'check-circle', 'tile' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300'],
        'true_false' => ['icon' => 'scale', 'tile' => 'bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-300'],
        'short_answer' => ['icon' => 'pencil', 'tile' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300'],
        'essay' => ['icon' => 'document-text', 'tile' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300'],
        'fill_blank' => ['icon' => 'ellipsis-horizontal-circle', 'tile' => 'bg-teal-100 text-teal-600 dark:bg-teal-900/40 dark:text-teal-300'],
        'matching' => ['icon' => 'arrows-right-left', 'tile' => 'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/40 dark:text-cyan-300'],
        'ordering' => ['icon' => 'bars-arrow-down', 'tile' => 'bg-pink-100 text-pink-600 dark:bg-pink-900/40 dark:text-pink-300'],
        'rating' => ['icon' => 'star', 'tile' => 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/40 dark:text-yellow-300'],
        'code_submission' => ['icon' => 'code-bracket', 'tile' => 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300'],
        'file_upload' => ['icon' => 'arrow-up-tray', 'tile' => 'bg-lime-100 text-lime-700 dark:bg-lime-900/40 dark:text-lime-300'],
        'rubric_criteria' => ['icon' => 'clipboard-document-check', 'tile' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-300'],
    ];

    public ?int $questionId = null;

    public ?int $assessmentId = null;

    /** 'modal' inside the assessment builder, 'page' on the Question Bank. */
    public string $context = 'modal';

    /** @var array<string, string> */
    public array $allowedTypes = [];

    public string $defaultType = 'multiple_choice';

    public array $questionFormData = [];

    public array $questionOptions = [];

    public $questionImage = null;

    public array $tempOptionImages = [];

    public array $rubricCriteria = [];

    public array $matchingPairs = [];

    public array $orderingItems = [];

    public array $ratingScaleSettings = ['min' => 1, 'max' => 5, 'labels' => []];

    public array $fillBlankSettings = ['blanks' => []];

    public array $codeSubmissionSettings = ['language' => 'javascript', 'template' => ''];

    public array $shortAnswer = ['correct_answer' => '', 'alternatives' => '', 'case_sensitive' => false];

    public string $tagInput = '';

    /** @var array<int, array{course_id: int|string|null, course_module_id: int|string|null}> */
    public array $placements = [];

    public function mount(?int $questionId = null, ?int $assessmentId = null, string $context = 'modal', array $allowedTypes = [], string $defaultType = 'multiple_choice', ?int $courseId = null): void
    {
        $user = Auth::user();
        abort_unless($user && $user->can('edit_courses'), 403);

        $this->questionId = $questionId;
        $this->assessmentId = $assessmentId;
        $this->context = $context;
        $this->allowedTypes = $allowedTypes ?: self::BANK_TYPES;
        $this->defaultType = $defaultType;

        $assessment = $this->assessment();
        if ($assessment) {
            $this->authorizeAssessment($assessment);
        }

        if ($questionId) {
            $question = Question::with(['options', 'tags', 'placements'])->findOrFail($questionId);
            abort_unless($question->isVisibleTo($user), 403);
            $this->fillFromQuestion($question);
        } else {
            $this->resetForm();
            $courseId ??= $assessment?->course_id;
            if ($courseId) {
                $this->placements = [[
                    'course_id' => $courseId,
                    'course_module_id' => $assessment?->lesson?->module_id,
                ]];
            }
        }
    }

    protected function assessment(): ?Assessment
    {
        return $this->assessmentId ? Assessment::with(['course', 'lesson'])->find($this->assessmentId) : null;
    }

    protected function authorizeAssessment(Assessment $assessment): void
    {
        $user = Auth::user();
        $hasGlobalEdit = $user->hasPermission('edit_courses') || $user->isAdmin();

        abort_if(
            ! $assessment->course || ($user->isTeacher() && ! $hasGlobalEdit && $assessment->course->instructor_id !== $user->id),
            403
        );
    }

    protected function fillFromQuestion(Question $question): void
    {
        $settings = is_array($question->settings) ? $question->settings : (json_decode((string) $question->settings, true) ?: []);

        $this->questionFormData = [
            'question_text' => $question->question_text,
            'question_type' => $question->question_type,
            'points' => $question->points,
            'difficulty' => $question->difficulty ?? 'medium',
            'status' => $question->status ?? 'active',
            'explanation' => $question->explanation,
            'image_url' => $question->image_url,
            'settings' => $settings,
        ];

        if (! isset($this->allowedTypes[$question->question_type])) {
            $this->allowedTypes[$question->question_type] = self::BANK_TYPES[$question->question_type] ?? ucwords(str_replace('_', ' ', $question->question_type));
        }

        $this->questionOptions = $question->options->map(fn ($option) => [
            'id' => $option->id,
            'option_text' => $option->option_text,
            'is_correct' => (bool) $option->is_correct,
            'image_url' => $option->image_url,
        ])->values()->all();

        $this->rubricCriteria = $settings['rubric_criteria'] ?? [];
        $this->matchingPairs = $settings['matching_pairs'] ?? [];
        $this->orderingItems = $settings['ordering_items'] ?? [];
        $this->ratingScaleSettings = $settings['rating_scale'] ?? ['min' => 1, 'max' => 5, 'labels' => []];
        $this->fillBlankSettings = $settings['fill_blank'] ?? ['blanks' => []];
        $this->codeSubmissionSettings = $settings['code_submission'] ?? ['language' => 'javascript', 'template' => ''];

        $key = $settings['correct_answer'] ?? $settings['short_answer']['correct_answer'] ?? '';
        if ($key === '' && $question->question_type === 'short_answer') {
            $key = (string) ($question->options->firstWhere('is_correct', true)?->option_text ?? '');
        }
        $alternatives = array_merge($settings['alternative_answers'] ?? [], $settings['short_answer']['alternative_answers'] ?? []);
        $this->shortAnswer = [
            'correct_answer' => (string) $key,
            'alternatives' => implode("\n", array_unique(array_map('strval', $alternatives))),
            'case_sensitive' => (bool) ($settings['case_sensitive'] ?? $settings['short_answer']['case_sensitive'] ?? false),
        ];

        $this->tagInput = $question->tags->pluck('name')->implode(', ');
        $this->placements = $question->placements
            ->map(fn ($p) => ['course_id' => $p->course_id, 'course_module_id' => $p->course_module_id])
            ->values()
            ->all();
    }

    public function resetForm(): void
    {
        $this->questionFormData = [
            'question_text' => '',
            'question_type' => isset($this->allowedTypes[$this->defaultType]) ? $this->defaultType : array_key_first($this->allowedTypes),
            'points' => 10,
            'difficulty' => 'medium',
            'status' => 'active',
            'explanation' => '',
            'image_url' => '',
            'settings' => [],
        ];
        $this->questionOptions = [];
        $this->questionImage = null;
        $this->tempOptionImages = [];
        $this->resetTypeSpecific();
        $this->tagInput = '';
    }

    protected function resetTypeSpecific(): void
    {
        $this->rubricCriteria = [];
        $this->matchingPairs = [];
        $this->orderingItems = [];
        $this->ratingScaleSettings = ['min' => 1, 'max' => 5, 'labels' => []];
        $this->fillBlankSettings = ['blanks' => []];
        $this->codeSubmissionSettings = ['language' => 'javascript', 'template' => ''];
        $this->shortAnswer = ['correct_answer' => '', 'alternatives' => '', 'case_sensitive' => false];
    }

    public function updatedQuestionFormDataQuestionType(): void
    {
        if (! in_array($this->questionFormData['question_type'], ['multiple_choice', 'multiple_select', 'true_false', 'choice'], true)) {
            $this->questionOptions = [];
        }
        if ($this->questionFormData['question_type'] === 'true_false' && $this->questionOptions === []) {
            $this->questionOptions = [
                ['id' => null, 'option_text' => 'True', 'is_correct' => true, 'image_url' => null],
                ['id' => null, 'option_text' => 'False', 'is_correct' => false, 'image_url' => null],
            ];
        }
        $this->resetTypeSpecific();
    }

    public function addQuestionOption(): void
    {
        $this->questionOptions[] = ['id' => null, 'option_text' => '', 'is_correct' => false, 'image_url' => null];
    }

    public function markCorrectOption(int $index): void
    {
        foreach ($this->questionOptions as $i => $option) {
            $this->questionOptions[$i]['is_correct'] = $i === $index;
        }
    }

    public function toggleCorrectOption(int $index): void
    {
        if (isset($this->questionOptions[$index])) {
            $this->questionOptions[$index]['is_correct'] = ! ($this->questionOptions[$index]['is_correct'] ?? false);
        }
    }

    public function removeQuestionOption(int $index): void
    {
        unset($this->questionOptions[$index], $this->tempOptionImages[$index]);
        $this->questionOptions = array_values($this->questionOptions);
        $this->tempOptionImages = array_values($this->tempOptionImages);
    }

    public function removeOptionImage(int $index): void
    {
        // Files are kept on disk: attempt snapshots may still show them.
        $this->questionOptions[$index]['image_url'] = null;
        unset($this->tempOptionImages[$index]);
    }

    public function removeQuestionImage(): void
    {
        $this->questionFormData['image_url'] = '';
        $this->questionImage = null;
    }

    public function addRubricCriterion(): void
    {
        $this->rubricCriteria[] = [
            'name' => '',
            'description' => '',
            'max_points' => 0,
            'weight' => 1,
            'performance_levels' => [
                ['level' => 'Excellent', 'points' => 100, 'description' => ''],
                ['level' => 'Good', 'points' => 75, 'description' => ''],
                ['level' => 'Satisfactory', 'points' => 50, 'description' => ''],
                ['level' => 'Needs Improvement', 'points' => 25, 'description' => ''],
            ],
        ];
    }

    public function removeRubricCriterion(int $index): void
    {
        unset($this->rubricCriteria[$index]);
        $this->rubricCriteria = array_values($this->rubricCriteria);
    }

    public function addMatchingPair(): void
    {
        $this->matchingPairs[] = ['left_item' => '', 'right_item' => ''];
    }

    public function removeMatchingPair(int $index): void
    {
        unset($this->matchingPairs[$index]);
        $this->matchingPairs = array_values($this->matchingPairs);
    }

    public function addOrderingItem(): void
    {
        $this->orderingItems[] = ['item_text' => '', 'correct_order' => count($this->orderingItems) + 1];
    }

    public function removeOrderingItem(int $index): void
    {
        unset($this->orderingItems[$index]);
        $this->orderingItems = array_values($this->orderingItems);
        foreach ($this->orderingItems as $i => $item) {
            $this->orderingItems[$i]['correct_order'] = $i + 1;
        }
    }

    public function addFillBlank(): void
    {
        $this->fillBlankSettings['blanks'][] = ['position' => '', 'correct_answer' => '', 'case_sensitive' => false, 'alternative_answers' => []];
    }

    public function removeFillBlank(int $index): void
    {
        unset($this->fillBlankSettings['blanks'][$index]);
        $this->fillBlankSettings['blanks'] = array_values($this->fillBlankSettings['blanks']);
    }

    public function addAlternativeAnswer(int $blankIndex): void
    {
        $this->fillBlankSettings['blanks'][$blankIndex]['alternative_answers'][] = '';
    }

    public function removeAlternativeAnswer(int $blankIndex, int $altIndex): void
    {
        unset($this->fillBlankSettings['blanks'][$blankIndex]['alternative_answers'][$altIndex]);
        $this->fillBlankSettings['blanks'][$blankIndex]['alternative_answers'] = array_values($this->fillBlankSettings['blanks'][$blankIndex]['alternative_answers']);
    }

    public function addPlacement(): void
    {
        $this->placements[] = ['course_id' => null, 'course_module_id' => null];
    }

    public function removePlacement(int $index): void
    {
        unset($this->placements[$index]);
        $this->placements = array_values($this->placements);
    }

    public function updatedPlacements($value, string $key): void
    {
        if (str_ends_with($key, '.course_id')) {
            $index = (int) explode('.', $key)[0];
            $this->placements[$index]['course_module_id'] = null;
        }
    }

    public function save(QuestionWriter $writer)
    {
        $type = $this->questionFormData['question_type'] ?? '';

        $this->validate([
            'questionFormData.question_text' => 'required|string',
            'questionFormData.question_type' => 'required|string|in:'.implode(',', array_keys($this->allowedTypes)),
            'questionFormData.points' => 'required|numeric|min:0|max:1000',
            'questionFormData.difficulty' => 'required|in:'.implode(',', array_keys(Question::DIFFICULTIES)),
            'questionFormData.status' => 'required|in:'.implode(',', array_keys(Question::STATUSES)),
            'questionImage' => 'nullable|image|max:5120',
            'tempOptionImages.*' => 'nullable|image|max:5120',
            'tagInput' => 'nullable|string|max:500',
            'placements.*.course_id' => 'nullable|integer|exists:courses,id',
            'placements.*.course_module_id' => 'nullable|integer|exists:course_modules,id',
        ], [], [
            'questionFormData.question_text' => 'question text',
            'questionFormData.points' => 'points',
        ]);

        if ($error = $this->typeValidationError($type)) {
            $this->addError('questionFormData.question_type', $error);

            return null;
        }

        $allowedCourseIds = $this->courseOptions()->pluck('id')->all();
        foreach ($this->placements as $placement) {
            if (! empty($placement['course_id']) && ! in_array((int) $placement['course_id'], $allowedCourseIds, true)) {
                $this->addError('placements', 'You can only place questions in courses you manage.');

                return null;
            }
        }

        $imagePath = $this->questionFormData['image_url'] ?: null;
        if ($this->questionImage) {
            $imagePath = $this->questionImage->store('assessments/questions', 'public');
        }

        $options = [];
        foreach ($this->questionOptions as $index => $option) {
            if (isset($this->tempOptionImages[$index]) && $this->tempOptionImages[$index]) {
                $option['image_url'] = $this->tempOptionImages[$index]->store('assessments/options', 'public');
            }
            $options[] = $option;
        }

        $data = [
            'question_text' => $this->questionFormData['question_text'],
            'question_type' => $type,
            'points' => $this->questionFormData['points'],
            'difficulty' => $this->questionFormData['difficulty'],
            'status' => $this->questionFormData['status'],
            'explanation' => $this->questionFormData['explanation'] ?: null,
            'image_url' => $imagePath,
            'settings' => $this->buildSettings($type),
        ];

        $existing = $this->questionId ? Question::findOrFail($this->questionId) : null;
        abort_if($existing && ! $existing->isVisibleTo(Auth::user()), 403);

        $assessment = $this->assessment();
        if (! $existing && $assessment) {
            $data['assessment_id'] = $assessment->id;
        }

        $question = $writer->save(
            $existing,
            $data,
            $options,
            preg_split('/[,\n]/', $this->tagInput) ?: [],
            $this->placements,
            $existing ? null : $assessment,
        );

        $this->questionId = $question->id;
        $this->questionImage = null;
        $this->tempOptionImages = [];

        if ($this->context === 'page') {
            session()->flash('message', 'Question saved.');

            return $this->redirect(route('questions.index'), navigate: true);
        }

        $this->dispatch('question-saved', questionId: $question->id);

        return null;
    }

    protected function typeValidationError(string $type): ?string
    {
        $filledOptions = array_filter($this->questionOptions, fn ($o) => trim((string) ($o['option_text'] ?? '')) !== '');
        $correct = array_filter($filledOptions, fn ($o) => ! empty($o['is_correct']));

        return match ($type) {
            'multiple_choice', 'choice', 'true_false' => count($filledOptions) < 2
                ? 'Add at least two answer options.'
                : (count($correct) !== 1 ? 'Mark exactly one option as correct.' : null),
            'multiple_select' => count($filledOptions) < 2
                ? 'Multiple select needs at least two options.'
                : (count($correct) < 2 ? 'Tick at least two correct answers so students can select more than one.' : null),
            'short_answer' => null,
            'matching' => count(array_filter($this->matchingPairs, fn ($p) => filled($p['left_item'] ?? null) && filled($p['right_item'] ?? null))) === 0
                ? 'Add at least one matching pair.' : null,
            'ordering' => count(array_filter($this->orderingItems, fn ($i) => filled($i['item_text'] ?? null))) < 2
                ? 'Add at least two items to order.' : null,
            'rubric_criteria' => count(array_filter($this->rubricCriteria, fn ($c) => filled($c['name'] ?? null))) === 0
                ? 'Add at least one rubric criterion.' : null,
            'fill_blank' => count(array_filter($this->fillBlankSettings['blanks'] ?? [], fn ($b) => filled($b['correct_answer'] ?? null))) === 0
                ? 'Add at least one blank with a correct answer.' : null,
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildSettings(string $type): array
    {
        $form = $this->questionFormData['settings'] ?? [];

        return match ($type) {
            'rubric_criteria' => ['rubric_criteria' => array_values($this->rubricCriteria)],
            'matching' => ['matching_pairs' => array_values(array_filter($this->matchingPairs, fn ($p) => filled($p['left_item'] ?? null) && filled($p['right_item'] ?? null)))],
            'ordering' => ['ordering_items' => array_values($this->orderingItems)],
            'rating' => ['rating_scale' => $this->ratingScaleSettings],
            'fill_blank' => ['fill_blank' => [
                'blanks' => collect($this->fillBlankSettings['blanks'] ?? [])
                    ->map(fn ($b) => array_merge($b, ['alternative_answers' => array_values(array_filter(array_map('trim', $b['alternative_answers'] ?? []), fn ($a) => $a !== ''))]))
                    ->values()
                    ->all(),
            ]],
            'code_submission' => ['code_submission' => $this->codeSubmissionSettings],
            'file_upload' => [
                'allowed_types' => $form['allowed_types'] ?? 'html,htm,css,pdf,doc,docx,txt,jpg,jpeg,png,gif,zip',
                'max_size' => $form['max_size'] ?? 10,
                'max_files' => $form['max_files'] ?? 1,
            ],
            'short_answer' => array_filter([
                'correct_answer' => trim((string) $this->shortAnswer['correct_answer']) ?: null,
                'alternative_answers' => array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $this->shortAnswer['alternatives']) ?: []), fn ($a) => $a !== '')) ?: null,
                'case_sensitive' => (bool) $this->shortAnswer['case_sensitive'] ?: null,
                'min_words' => $form['min_words'] ?? null,
                'max_words' => $form['max_words'] ?? null,
            ], fn ($v) => $v !== null && $v !== ''),
            'essay' => array_filter([
                'min_words' => $form['min_words'] ?? null,
                'max_words' => $form['max_words'] ?? null,
            ], fn ($v) => $v !== null && $v !== ''),
            default => [],
        };
    }

    /**
     * Courses this user can place questions in.
     */
    public function courseOptions()
    {
        $user = Auth::user();

        return Course::query()
            ->when(! $user->isAdmin(), fn ($q) => $q->where(fn ($w) => $w
                ->where('instructor_id', $user->id)
                ->orWhereHas('collaborators', fn ($c) => $c->where('user_id', $user->id))))
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    public function render()
    {
        $courseIds = collect($this->placements)->pluck('course_id')->filter()->unique()->all();
        $usage = $this->questionId
            ? \Illuminate\Support\Facades\DB::table('assessment_question')->where('question_id', $this->questionId)->count()
            : 0;

        return view('livewire.questions.question-editor', [
            'courses' => $this->courseOptions(),
            'modulesByCourse' => CourseModule::whereIn('course_id', $courseIds)->orderBy('order_index')->get(['id', 'course_id', 'title'])->groupBy('course_id'),
            'usageCount' => $usage,
            'difficulties' => Question::DIFFICULTIES,
            'statuses' => Question::STATUSES,
        ]);
    }
}
