<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Str;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public $title = '';
    public $slug = '';
    public $short_description = '';
    public $description = '';
    public $difficulty_level = 'Beginner';
    public $estimated_duration = 0;
    public $price = 0.00;
    public $category = '';
    public $tags = [];
    public $tagInput = '';
    public $requirements = [];
    public $requirementInput = '';
    public $what_you_learn = [];
    public $learnInput = '';
    public $is_featured = false;
    public $is_published = false;
    public $enrollment_type = 'open';
    public $max_students = null;

    protected $rules = [
        'title' => 'required|string|max:255',
        'short_description' => 'required|string|max:500',
        'description' => 'required|string',
        'difficulty_level' => 'required|in:Beginner,Intermediate,Advanced',
        'estimated_duration' => 'required|integer|min:1',
        'price' => 'required|numeric|min:0',
        'category' => 'required|string|max:255',
        'enrollment_type' => 'required|in:open,invite_only,approval_required',
        'max_students' => 'nullable|integer|min:1',
    ];

    public function updatedTitle()
    {
        $this->slug = Str::slug($this->title);
    }

    public function addTag()
    {
        if (!empty(trim($this->tagInput))) {
            $this->tags[] = trim($this->tagInput);
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
        if (!empty(trim($this->requirementInput))) {
            $this->requirements[] = trim($this->requirementInput);
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
        if (!empty(trim($this->learnInput))) {
            $this->what_you_learn[] = trim($this->learnInput);
            $this->learnInput = '';
        }
    }

    public function removeLearningOutcome($index)
    {
        unset($this->what_you_learn[$index]);
        $this->what_you_learn = array_values($this->what_you_learn);
    }

    public function saveDraft()
    {
        $this->validate();
        
        $course = Course::create([
            'title' => $this->title,
            'slug' => $this->uniqueSlug($this->slug ?: Str::slug($this->title)),
            'short_description' => $this->short_description,
            'description' => $this->description,
            'instructor_id' => Auth::id(),
            'difficulty_level' => $this->difficulty_level,
            'estimated_duration' => $this->estimated_duration,
            'price' => $this->price,
            'category' => $this->category,
            'tags' => $this->tags,
            'requirements' => $this->requirements,
            'what_you_learn' => $this->what_you_learn,
            'is_featured' => $this->is_featured,
            'is_published' => false,
            'enrollment_type' => $this->enrollment_type,
            'max_students' => $this->max_students,
            'approval_status' => 'draft',
        ]);

        session()->flash('message', 'Course saved as draft successfully!');
        return $this->redirect(route('courses.edit', $course), navigate: true);
    }

    public function submitForApproval()
    {
        $this->validate();
        
        $course = Course::create([
            'title' => $this->title,
            'slug' => $this->uniqueSlug($this->slug ?: Str::slug($this->title)),
            'short_description' => $this->short_description,
            'description' => $this->description,
            'instructor_id' => Auth::id(),
            'difficulty_level' => $this->difficulty_level,
            'estimated_duration' => $this->estimated_duration,
            'price' => $this->price,
            'category' => $this->category,
            'tags' => $this->tags,
            'requirements' => $this->requirements,
            'what_you_learn' => $this->what_you_learn,
            'is_featured' => $this->is_featured,
            'is_published' => false,
            'enrollment_type' => $this->enrollment_type,
            'max_students' => $this->max_students,
            'approval_status' => 'pending',
            'submitted_for_approval_at' => now(),
        ]);

        // Create content approval record
        \App\Models\ContentApproval::create([
            'approvable_type' => Course::class,
            'approvable_id' => $course->id,
            'status' => 'pending',
            'submitted_by' => Auth::id(),
            'submitted_at' => now(),
            'category' => 'course',
            'priority' => 'normal',
        ]);

        session()->flash('message', 'Course submitted for approval!');
        return $this->redirect(route('courses.show', $course), navigate: true);
    }

    protected function uniqueSlug(string $base): string
    {
        $slug = $base !== '' ? $base : 'course';
        $original = $slug;
        $i = 2;

        while (Course::where('slug', $slug)->exists()) {
            $slug = $original.'-'.$i;
            $i++;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.courses.create');
    }
}
