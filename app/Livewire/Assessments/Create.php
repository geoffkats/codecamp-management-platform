<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Create extends Component
{
    use WithFileUploads;

    public bool $embedded = false;
    public $course_id;
    public $lesson_id = null;
    public $title = '';
    public $assessment_type = 'quiz';
    public $description = '';
    public $max_attempts = 1;
    public $time_limit_minutes = null;
    public $passing_score = 70;
    public $xp_reward = 50;
    public $is_required = false;
    public $show_results_immediately = true;
    public $is_randomized = false;
    public $shuffle_options = false;
    public $show_correct_answers = true;
    public $allow_review = true;
    public $is_locked = false;

    protected function queryString(): array
    {
        // Don't rewrite the builder URL when this form is embedded
        if ($this->embedded) {
            return [];
        }

        return [
            'course_id',
            'lesson_id',
        ];
    }
    
    // Project platform for pre/post project tests
    public $project_platform = null; // 'scratch', 'other', or null
    
    // Type-specific fields
    public $rubric_criteria = [];
    public $survey_data = [];
    public $peer_review_data = [];
    public $self_assessment_data = [];

    // Assignment-specific fields
    public string $assignment_instructions = '';
    public ?string $assignment_due_date = null;
    public int $assignment_max_points = 100;
    public bool $assignment_allow_text = true;
    public bool $assignment_allow_files = true;
    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $assignmentBriefFiles = [];

    protected $rules = [
        'course_id' => 'required|exists:courses,id',
        'lesson_id' => 'nullable|exists:lessons,id',
        'title' => 'required|string|max:255',
        'assessment_type' => 'required|in:quiz,assignment,pre_project_test,post_project_test,unit_survey,rubric_assessment,peer_review,self_assessment',
        'description' => 'nullable|string',
        'max_attempts' => 'required|integer|min:1',
        'time_limit_minutes' => 'nullable|integer|min:1',
        'passing_score' => 'required|integer|min:0|max:100',
        'xp_reward' => 'required|integer|min:0',
        'is_required' => 'boolean',
        'show_results_immediately' => 'boolean',
        'is_randomized' => 'boolean',
        'shuffle_options' => 'boolean',
        'show_correct_answers' => 'boolean',
        'allow_review' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function mount($courseId = null, $lessonId = null)
    {
        if ($courseId) {
            $this->course_id = $courseId;
        }

        if ($lessonId) {
            $this->lesson_id = $lessonId;
        }

        // Fallback to query string values when component is mounted via a plain URL
        $this->course_id = $this->course_id ?? request()->query('course_id');
        $this->lesson_id = $this->lesson_id ?? request()->query('lesson_id');

        $requestedType = request()->query('type');
        if (in_array($requestedType, ['quiz', 'assignment', 'pre_project_test', 'post_project_test', 'unit_survey', 'rubric_assessment', 'peer_review', 'self_assessment'], true)) {
            $this->assessment_type = $requestedType;
        }

        if ($this->assessment_type === 'assignment') {
            $this->applyAssignmentDefaults();
        }
    }

    public function updatedAssessmentType()
    {
        // Reset type-specific fields when type changes
        $this->rubric_criteria = [];
        $this->survey_data = [];
        $this->peer_review_data = [];
        $this->self_assessment_data = [];
        $this->project_platform = null;
        $this->assignment_instructions = '';
        $this->assignment_due_date = null;
        $this->assignment_max_points = 100;
        $this->assignment_allow_text = true;
        $this->assignment_allow_files = true;
        $this->assignmentBriefFiles = [];

        if ($this->assessment_type === 'assignment') {
            $this->applyAssignmentDefaults();
        }
    }

    private function applyAssignmentDefaults(): void
    {
        $this->max_attempts = max(1, (int) $this->max_attempts);
        $this->show_results_immediately = false;
        $this->is_randomized = false;
        $this->shuffle_options = false;
        $this->show_correct_answers = false;
        $this->time_limit_minutes = null;
    }

    public function removeAssignmentBriefFile(int $index): void
    {
        unset($this->assignmentBriefFiles[$index]);
        $this->assignmentBriefFiles = array_values($this->assignmentBriefFiles);
    }

    public function addRubricCriterion()
    {
        $this->rubric_criteria[] = [
            'name' => '',
            'description' => '',
            'max_points' => 0,
            'weight' => 1,
        ];
    }

    public function removeRubricCriterion($index)
    {
        unset($this->rubric_criteria[$index]);
        $this->rubric_criteria = array_values($this->rubric_criteria);
    }

    public function save()
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

        if ($this->lesson_id) {
            $lessonValid = Lesson::where('id', $this->lesson_id)
                ->where('course_id', $this->course_id)
                ->exists();

            if (! $lessonValid) {
                $this->addError('lesson_id', 'The selected lesson does not belong to this course.');

                return;
            }
        }

        $data = [
            'course_id' => $this->course_id,
            'lesson_id' => $this->lesson_id ?: null,
            'title' => $this->title,
            'assessment_type' => $this->assessment_type,
            'description' => $this->description,
            'max_attempts' => $this->max_attempts,
            'time_limit_minutes' => $this->time_limit_minutes,
            'passing_score' => $this->passing_score,
            'xp_reward' => $this->xp_reward,
            'is_required' => $this->is_required,
            'show_results_immediately' => $this->show_results_immediately,
            'is_randomized' => $this->is_randomized,
            'shuffle_options' => $this->shuffle_options,
            'show_correct_answers' => $this->show_correct_answers,
            'allow_review' => $this->allow_review,
            'is_locked' => $this->is_locked,
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ];

        // Add type-specific data
        if ($this->assessment_type === 'rubric_assessment') {
            $data['rubric_criteria'] = $this->rubric_criteria;
        } elseif ($this->assessment_type === 'unit_survey') {
            $data['survey_data'] = $this->survey_data;
        } elseif ($this->assessment_type === 'peer_review') {
            $data['peer_review_data'] = $this->peer_review_data;
        } elseif ($this->assessment_type === 'self_assessment') {
            $data['self_assessment_data'] = $this->self_assessment_data;
        } elseif (in_array($this->assessment_type, ['pre_project_test', 'post_project_test'])) {
            // Store project platform
            $data['project_test_data'] = [
                'platform' => $this->project_platform,
            ];
        } elseif ($this->assessment_type === 'assignment') {
            $attachments = [];
            foreach ($this->assignmentBriefFiles as $file) {
                $path = $file->store('assessments/briefs', 'public');
                $attachments[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                ];
            }

            $data['assignment_data'] = [
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

        $assessment = Assessment::create($data);

        session()->flash(
            'message',
            $this->assessment_type === 'assignment'
                ? 'Assignment created! Students can submit text and files.'
                : 'Assessment created! Now add your questions below.'
        );

        if ($this->embedded) {
            // Open the new assessment in the embedded editor within the builder
            $this->dispatch('select-item', type: 'assessment', id: $assessment->id, parentId: null)
                ->to(\App\Livewire\Curriculum\NewBuilder::class);
            return;
        }

        return $this->redirect(route('assessments.edit', $assessment), navigate: true);
    }

    public function cancelEmbedded(): void
    {
        $this->dispatch('close-form')->to(\App\Livewire\Curriculum\NewBuilder::class);
    }

    public function render()
    {
        // If the user can edit courses (teacher/admin), show all courses so they can
        // create assessments across any course. Otherwise show only courses they
        // teach or are enrolled in.
        if (Auth::user() && Auth::user()->can('edit_courses')) {
            $courses = Course::all();
        } else {
            $courses = Course::where('instructor_id', Auth::id())
                ->orWhereHas('enrollments', function ($q) {
                    $q->where('user_id', Auth::id());
                })
                ->get();
        }

        $lessons = $this->course_id 
            ? Lesson::where('course_id', $this->course_id)->get()
            : collect();

        $assessmentTypes = [
            'quiz' => 'Quiz',
            'assignment' => 'Assignment',
            'pre_project_test' => 'Pre-Project Test',
            'post_project_test' => 'Post-Project Test',
            'unit_survey' => 'Unit Survey',
            'rubric_assessment' => 'Rubric Assessment',
            'peer_review' => 'Peer Review',
            'self_assessment' => 'Self-Assessment',
        ];

        return view('livewire.assessments.create', [
            'courses' => $courses,
            'lessons' => $lessons,
            'assessmentTypes' => $assessmentTypes,
        ]);
    }
}
