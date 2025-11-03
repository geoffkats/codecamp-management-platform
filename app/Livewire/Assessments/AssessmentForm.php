<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Lesson;
use Livewire\Component;

class AssessmentForm extends Component
{
    public $assessment;
    public $course_id;
    public $lesson_id;
    public $title;
    public $assessment_type = 'quiz';
    public $description;
    public $max_attempts = 1;
    public $time_limit_minutes;
    public $passing_score = 70;
    public $xp_reward = 50;
    public $is_required = false;
    public $show_results_immediately = true;
    public $is_locked = false;
    public $questions = [];

    public function mount($courseId, $lessonId = null, ?Assessment $assessment = null)
    {
        $this->course_id = $courseId;
        $this->lesson_id = $lessonId;
        $this->assessment = $assessment;

        if ($assessment) {
            $this->title = $assessment->title;
            $this->assessment_type = $assessment->assessment_type;
            $this->description = $assessment->description;
            $this->max_attempts = $assessment->max_attempts;
            $this->time_limit_minutes = $assessment->time_limit_minutes;
            $this->passing_score = $assessment->passing_score;
            $this->xp_reward = $assessment->xp_reward;
            $this->is_required = $assessment->is_required;
            $this->show_results_immediately = $assessment->show_results_immediately;
            $this->is_locked = $assessment->is_locked;
            $this->questions = $assessment->questions ?? [];
        }
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'assessment_type' => 'required|in:quiz,assignment,pre_project_test,post_project_test,unit_survey,rubric_assessment,peer_review,self_assessment',
            'max_attempts' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'xp_reward' => 'required|integer|min:0',
        ]);

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
            'is_locked' => $this->is_locked,
            'questions' => $this->questions,
        ];

        if ($this->assessment) {
            $this->assessment->update($data);
            session()->flash('message', 'Assessment updated successfully!');
        } else {
            Assessment::create($data);
            session()->flash('message', 'Assessment created successfully!');
        }

        return redirect()->route('assessments.index', ['course' => $this->course_id]);
    }

    public function render()
    {
        return view('livewire.assessments.assessment-form', [
            'lessons' => Lesson::where('course_id', $this->course_id)->get(),
        ]);
    }
}

