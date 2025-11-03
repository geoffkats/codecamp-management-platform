<?php

namespace App\Livewire\Discussions;

use App\Models\Course;
use App\Models\Discussion;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public Discussion $discussion;
    public $title = '';
    public $content = '';
    public $courseId = null;
    public $lessonId = null;
    public $isPinned = false;
    public $isLocked = false;
    public $status = 'active';
    public $tags = [];
    public $tagInput = '';
    public $category = 'general';

    public function mount(Discussion $discussion)
    {
        // Check permissions - only creator or staff can edit
        if ($discussion->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['admin', 'teacher', 'supervisor'])) {
            session()->flash('error', 'You do not have permission to edit this discussion.');
            return $this->redirect(route('discussions.show', $discussion), navigate: true);
        }

        $this->discussion = $discussion;
        $this->title = $discussion->title;
        $this->content = $discussion->content;
        $this->courseId = $discussion->course_id;
        $this->lessonId = $discussion->lesson_id;
        $this->isPinned = $discussion->is_pinned;
        $this->isLocked = $discussion->is_locked;
        $this->status = $discussion->status;
    }

    protected function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'courseId' => ['nullable', 'exists:courses,id'],
            'lessonId' => ['nullable', 'exists:lessons,id'],
            'isPinned' => ['boolean'],
            'isLocked' => ['boolean'],
            'status' => ['required', 'string', 'in:active,closed,archived'],
            'tags' => ['array', 'max:5'],
            'category' => ['required', 'string', 'in:general,question,help,announcement,project,feedback'],
        ];

        // Only staff can modify pin, lock, and status
        $isStaff = Auth::user()->hasAnyRole(['admin', 'teacher', 'supervisor']);
        if (!$isStaff) {
            unset($rules['isPinned'], $rules['isLocked'], $rules['status']);
        }

        return $rules;
    }

    public function updatedCourseId()
    {
        $this->lessonId = null; // Reset lesson when course changes
        $this->resetValidation('lessonId');
    }

    public function addTag()
    {
        if (!empty($this->tagInput) && count($this->tags) < 5) {
            $tag = trim($this->tagInput);
            if (!in_array($tag, $this->tags)) {
                $this->tags[] = $tag;
                $this->tagInput = '';
            }
        }
    }

    public function removeTag($index)
    {
        unset($this->tags[$index]);
        $this->tags = array_values($this->tags);
    }

    public function update()
    {
        $validated = $this->validate();

        $user = Auth::user();
        $isStaff = $user->hasAnyRole(['admin', 'teacher', 'supervisor']);

        // Validate course access
        if ($this->courseId) {
            $course = Course::find($this->courseId);
            if (!$course) {
                session()->flash('error', 'Selected course not found.');
                return;
            }

            // Students can edit discussions in:
            // 1. Courses they're enrolled in
            // 2. Open courses (enrollment_type = 'open')
            if (!$isStaff && $this->discussion->user_id === $user->id) {
                $isEnrolled = $course->enrollments()->where('user_id', $user->id)->exists();
                $isOpen = $course->enrollment_type === 'open';
                
                if (!$isEnrolled && !$isOpen) {
                    session()->flash('error', 'You must be enrolled in this course to edit discussions.');
                    return;
                }
            }
        } elseif (!$isStaff && $this->discussion->course_id) {
            // Original discussion had a course, student can't remove it
            session()->flash('error', 'Students cannot change course association.');
            return;
        }

        // Update discussion
        $updateData = [
            'title' => $validated['title'],
            'content' => $validated['content'],
            'course_id' => $this->courseId,
            'lesson_id' => $this->lessonId,
        ];

        // Only staff can modify these fields
        if ($isStaff) {
            $updateData['is_pinned'] = $this->isPinned;
            $updateData['is_locked'] = $this->isLocked;
            $updateData['status'] = $this->status;
        }

        $this->discussion->update($updateData);

        session()->flash('message', 'Discussion updated successfully!');
        return $this->redirect(route('discussions.show', $this->discussion), navigate: true);
    }

    public function delete()
    {
        $user = Auth::user();
        $isStaff = $user->hasAnyRole(['admin', 'teacher', 'supervisor']);

        // Only creator or staff can delete
        if ($this->discussion->user_id !== $user->id && !$isStaff) {
            session()->flash('error', 'You do not have permission to delete this discussion.');
            return;
        }

        $this->discussion->delete();

        session()->flash('message', 'Discussion deleted successfully!');
        return $this->redirect(route('discussions.index'), navigate: true);
    }

    public function render()
    {
        $user = Auth::user();
        $isStaff = $user->hasAnyRole(['admin', 'teacher', 'supervisor']);

        // Staff can see all courses
        // Students can see:
        // 1. Courses they're enrolled in
        // 2. Open courses (enrollment_type = 'open')
        $courses = $isStaff
            ? Course::all()
            : Course::where(function ($q) use ($user) {
                $q->whereHas('enrollments', fn($q) => $q->where('user_id', $user->id))
                  ->orWhere('enrollment_type', 'open');
            })->get();

        $lessons = $this->courseId
            ? Lesson::where('course_id', $this->courseId)->get()
            : collect();

        return view('livewire.discussions.edit', [
            'courses' => $courses,
            'lessons' => $lessons,
            'isStaff' => $isStaff,
        ]);
    }
}
