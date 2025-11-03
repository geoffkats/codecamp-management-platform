<?php

namespace App\Livewire\Assignments;

use App\Models\Assignment;
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
    public $description = '';
    public $instructions = '';
    public $due_date = '';
    public $max_points = 100;
    public $status = 'draft';

    protected $rules = [
        'course_id' => 'required|exists:courses,id',
        'lesson_id' => 'nullable|exists:lessons,id',
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'instructions' => 'nullable|string',
        'due_date' => 'nullable|date|after:today',
        'max_points' => 'required|integer|min:1|max:1000',
        'status' => 'required|in:draft,active,inactive,archived',
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

    public function save()
    {
        $this->validate();

        Assignment::create([
            'course_id' => $this->course_id,
            'lesson_id' => $this->lesson_id,
            'created_by' => Auth::id(),
            'title' => $this->title,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'due_date' => $this->due_date ? now()->parse($this->due_date) : null,
            'max_points' => $this->max_points,
            'status' => $this->status,
        ]);

        session()->flash('message', 'Assignment created successfully!');

        return $this->redirect(route('assignments.index'), navigate: true);
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

        return view('livewire.assignments.create', [
            'courses' => $courses,
            'lessons' => $lessons,
        ]);
    }
}
