<?php

namespace App\Livewire\Lessons;

use App\Models\Course;
use App\Models\Lesson;
use Livewire\Component;
use Livewire\WithFileUploads;

class LessonCreate extends Component
{
    use WithFileUploads;

    public $course_id;
    public $module_id = null;
    public $title = '';
    public $content = '';
    public $summary = '';
    public $lesson_type = 'text';
    public $difficulty_level = 'beginner';
    public $duration_minutes = '';
    public $video_url = '';
    public $objectives = '';
    public $is_free_preview = false;
    public $is_locked = false;

    protected $rules = [
        'course_id' => 'required|exists:courses,id',
        'module_id' => 'nullable|exists:course_modules,id',
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'summary' => 'nullable|string',
        'lesson_type' => 'required|in:text,video,interactive,quiz,assignment',
        'difficulty_level' => 'required|in:beginner,intermediate,advanced',
        'duration_minutes' => 'nullable|integer|min:1',
        'video_url' => 'nullable|url',
        'objectives' => 'nullable|string',
        'is_free_preview' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function mount($courseId)
    {
        $this->course_id = $courseId;
    }

    public function save()
    {
        $this->validate();

        $lesson = Lesson::create([
            'course_id' => $this->course_id,
            'module_id' => $this->module_id,
            'title' => $this->title,
            'content' => $this->content,
            'summary' => $this->summary,
            'lesson_type' => $this->lesson_type,
            'difficulty_level' => $this->difficulty_level,
            'duration_minutes' => $this->duration_minutes,
            'video_url' => $this->video_url,
            'objectives' => $this->objectives,
            'is_free_preview' => $this->is_free_preview,
            'is_locked' => $this->is_locked,
            'approval_status' => 'draft',
        ]);

        session()->flash('message', 'Lesson created successfully!');

        return redirect()->route('lessons.show', $lesson->id);
    }

    public function render()
    {
        $course = Course::with('modules')->findOrFail($this->course_id);
        return view('livewire.lessons.lesson-create', [
            'course' => $course,
        ]);
    }
}

