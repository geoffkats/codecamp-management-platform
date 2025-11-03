<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Models\ContentApproval;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Str;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public Course $course;
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

    public function mount(Course $course)
    {
        // Check authorization
        if ($course->instructor_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $this->course = $course;
        $this->title = $course->title;
        $this->slug = $course->slug;
        $this->short_description = $course->short_description;
        $this->description = $course->description;
        $this->difficulty_level = $course->difficulty_level;
        $this->estimated_duration = $course->estimated_duration;
        $this->price = $course->price;
        $this->category = $course->category;
        $this->tags = $course->tags ?? [];
        $this->requirements = $course->requirements ?? [];
        $this->what_you_learn = $course->what_you_learn ?? [];
        $this->is_featured = $course->is_featured;
        $this->is_published = $course->is_published;
        $this->enrollment_type = $course->enrollment_type ?? 'open';
        $this->max_students = $course->max_students;
    }

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
        
        $this->course->update([
            'title' => $this->title,
            'slug' => $this->slug ?: Str::slug($this->title),
            'short_description' => $this->short_description,
            'description' => $this->description,
            'difficulty_level' => $this->difficulty_level,
            'estimated_duration' => $this->estimated_duration,
            'price' => $this->price,
            'category' => $this->category,
            'tags' => $this->tags,
            'requirements' => $this->requirements,
            'what_you_learn' => $this->what_you_learn,
            'is_featured' => $this->is_featured,
            'approval_status' => 'draft',
            'is_published' => false,
        ]);

        session()->flash('message', 'Course updated successfully!');
        $this->dispatch('course-updated');
    }

    public function submitForApproval()
    {
        $this->validate();
        
        $this->course->update([
            'title' => $this->title,
            'slug' => $this->slug ?: Str::slug($this->title),
            'short_description' => $this->short_description,
            'description' => $this->description,
            'difficulty_level' => $this->difficulty_level,
            'estimated_duration' => $this->estimated_duration,
            'price' => $this->price,
            'category' => $this->category,
            'tags' => $this->tags,
            'requirements' => $this->requirements,
            'what_you_learn' => $this->what_you_learn,
            'is_featured' => $this->is_featured,
            'approval_status' => 'pending',
            'submitted_for_approval_at' => now(),
            'is_published' => false,
            'enrollment_type' => $this->enrollment_type,
            'max_students' => $this->max_students,
        ]);

        // Create or update content approval record
        ContentApproval::updateOrCreate(
            [
                'approvable_type' => Course::class,
                'approvable_id' => $this->course->id,
            ],
            [
                'status' => 'pending',
                'submitted_by' => Auth::id(),
                'submitted_at' => now(),
                'category' => 'course',
                'priority' => 'normal',
            ]
        );

        session()->flash('message', 'Course submitted for approval!');
        return $this->redirect(route('courses.show', $this->course), navigate: true);
    }

    public function deleteCourse()
    {
        if ($this->course->enrollments()->count() > 0) {
            session()->flash('error', 'Cannot delete course with active enrollments.');
            return;
        }

        $this->course->delete();
        session()->flash('message', 'Course deleted successfully!');
        return $this->redirect(route('courses.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.courses.edit');
    }
}
