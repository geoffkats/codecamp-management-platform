<?php

namespace App\Livewire\Curriculum;

use App\Models\Assessment;
use App\Models\ContentApproval;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Builder extends Component
{
    use WithFileUploads;
    
    public $courseId;
    public $course;
    public $modules = [];
    public $lessons = [];
    public $assessments = [];
    public $selectedItem = null;
    public $selectedType = null; // 'course', 'module', 'lesson', 'assessment'
    public $showModal = false;
    public $modalType = 'create'; // 'create', 'edit'
    public $formData = [];
    public $savedAssessmentId = null;
    
    // File upload for lesson attachments
    public $attachmentFiles = [];
    public $existingAttachments = []; // For displaying already uploaded attachments

    public function mount($course = null)
    {
        // If course parameter is provided, load that course
        if ($course) {
            $courseModel = is_object($course) ? $course : Course::find($course);
            
            if ($courseModel) {
                // Check authorization
                $user = Auth::user();

                $hasGlobalEdit = $user->hasPermission('edit_courses') || $user->isAdmin();

                if ($user->isTeacher() && !$hasGlobalEdit && $courseModel->instructor_id !== $user->id) {
                    abort(403, 'You can only edit your own courses.');
                }

                if (!$user->isAdmin() && !$user->isTeacher() && !$user->isSupervisor()) {
                    abort(403, 'Only teachers, supervisors and admins can access the curriculum builder.');
                }
                
                $this->courseId = $courseModel->id;
                $this->loadCourse();
            }
        }
    }

    public function loadCourse()
    {
        if (!$this->courseId) {
            return;
        }
        
        // Load course with modules, lessons, and assessments
        $query = Course::with(['modules.lessons.assessments'])
            ->where('id', $this->courseId);
            
        // Restrict teachers without global edit permission to their own courses
        $user = Auth::user();
        $hasGlobalEdit = $user->hasPermission('edit_courses') || $user->isAdmin();

        if (!$user->isAdmin() && !$user->isSupervisor() && !$hasGlobalEdit) {
            $query->where('instructor_id', $user->id);
        }
        
        $this->course = $query->first();
        
        if (!$this->course) {
            session()->flash('error', 'Course not found.');
            return;
        }

            $this->modules = $this->course->modules->map(function ($module) {
                return [
                    'id' => $module->id,
                    'title' => $module->title,
                    'description' => $module->description,
                    'order_index' => $module->order_index,
                    'lessons' => $module->lessons->map(function ($lesson) {
                        return [
                            'id' => $lesson->id,
                            'title' => $lesson->title,
                            'type' => $lesson->lesson_type,
                            'order_index' => $lesson->order_index,
                            'assessments' => $lesson->assessments->map(function ($assessment) {
                                return [
                                    'id' => $assessment->id,
                                    'title' => $assessment->title,
                                    'type' => $assessment->assessment_type,
                                ];
                            })->toArray(),
                        ];
                    })->toArray(),
                ];
            })->toArray();
    }

    public function selectItem($type, $id = null)
    {
        $this->selectedType = $type;
        $this->selectedItem = $id;

        if ($id && $type !== 'course') {
            $this->editItem($type, $id);
        } else {
            $this->createItem($type);
        }
    }

    public function createItem($type)
    {
        $this->modalType = 'create';
        $this->selectedType = $type;
        $this->formData = $this->getDefaultFormData($type);
        $this->attachmentFiles = [];
        $this->existingAttachments = [];
        $this->showModal = true;
    }

    public function editItem($type, $id)
    {
        $this->modalType = 'edit';
        $this->selectedType = $type;
        $this->selectedItem = $id;

        switch ($type) {
            case 'module':
                $item = CourseModule::find($id);
                $this->formData = [
                    'title' => $item->title,
                    'description' => $item->description,
                    'overview' => $item->overview,
                    'order_index' => $item->order_index,
                ];
                break;
            case 'lesson':
                $item = Lesson::find($id);
                $this->formData = [
                    'title' => $item->title,
                    'content' => $item->content,
                    'summary' => $item->summary,
                    'lesson_type' => $item->lesson_type,
                    'module_id' => $item->module_id,
                    'order_index' => $item->order_index,
                    'duration_minutes' => $item->duration_minutes,
                    'video_url' => $item->video_url,
                    'video_duration' => $item->video_duration,
                    'objectives' => $item->objectives,
                    'implementation_guidance' => $item->implementation_guidance,
                    'difficulty_level' => $item->difficulty_level ?? 'beginner',
                    'is_published' => $item->is_published ?? false,
                    'is_free_preview' => $item->is_free_preview ?? false,
                    'is_locked' => $item->is_locked ?? false,
                    'is_active' => $item->is_active ?? true,
                    'has_levels' => $item->has_levels ?? false,
                    'total_levels' => $item->total_levels ?? 0,
                    'question_of_day' => $item->question_of_day,
                    'approval_status' => $item->approval_status ?? 'draft',
                ];
                // Load existing attachments
                $this->existingAttachments = $item->attachments ?? [];
                break;
            case 'assessment':
                $item = Assessment::find($id);
                $this->formData = [
                    'title' => $item->title,
                    'assessment_type' => $item->assessment_type,
                    'lesson_id' => $item->lesson_id,
                    'max_attempts' => $item->max_attempts,
                    'passing_score' => $item->passing_score,
                ];
                break;
        }

        $this->showModal = true;
    }

    public function saveItem()
    {
        $this->validate($this->getValidationRules());

        $shouldRedirect = false;
        $redirectRoute = null;
        
        switch ($this->selectedType) {
            case 'module':
                $this->saveModule();
                break;
            case 'lesson':
                $lesson = $this->saveLesson();
                // Ensure selectedItem is set for newly created lessons
                if ($this->modalType === 'create' && $lesson) {
                    $this->selectedItem = $lesson->id;
                }
                break;
            case 'assessment':
                $this->saveAssessment();
                // After saving assessment, redirect to edit page to add questions
                if ($this->savedAssessmentId) {
                    $shouldRedirect = true;
                    $redirectRoute = route('assessments.edit', $this->savedAssessmentId);
                }
                break;
        }

        $this->showModal = false;
        $this->loadCourse();
        $this->dispatch('item-saved');
        
        // Redirect to assessment edit page if it was an assessment
        if ($shouldRedirect && $redirectRoute) {
            return $this->redirect($redirectRoute, navigate: true);
        }
    }

    private function saveModule()
    {
        $data = array_merge($this->formData, [
            'course_id' => $this->courseId,
        ]);

        if ($this->modalType === 'create') {
            CourseModule::create($data);
        } else {
            CourseModule::where('id', $this->selectedItem)->update($data);
        }
    }

    private function saveLesson()
    {
        $data = array_merge($this->formData, [
            'course_id' => $this->courseId,
        ]);

        // Ensure boolean fields are properly cast
        $booleanFields = ['is_published', 'is_free_preview', 'is_locked', 'is_active', 'has_levels'];
        foreach ($booleanFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = filter_var($data[$field], FILTER_VALIDATE_BOOLEAN);
            }
        }

        // Handle approval status based on user role
        // Admins can set status directly, teachers save as draft
        $existingLesson = null;
        if ($this->modalType === 'edit') {
            $existingLesson = Lesson::find($this->selectedItem);
        }
        
        if (!Auth::user()->isAdmin()) {
            // Teachers: Don't allow changing approval_status from form, keep existing or set to draft
            if ($this->modalType === 'create') {
                $data['approval_status'] = 'draft';
            } else {
                // When editing, preserve current status unless it's a new submission
                if ($existingLesson && in_array($existingLesson->approval_status, ['pending', 'approved'])) {
                    // Don't change status if already pending or approved
                    unset($data['approval_status']);
                } elseif (!isset($data['approval_status'])) {
                    $data['approval_status'] = 'draft';
                }
            }
            // Prevent publishing if not approved
            if (isset($data['is_published']) && $data['is_published']) {
                $currentStatus = $existingLesson->approval_status ?? ($data['approval_status'] ?? 'draft');
                if ($currentStatus !== 'approved') {
                    $data['is_published'] = false;
                }
            }
        }
        // Admins can set approval_status directly through the form

        // Process file uploads and merge with existing attachments
        $attachments = $this->existingAttachments ?? [];
        
        // Upload new files
        if (!empty($this->attachmentFiles)) {
            $this->validate([
                'attachmentFiles.*' => 'file|max:10240', // 10MB max per file
            ], [
                'attachmentFiles.*.max' => 'Each file must not exceed 10MB.',
            ]);
            
            foreach ($this->attachmentFiles as $file) {
                if ($file) {
                    $path = $file->store('lessons/attachments', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                        'uploaded_at' => now()->toIso8601String(),
                    ];
                }
            }
        }
        
        $data['attachments'] = $attachments;

        $lesson = null;
        if ($this->modalType === 'create') {
            $lesson = Lesson::create($data);
        } else {
            Lesson::where('id', $this->selectedItem)->update($data);
            $lesson = Lesson::find($this->selectedItem);
        }
        
        // Reset file uploads after saving
        $this->attachmentFiles = [];
        
        return $lesson;
    }
    
    public function submitLessonForApproval()
    {
        // Only allow submission if lesson exists and user has permission
        if ($this->selectedType !== 'lesson' || !$this->selectedItem) {
            session()->flash('error', 'Please save the lesson first before submitting for approval.');
            return;
        }
        
        $lesson = Lesson::find($this->selectedItem);
        if (!$lesson) {
            session()->flash('error', 'Lesson not found.');
            return;
        }
        
        // Check if already approved or pending
        if ($lesson->approval_status === 'approved') {
            session()->flash('info', 'This lesson is already approved.');
            return;
        }
        
        if ($lesson->approval_status === 'pending') {
            session()->flash('info', 'This lesson is already pending approval.');
            return;
        }
        
        // Update lesson status
        $lesson->update([
            'approval_status' => 'pending',
            'submitted_for_approval_at' => now(),
            'is_published' => false, // Don't allow publishing until approved
        ]);
        
        // Create or update content approval record
        ContentApproval::updateOrCreate(
            [
                'approvable_type' => Lesson::class,
                'approvable_id' => $lesson->id,
            ],
            [
                'status' => 'pending',
                'submitted_by' => Auth::id(),
                'submitted_at' => now(),
                'category' => 'lesson',
                'priority' => 'normal',
            ]
        );
        
        // Update form data to reflect new status
        $this->formData['approval_status'] = 'pending';
        
        session()->flash('message', 'Lesson submitted for approval successfully! It will be reviewed by a supervisor or admin.');
        $this->loadCourse();
    }
    
    public function removeAttachment($index)
    {
        if (isset($this->existingAttachments[$index])) {
            $attachment = $this->existingAttachments[$index];
            
            // Delete file from storage
            if (isset($attachment['path']) && Storage::disk('public')->exists($attachment['path'])) {
                Storage::disk('public')->delete($attachment['path']);
            }
            
            // Remove from array
            unset($this->existingAttachments[$index]);
            $this->existingAttachments = array_values($this->existingAttachments); // Re-index array
        }
    }
    
    public function removeNewAttachment($index)
    {
        if (isset($this->attachmentFiles[$index])) {
            unset($this->attachmentFiles[$index]);
            $this->attachmentFiles = array_values($this->attachmentFiles); // Re-index array
        }
    }

    private function saveAssessment()
    {
        $data = array_merge($this->formData, [
            'course_id' => $this->course->id,
        ]);

        $assessment = null;
        if ($this->modalType === 'create') {
            $assessment = Assessment::create($data);
        } else {
            Assessment::where('id', $this->selectedItem)->update($data);
            $assessment = Assessment::find($this->selectedItem);
        }
        
        // Store assessment ID for redirect
        if ($assessment) {
            $this->savedAssessmentId = $assessment->id;
        }
    }

    public function deleteItem($type, $id)
    {
        // Check permissions - teachers need specific delete permissions, admins can delete anything
        $canDelete = Auth::user()->isAdmin();
        
        if (!$canDelete) {
            switch ($type) {
                case 'module':
                    $canDelete = Auth::user()->hasPermission('delete_courses');
                    break;
                case 'lesson':
                    $canDelete = Auth::user()->hasPermission('delete_lessons');
                    break;
                case 'assessment':
                    $canDelete = Auth::user()->hasPermission('delete_courses'); // Assessments use course permission
                    break;
            }
        }
        
        if (!$canDelete) {
            session()->flash('error', 'You do not have permission to delete this item.');
            return;
        }

        switch ($type) {
            case 'module':
                CourseModule::find($id)->delete();
                break;
            case 'lesson':
                Lesson::find($id)->delete();
                break;
            case 'assessment':
                Assessment::find($id)->delete();
                break;
        }

        $this->loadCourse();
        $this->dispatch('item-deleted');
        session()->flash('message', ucfirst($type) . ' deleted successfully.');
    }

    public function reorderItems($type, $items)
    {
        foreach ($items as $index => $item) {
            switch ($type) {
                case 'module':
                    CourseModule::where('id', $item['id'])->update(['order_index' => $index + 1]);
                    break;
                case 'lesson':
                    Lesson::where('id', $item['id'])->update(['order_index' => $index + 1]);
                    break;
            }
        }

        $this->loadCourse();
    }

    private function getDefaultFormData($type): array
    {
        return match($type) {
            'module' => [
                'title' => '',
                'description' => '',
                'overview' => '',
                'order_index' => count($this->modules) + 1,
            ],
            'lesson' => [
                'title' => '',
                'content' => '',
                'summary' => '',
                'lesson_type' => 'text',
                'module_id' => null,
                'order_index' => 1,
                'duration_minutes' => 30,
                'video_url' => '',
                'video_duration' => null,
                'objectives' => '',
                'implementation_guidance' => '',
                'difficulty_level' => 'beginner',
                'is_published' => false,
                'is_free_preview' => false,
                'is_locked' => false,
                'is_active' => true,
                'has_levels' => false,
                'total_levels' => 0,
                'question_of_day' => null,
                'approval_status' => 'draft',
            ],
            'assessment' => [
                'title' => '',
                'assessment_type' => 'quiz',
                'lesson_id' => null,
                'max_attempts' => 1,
                'passing_score' => 70,
            ],
            default => [],
        };
    }

    private function getValidationRules(): array
    {
        return match($this->selectedType) {
            'module' => [
                'formData.title' => 'required|string|max:255',
                'formData.description' => 'nullable|string',
                'formData.order_index' => 'required|integer',
            ],
            'lesson' => [
                'formData.title' => 'required|string|max:255',
                'formData.content' => 'nullable|string',
                'formData.summary' => 'nullable|string|max:500',
                'formData.module_id' => 'required|exists:course_modules,id',
                'formData.lesson_type' => 'required|in:text,video,interactive,quiz',
                'formData.order_index' => 'required|integer',
                'formData.duration_minutes' => 'nullable|integer|min:1',
                'formData.video_url' => 'required_if:formData.lesson_type,video|nullable|url',
                'formData.video_duration' => 'nullable|integer|min:1',
                'formData.objectives' => 'nullable|string',
                'formData.implementation_guidance' => 'nullable|string',
                'formData.difficulty_level' => 'nullable|in:beginner,intermediate,advanced',
                'formData.is_published' => 'nullable|boolean',
                'formData.is_free_preview' => 'nullable|boolean',
                'formData.is_locked' => 'nullable|boolean',
                'formData.is_active' => 'nullable|boolean',
                'formData.has_levels' => 'nullable|boolean',
                'formData.total_levels' => 'nullable|integer|min:0',
                'formData.question_of_day' => 'nullable|string',
                'formData.approval_status' => 'nullable|in:draft,pending,approved,rejected',
            ],
            'assessment' => [
                'formData.title' => 'required|string|max:255',
                'formData.assessment_type' => 'required|in:quiz,assignment,survey,rubric,peer_review,self_assessment',
                'formData.lesson_id' => 'required|exists:lessons,id',
                'formData.max_attempts' => 'nullable|integer|min:1',
                'formData.passing_score' => 'nullable|integer|min:0|max:100',
            ],
            default => [],
        };
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->formData = [];
        $this->selectedItem = null;
        $this->selectedType = null;
        $this->attachmentFiles = [];
        $this->existingAttachments = [];
    }

    public function render()
    {
        // Admins and Supervisors can see all courses, teachers only their own
        if (Auth::user()->isAdmin() || Auth::user()->isSupervisor()) {
            $courses = Course::with(['modules.lessons'])
                ->where('approval_status', '!=', 'deleted')
                ->orderBy('title')
                ->get();
        } else {
            $courses = Course::with(['modules.lessons'])
                ->where('instructor_id', Auth::id())
                ->where('approval_status', '!=', 'deleted')
                ->orderBy('title')
                ->get();
        }

        return view('livewire.curriculum.builder', [
            'courses' => $courses,
        ]);
    }
}
