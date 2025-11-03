<?php

namespace App\Livewire\Lessons;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Support\Str;
use Livewire\Component;

class LessonForm extends Component
{
    public $lesson;
    public $course_id;
    public $module_id;
    public $title;
    public $content;
    public $summary;
    public $lesson_type = 'text';
    public $difficulty_level = 'beginner';
    public $duration_minutes;
    public $video_url;
    public $is_published = false;
    public $is_free_preview = false;
    public $is_locked = false;
    public $order_index = 0;

    public function mount($courseId, ?Lesson $lesson = null)
    {
        $this->course_id = $courseId;
        $this->lesson = $lesson;

        if ($lesson) {
            $this->module_id = $lesson->module_id;
            $this->title = $lesson->title;
            $this->content = $lesson->content;
            $this->summary = $lesson->summary;
            $this->lesson_type = $lesson->lesson_type;
            $this->difficulty_level = $lesson->difficulty_level;
            $this->duration_minutes = $lesson->duration_minutes;
            $this->video_url = $lesson->video_url;
            $this->is_published = $lesson->is_published;
            $this->is_free_preview = $lesson->is_free_preview;
            $this->is_locked = $lesson->is_locked;
            $this->order_index = $lesson->order_index;
        }
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'lesson_type' => 'required|in:text,video,interactive,quiz,assignment',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced',
        ]);

        $data = [
            'course_id' => $this->course_id,
            'module_id' => $this->module_id,
            'title' => $this->title,
            'slug' => Str::slug($this->title),
            'content' => $this->content,
            'summary' => $this->summary,
            'lesson_type' => $this->lesson_type,
            'difficulty_level' => $this->difficulty_level,
            'duration_minutes' => $this->duration_minutes,
            'video_url' => $this->video_url,
            'is_published' => $this->is_published,
            'is_free_preview' => $this->is_free_preview,
            'is_locked' => $this->is_locked,
            'order_index' => $this->order_index,
        ];

        if ($this->lesson) {
            $this->lesson->update($data);
            session()->flash('message', 'Lesson updated successfully!');
        } else {
            Lesson::create($data);
            session()->flash('message', 'Lesson created successfully!');
        }

        return redirect()->route('lessons.index', ['course' => $this->course_id]);
    }

    public function render()
    {
        return view('livewire.lessons.lesson-form', [
            'modules' => CourseModule::where('course_id', $this->course_id)->get(),
        ]);
    }
}

