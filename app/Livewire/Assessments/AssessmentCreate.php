<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\Course;
use Livewire\Component;

class AssessmentCreate extends Component
{
    public $course_id;
    public $lesson_id;
    public $title = '';
    public $assessment_type = 'quiz';
    public $description = '';
    public $max_attempts = 1;
    public $time_limit_minutes = '';
    public $passing_score = 70;
    public $xp_reward = 50;
    public $is_required = false;
    public $show_results_immediately = true;
    public $is_locked = false;

    protected $rules = [
        'course_id' => 'required|exists:courses,id',
        'lesson_id' => 'required|exists:lessons,id',
        'title' => 'required|string|max:255',
        'assessment_type' => 'required|in:quiz,assignment,pre_project_test,post_project_test,unit_survey,rubric_assessment,peer_review,self_assessment',
        'description' => 'nullable|string',
        'max_attempts' => 'required|integer|min:1',
        'time_limit_minutes' => 'nullable|integer|min:1',
        'passing_score' => 'required|integer|min:0|max:100',
        'xp_reward' => 'required|integer|min:0',
        'is_required' => 'boolean',
        'show_results_immediately' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function mount($courseId, $lessonId)
    {
        $this->course_id = $courseId;
        $this->lesson_id = $lessonId;
    }

    public function save()
    {
        $this->validate();

        $assessment = Assessment::create([
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
            'is_locked' => $this->is_locked,
            'approval_status' => 'draft',
        ]);

        session()->flash('message', 'Assessment created successfully!');

        return redirect()->route('assessments.show', $assessment->id);
    }

    public function render()
    {
        $course = Course::findOrFail($this->course_id);
        return view('livewire.assessments.assessment-create', [
            'course' => $course,
        ]);
    }
}

