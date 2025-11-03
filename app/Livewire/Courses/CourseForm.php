<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use Illuminate\Support\Str;
use Livewire\Component;

class CourseForm extends Component
{
    public $course;
    public $title;
    public $description;
    public $short_description;
    public $featured_image;
    public $difficulty_level = 'Beginner';
    public $estimated_duration;
    public $is_published = false;
    public $is_featured = false;
    public $price = 0;
    public $category;
    public $tags = [];
    public $requirements = [];
    public $what_you_learn = [];

    public function mount(?Course $course = null)
    {
        $this->course = $course;
        if ($course) {
            $this->title = $course->title;
            $this->description = $course->description;
            $this->short_description = $course->short_description;
            $this->featured_image = $course->featured_image;
            $this->difficulty_level = $course->difficulty_level;
            $this->estimated_duration = $course->estimated_duration;
            $this->is_published = $course->is_published;
            $this->is_featured = $course->is_featured;
            $this->price = $course->price;
            $this->category = $course->category;
            $this->tags = $course->tags ?? [];
            $this->requirements = $course->requirements ?? [];
            $this->what_you_learn = $course->what_you_learn ?? [];
        }
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'difficulty_level' => 'required|in:Beginner,Intermediate,Advanced',
            'price' => 'required|numeric|min:0',
        ]);

        $data = [
            'title' => $this->title,
            'slug' => Str::slug($this->title),
            'description' => $this->description,
            'short_description' => $this->short_description,
            'instructor_id' => auth()->id(),
            'featured_image' => $this->featured_image,
            'difficulty_level' => $this->difficulty_level,
            'estimated_duration' => $this->estimated_duration,
            'is_published' => $this->is_published,
            'is_featured' => $this->is_featured,
            'price' => $this->price,
            'category' => $this->category,
            'tags' => $this->tags,
            'requirements' => $this->requirements,
            'what_you_learn' => $this->what_you_learn,
        ];

        if ($this->course) {
            $this->course->update($data);
            session()->flash('message', 'Course updated successfully!');
        } else {
            Course::create($data);
            session()->flash('message', 'Course created successfully!');
        }

        return redirect()->route('courses.index');
    }

    public function render()
    {
        return view('livewire.courses.course-form');
    }
}

