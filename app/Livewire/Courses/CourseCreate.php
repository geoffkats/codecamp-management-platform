<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class CourseCreate extends Component
{
    use WithFileUploads;

    public $title = '';
    public $description = '';
    public $short_description = '';
    public $difficulty_level = 'Beginner';
    public $estimated_duration = '';
    public $price = 0;
    public $category = '';
    public $tags = [];
    public $tagInput = '';
    public $requirements = [];
    public $requirementInput = '';
    public $what_you_learn = [];
    public $learnInput = '';
    public $featured_image;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'short_description' => 'nullable|string',
        'difficulty_level' => 'required|in:Beginner,Intermediate,Advanced',
        'estimated_duration' => 'nullable|integer|min:1',
        'price' => 'nullable|numeric|min:0',
        'category' => 'nullable|string|max:255',
        'tags' => 'nullable|array',
        'requirements' => 'nullable|array',
        'what_you_learn' => 'nullable|array',
        'featured_image' => 'nullable|image|max:2048',
    ];

    public function addTag()
    {
        if (!empty($this->tagInput)) {
            $this->tags[] = $this->tagInput;
            $this->tagInput = '';
        }
    }

    public function removeTag($index)
    {
        unset($this->tags[$index]);
        $this->tags = array_values($this->tags);
    }

    public function addRequirement()
    {
        if (!empty($this->requirementInput)) {
            $this->requirements[] = $this->requirementInput;
            $this->requirementInput = '';
        }
    }

    public function removeRequirement($index)
    {
        unset($this->requirements[$index]);
        $this->requirements = array_values($this->requirements);
    }

    public function addLearningOutcome()
    {
        if (!empty($this->learnInput)) {
            $this->what_you_learn[] = $this->learnInput;
            $this->learnInput = '';
        }
    }

    public function removeLearningOutcome($index)
    {
        unset($this->what_you_learn[$index]);
        $this->what_you_learn = array_values($this->what_you_learn);
    }

    public function save()
    {
        $this->validate();

        $course = Course::create([
            'title' => $this->title,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'instructor_id' => Auth::id(),
            'difficulty_level' => $this->difficulty_level,
            'estimated_duration' => $this->estimated_duration,
            'price' => $this->price ?? 0,
            'category' => $this->category,
            'tags' => $this->tags,
            'requirements' => $this->requirements,
            'what_you_learn' => $this->what_you_learn,
            'approval_status' => 'draft',
        ]);

        if ($this->featured_image) {
            $course->featured_image = $this->featured_image->store('courses', 'public');
            $course->save();
        }

        session()->flash('message', 'Course created successfully!');

        return redirect()->route('courses.show', $course->id);
    }

    public function render()
    {
        return view('livewire.courses.course-create');
    }
}

