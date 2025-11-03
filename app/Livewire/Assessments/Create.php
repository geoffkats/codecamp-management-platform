<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
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
    
    // Type-specific fields
    public $rubric_criteria = [];
    public $survey_data = [];
    public $peer_review_data = [];
    public $self_assessment_data = [];

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
    }

    public function updatedAssessmentType()
    {
        // Reset type-specific fields when type changes
        $this->rubric_criteria = [];
        $this->survey_data = [];
        $this->peer_review_data = [];
        $this->self_assessment_data = [];
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
        $this->validate();

        $data = [
            'course_id' => $this->course_id,
            'lesson_id' => $this->lesson_id,
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
            'approval_status' => 'draft',
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
        }

        $assessment = Assessment::create($data);

        session()->flash('message', 'Assessment created successfully! You can now add questions or configure specific settings.');

        return $this->redirect(route('assessments.edit', $assessment), navigate: true);
    }

    public function render()
    {
        $courses = Course::where('instructor_id', Auth::id())
            ->orWhereHas('enrollments', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->get();

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
