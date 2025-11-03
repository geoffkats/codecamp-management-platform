<?php

namespace App\Livewire\Discussions;

use App\Models\Course;
use App\Models\Discussion;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public $title = '';
    public $content = '';
    public $courseId = null;
    public $lessonId = null;
    public $isPinned = false;
    public $tags = [];
    public $tagInput = '';
    public $category = 'general';
    public $attachments = [];
    public $allowReplies = true;
    public $notifySubscribers = true;

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'courseId' => ['nullable', 'exists:courses,id'],
            'lessonId' => ['nullable', 'exists:lessons,id'],
            'isPinned' => ['boolean'],
            'tags' => ['array', 'max:5'],
            'category' => ['required', 'string', 'in:general,question,help,announcement,project,feedback'],
            'allowReplies' => ['boolean'],
            'notifySubscribers' => ['boolean'],
        ];
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

    public function save()
    {
        $validated = $this->validate();

        // Check access - staff (admin/teacher/supervisor) can create conversations without course
        $user = Auth::user();
        $isStaff = $user->hasAnyRole(['admin', 'teacher', 'supervisor']);
        
        if ($this->courseId) {
            $course = Course::find($this->courseId);
            if (!$course) {
                session()->flash('error', 'Selected course not found.');
                return;
            }
            
            // Students can create discussions in:
            // 1. Courses they're enrolled in
            // 2. Open courses (enrollment_type = 'open') - no enrollment required
            if (!$isStaff) {
                $isEnrolled = $course
                    ->enrollments()
                    ->where('user_id', $user->id)
                    ->exists();

                $isOpen = $course->enrollment_type === 'open';

                if (!$isEnrolled && !$isOpen) {
                    session()->flash('error', 'You must be enrolled in this course to create discussions.');
                    return;
                }
            }
        }
        // Note: Students can now create discussions without a course (general discussions)

        // Validate lesson belongs to course if both are selected
        if ($this->courseId && $this->lessonId) {
            $lesson = Lesson::where('id', $this->lessonId)
                ->where('course_id', $this->courseId)
                ->first();
            
            if (!$lesson) {
                session()->flash('error', 'Selected lesson does not belong to the selected course.');
                return;
            }
        }

        $discussion = Discussion::create([
            'user_id' => $user->id,
            'course_id' => $this->courseId, // Nullable for staff conversations
            'lesson_id' => $this->lessonId,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_pinned' => $this->isPinned && $isStaff,
            'is_locked' => false,
            'status' => 'active',
        ]);

        // Note: Category and tags are stored but not yet used in the database schema
        // Future enhancement: add metadata column or separate table for tags

        // Award XP for creating discussion
        try {
            $user = Auth::user();
            if (!$user->points) {
                \App\Models\UserPoint::create([
                    'user_id' => $user->id,
                    'total_points' => 0,
                    'level' => 1,
                ]);
            }
            $user->points->increment('total_points', 10); // Small XP for participation
        } catch (\Exception $e) {
            // XP awarding failed, but discussion was created, so continue
        }

        session()->flash('message', 'Discussion created successfully!');
        return $this->redirect(route('discussions.show', $discussion), navigate: true);
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

        return view('livewire.discussions.create', [
            'courses' => $courses,
            'lessons' => $lessons,
        ]);
    }
}
