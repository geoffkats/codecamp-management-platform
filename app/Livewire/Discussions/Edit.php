<?php

namespace App\Livewire\Discussions;

use App\Models\Course;
use App\Models\Discussion;
use App\Models\Lesson;
use App\Support\DiscussionSanitizer;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    use WithFileUploads;

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
    
    // Rich content fields
    public $subjectTag = '';
    public $scratchProjectId = '';
    public $codeLanguage = '';
    public $codeTitle = '';
    public $codeContent = '';
    public $newImages = [];
    public $existingImages = [];

    public function mount(Discussion $discussion)
    {
        abort_unless(Auth::user()->canAccessDiscussions(), 403);

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
        
        // Load rich content
        $this->subjectTag = $discussion->subject_tag ?? '';
        $this->scratchProjectId = $discussion->scratch_project_id ?? '';
        $this->existingImages = $discussion->attachments ?? [];
        
        // Load first code snippet if exists
        if (!empty($discussion->code_snippets) && is_array($discussion->code_snippets)) {
            $firstSnippet = $discussion->code_snippets[0];
            $this->codeLanguage = $firstSnippet['language'] ?? '';
            $this->codeContent = $firstSnippet['code'] ?? '';
            $this->codeTitle = $firstSnippet['title'] ?? '';
        }
    }

    protected function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'content' => ['required', 'string', 'min:10', 'max:10000'],
            'courseId' => ['nullable', 'exists:courses,id'],
            'lessonId' => ['nullable', 'exists:lessons,id'],
            'isPinned' => ['boolean'],
            'isLocked' => ['boolean'],
            'status' => ['required', 'string', 'in:active,closed,archived'],
            'tags' => ['array', 'max:5'],
            'category' => ['required', 'string', 'in:general,question,help,announcement,project,feedback'],
            'subjectTag' => ['nullable', 'string', 'in:scratch,python,web,javascript'],
            'scratchProjectId' => ['nullable', 'string', 'max:50'],
            'codeLanguage' => ['nullable', 'string', 'in:python,javascript,html,css,php,sql'],
            'codeTitle' => ['nullable', 'string', 'max:100'],
            'codeContent' => ['nullable', 'string'],
            'newImages' => ['nullable', 'array', 'max:5'],
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

    public function removeExistingImage($index)
    {
        if (isset($this->existingImages[$index])) {
            unset($this->existingImages[$index]);
            $this->existingImages = array_values($this->existingImages);
        }
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

        // Prepare code snippets array
        $codeSnippets = [];
        if (!empty($this->codeContent) && !empty($this->codeLanguage)) {
            $codeSnippets[] = [
                'language' => $this->codeLanguage,
                'code' => DiscussionSanitizer::codeSnippet($this->codeContent),
                'title' => $this->codeTitle ? DiscussionSanitizer::title($this->codeTitle) : null,
            ];
        }

        // Handle image uploads
        $attachments = $this->existingImages;
        if (!empty($this->newImages)) {
            foreach ($this->newImages as $image) {
                $path = $image->store('discussion-images', 'public');
                $attachments[] = $path;
            }
        }

        // Update discussion
        $updateData = [
            'title' => DiscussionSanitizer::title($validated['title']),
            'content' => DiscussionSanitizer::body($validated['content']),
            'course_id' => $this->courseId,
            'lesson_id' => $this->lessonId,
            'subject_tag' => $this->subjectTag ?: null,
            'scratch_project_id' => $this->scratchProjectId
                ? DiscussionSanitizer::scratchProjectId($this->scratchProjectId) ?: null
                : null,
            'code_snippets' => !empty($codeSnippets) ? $codeSnippets : null,
            'attachments' => !empty($attachments) ? $attachments : null,
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
