<?php

namespace App\Livewire\Curriculum;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Notification;
use App\Models\ContentApproval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class NewBuilder extends Component
{
    use WithFileUploads;

    public $courseId;
    public $course;
    public $selectedType = null; // 'module', 'lesson', 'assessment'
    public $selectedId = null;
    public $showForm = false;
    public $formData = [];
    public $showRejectModal = false;
    public $rejectionReason = '';
    public $lesson = null;
    public $lessonId = null;  // For creating assessments
    public $sidebarCollapsed = false; // Toggle sidebar visibility
    public $pdfUpload = null;
    public $restoreWindowDays = 30;
    public $mode = 'build'; // build | manage
    public $structureTab = 'active'; // active | archived
    public $canManageCourse = false;
    
    // Cache user permissions to avoid N+1 queries
    protected $isAdmin;
    protected $isSupervisor;
    protected $queryString = [
        'mode' => ['except' => 'build'],
        'structureTab' => ['except' => 'active'],
    ];

    public function mount($course = null)
    {
        // Cache user roles once
        $user = Auth::user();
        $user->load('roles'); // Eager load roles
        $this->isAdmin = $user->isAdmin();
        $this->isSupervisor = $user->isSupervisor();
        $this->restoreWindowDays = (int) config('app.restore_window_days', 30);
        
        if ($course) {
            $this->courseId = $course;
            $this->loadCourse();
            
            // Auto-select lesson if provided in query string
            $lessonId = request()->query('lesson');
            if ($lessonId) {
                $lesson = Lesson::find($lessonId);
                if ($lesson && $lesson->course_id == $course) {
                    $this->selectItem('lesson', $lessonId);
                }
            }
            
            // Check if we should create a new lesson (for draft restoration)
            $createNew = request()->query('new');
            if ($createNew === 'lesson') {
                $this->selectItem('lesson', null);
            }
        }
        $this->initializeFormData();
    }

    public function setMode(string $mode): void
    {
        if (!in_array($mode, ['build', 'manage'], true)) {
            return;
        }

        $this->mode = $mode;

        if ($mode === 'manage') {
            $this->showForm = false;
            $this->selectedType = null;
            $this->selectedId = null;
        }

        $this->loadCourse();
    }

    public function setStructureTab(string $tab): void
    {
        if (!in_array($tab, ['active', 'archived'], true)) {
            return;
        }

        $this->structureTab = $tab;
    }
    
    public function initializeFormData()
    {
        if ($this->selectedType === 'module') {
            $this->formData = [
                'title' => '',
                'description' => '',
                'order_index' => 1,
            ];
        } else {
            $this->formData = [
                'title' => '',
                'module_id' => '',
                'lesson_type' => 'text',
                'content' => '',
                'summary' => '',
                'objectives' => '',
                'video_url' => '',
                'video_duration' => '',
                'difficulty_level' => 'beginner',
                'duration_minutes' => '',
                'order_index' => 1,
                'is_published' => false,
                'is_active' => true,
                'is_free_preview' => false,
                'is_locked' => false,
                'lesson_steps_text' => '',
                'scratch_project_id' => '',
                'code_examples_text' => '',
            ];
        }
    }
    
    public function loadCourse()
    {
        if (!$this->courseId) {
            return;
        }
        
        // Reload user roles to ensure they're fresh
        $user = Auth::user();
        $this->isAdmin = $user->hasRole('admin');
        $this->isSupervisor = $user->hasRole('supervisor');
        
        $loadAssessments = $this->mode === 'manage';

        // Optimize query with selective eager loading
        $query = Course::withTrashed()->with([
            'modules' => function($q) {
                $q->withTrashed()
                  ->orderBy('order_index')
                  ->select('id', 'course_id', 'title', 'order_index', 'deleted_at');
            },
            'modules.lessons' => function($q) use ($loadAssessments) {
                $q->orderBy('order_index')
                  ->withTrashed()
                  ->select('id', 'module_id', 'title', 'order_index', 'approval_status', 'is_locked', 'deleted_at')
                  ->withCount('assessments');

                if ($loadAssessments) {
                    $q->with(['assessments' => function($q) {
                        $q->select('id', 'lesson_id', 'title', 'is_locked');
                    }]);
                }
            }
        ])->where('id', $this->courseId);
            
        if (!$this->isAdmin && !$this->isSupervisor) {
            // Show courses where user is instructor OR collaborator
            $query->where(function($q) {
                $q->where('instructor_id', Auth::id())
                  ->orWhereHas('collaborators', function($q) {
                      $q->where('user_id', Auth::id());
                  });
            });
        }
        
        $this->course = $query->first();
        $this->canManageCourse = $this->course ? $this->userCanManageCourse() : false;
    }
    
    public function approveCourse()
    {
        if (!$this->course || (!$this->isAdmin && !$this->isSupervisor)) {
            session()->flash('error', 'You do not have permission to approve courses.');
            return;
        }
        
        $this->course->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);
        
        // Notify instructor
        \App\Models\Notification::create([
            'user_id' => $this->course->instructor_id,
            'title' => 'Course Approved',
            'message' => 'Your course "' . $this->course->title . '" has been approved and is now published.',
            'type' => 'success',
        ]);
        
        session()->flash('message', 'Course approved successfully!');
        $this->loadCourse();
    }
    
    public function rejectCourse()
    {
        if (!$this->course || (!$this->isAdmin && !$this->isSupervisor)) {
            session()->flash('error', 'You do not have permission to reject courses.');
            return;
        }
        
        $this->showRejectModal = true;
    }
    
    public function confirmReject()
    {
        if (!$this->rejectionReason) {
            session()->flash('error', 'Please provide a reason for rejection.');
            return;
        }
        
        $this->course->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $this->rejectionReason,
        ]);
        
        // Notify instructor
        \App\Models\Notification::create([
            'user_id' => $this->course->instructor_id,
            'title' => 'Course Rejected',
            'message' => 'Your course "' . $this->course->title . '" has been rejected. Reason: ' . $this->rejectionReason,
            'type' => 'error',
        ]);
        
        session()->flash('message', 'Course rejected.');
        $this->showRejectModal = false;
        $this->rejectionReason = '';
        $this->loadCourse();
    }
    
    public function selectItem($type, $id = null, $parentId = null)
    {
        // Simple approach: just update the values directly
        $this->selectedType = $type;
        $this->selectedId = $id;
        $this->showForm = true;
        
        if ($type === 'assessment') {
            // Store lesson ID for creating new assessments
            if (!$id && $parentId) {
                $this->lessonId = $parentId;
            }
            // Assessment editing is handled by the dedicated assessment builder
        } elseif ($type === 'module') {
            if ($id) {
                // Load existing module
                $module = CourseModule::find($id);
                if ($module) {
                    $this->formData = [
                        'title' => $module->title,
                        'description' => $module->description,
                        'order_index' => $module->order_index,
                    ];
                }
            } else {
                $this->initializeFormData();
            }
        } elseif ($type === 'lesson') {
            if ($id) {
                // Load existing lesson
                $this->lesson = Lesson::find($id);
                if ($this->lesson) {
                    // Convert lesson_steps array to text format for editing
                    $stepsText = '';
                    if ($this->lesson->lesson_steps && is_array($this->lesson->lesson_steps)) {
                        foreach ($this->lesson->lesson_steps as $index => $step) {
                            $stepNum = $index + 1;
                            $title = $step['title'] ?? "Step {$stepNum}";
                            $desc = $step['description'] ?? '';
                            $stepsText .= "{$title}\n{$desc}\n\n";
                        }
                    }
                    
                    // Convert scratch_blocks array to text format for editing
                    $codeExamplesText = '';
                    if ($this->lesson->scratch_blocks && is_array($this->lesson->scratch_blocks)) {
                        foreach ($this->lesson->scratch_blocks as $block) {
                            $text = $block['text'] ?? '';
                            if ($text) {
                                $codeExamplesText .= $text . "\n";
                            }
                        }
                    }
                    
                    $this->formData = [
                        'title' => $this->lesson->title,
                        'module_id' => $this->lesson->module_id,
                        'lesson_type' => $this->lesson->lesson_type,
                        'content' => $this->lesson->content,
                        'summary' => $this->lesson->summary,
                        'objectives' => $this->lesson->objectives,
                        'video_url' => $this->lesson->video_url,
                        'video_duration' => $this->lesson->video_duration,
                        'difficulty_level' => $this->lesson->difficulty_level ?? 'beginner',
                        'duration_minutes' => $this->lesson->duration_minutes,
                        'order_index' => $this->lesson->order_index,
                        'is_published' => $this->lesson->is_published ?? false,
                        'is_active' => $this->lesson->is_active ?? true,
                        'is_free_preview' => $this->lesson->is_free_preview ?? false,
                        'is_locked' => $this->lesson->is_locked ?? false,
                        'approval_status' => $this->lesson->approval_status ?? 'draft',
                        'lesson_steps_text' => trim($stepsText),
                        'scratch_project_id' => $this->lesson->scratch_project_id ?? '',
                        'code_examples_text' => trim($codeExamplesText),
                        'attachments' => $this->lesson->attachments ?? [],
                    ];
                }
            } else {
                $this->lesson = null;
                // New lesson - set module if provided
                $this->initializeFormData();
                if ($parentId) {
                    $this->formData['module_id'] = $parentId;
                }
            }
        }
    }
    
    public function closeForm()
    {
        $this->showForm = false;
        $this->selectedType = null;
        $this->selectedId = null;
        $this->lesson = null;
        $this->lessonId = null;
        $this->pdfUpload = null;
        $this->initializeFormData();
    }
    
    public function saveLesson()
    {
        $rules = [
            'formData.title' => 'required|string|max:255',
            'formData.module_id' => 'required|exists:course_modules,id',
            'formData.lesson_type' => 'required|in:text,video,interactive,quiz',
            'formData.content' => 'nullable|string',
            'formData.summary' => 'nullable|string|max:500',
            'formData.objectives' => 'nullable|string',
            'formData.video_url' => 'required_if:formData.lesson_type,video|nullable|url',
            'formData.video_duration' => 'nullable|integer|min:1',
            'formData.difficulty_level' => 'nullable|in:beginner,intermediate,advanced',
            'formData.duration_minutes' => 'nullable|integer|min:1',
            'formData.order_index' => 'required|integer|min:0',
            'formData.is_published' => 'boolean',
            'formData.is_active' => 'boolean',
            'formData.is_free_preview' => 'boolean',
            'formData.is_locked' => 'boolean',
            'pdfUpload' => 'nullable|file|mimes:pdf|max:51200',
        ];
        
        $this->validate($rules);
        
        // Convert lesson steps text to JSON array
        $lessonSteps = null;
        if (!empty($this->formData['lesson_steps_text']) && $this->formData['lesson_type'] === 'interactive') {
            $lessonSteps = $this->parseStepsText($this->formData['lesson_steps_text']);
        }
        
        // Convert code examples text to JSON array
        $codeBlocks = null;
        if (!empty($this->formData['code_examples_text']) && $this->formData['lesson_type'] === 'interactive') {
            $codeBlocks = $this->parseCodeExamplesText($this->formData['code_examples_text']);
        }
        
        $lessonData = [
            'course_id' => $this->courseId,
            'module_id' => $this->formData['module_id'],
            'title' => $this->formData['title'],
            'lesson_type' => $this->formData['lesson_type'],
            'content' => $this->formData['content'],
            'summary' => $this->formData['summary'],
            'objectives' => $this->formData['objectives'],
            'video_url' => $this->formData['video_url'] ?: null,
            'video_duration' => !empty($this->formData['video_duration']) ? $this->formData['video_duration'] : null,
            'difficulty_level' => $this->formData['difficulty_level'],
            'duration_minutes' => !empty($this->formData['duration_minutes']) ? $this->formData['duration_minutes'] : null,
            'order_index' => $this->formData['order_index'],
            'is_published' => $this->formData['is_published'],
            'is_active' => $this->formData['is_active'],
            'is_free_preview' => $this->formData['is_free_preview'],
            'is_locked' => $this->formData['is_locked'],
            'lesson_steps' => $lessonSteps,
            'scratch_project_id' => !empty($this->formData['scratch_project_id']) ? $this->formData['scratch_project_id'] : null,
            'scratch_blocks' => $codeBlocks,
        ];

        // Preserve and append PDF attachments
        $attachments = [];
        if ($this->selectedId) {
            $existingLesson = Lesson::find($this->selectedId);
            $existingAttachments = $existingLesson->attachments ?? [];
            if (is_array($existingAttachments)) {
                $attachments = $existingAttachments;
            }
        }

        if ($this->pdfUpload) {
            $storedPath = $this->pdfUpload->store("lessons/{$this->courseId}", 'public');
            $attachments[] = [
                'type' => 'pdf',
                'name' => $this->pdfUpload->getClientOriginalName(),
                'path' => $storedPath,
            ];
        }

        if (!empty($attachments)) {
            $lessonData['attachments'] = $attachments;
        }
        
        if ($this->selectedId) {
            // Update existing lesson
            $lesson = $existingLesson ?? Lesson::find($this->selectedId);
            $wasApproved = $lesson->approval_status === 'approved';
            
            // If lesson was previously approved and user is not admin/supervisor, reset to pending
            $user = Auth::user();
            $isAdminOrSupervisor = $user->isAdmin() || $user->isSupervisor();
            
            if ($wasApproved && !$isAdminOrSupervisor) {
                $lessonData['approval_status'] = 'pending';
                $lessonData['submitted_for_approval_at'] = now();
                $lessonData['approved_at'] = null;
                $lessonData['approved_by'] = null;
                
                $lesson->update($lessonData);
                
                // Create ContentApproval record for re-approval
                ContentApproval::create([
                    'approvable_type' => Lesson::class,
                    'approvable_id' => $lesson->id,
                    'status' => 'pending',
                    'submitted_by' => $user->id,
                    'submitted_at' => now(),
                    'notes' => 'Lesson updated - requires re-approval',
                    'priority' => 'normal',
                    'category' => 'update',
                ]);
                
                // Notify admins and supervisors
                $this->notifyApprovers($lesson, 'updated');
                
                session()->flash('message', 'Lesson updated and sent for re-approval!');
            } else {
                session()->flash('message', 'Lesson updated successfully!');
                $lesson->update($lessonData);
            }
        } else {
            // Create new lesson - set to pending approval
            $user = Auth::user();
            $isAdminOrSupervisor = $user->isAdmin() || $user->isSupervisor();
            
            if (!$isAdminOrSupervisor) {
                $lessonData['approval_status'] = 'pending';
                $lessonData['submitted_for_approval_at'] = now();
            } else {
                // Admins/supervisors can auto-approve their own lessons
                $lessonData['approval_status'] = 'approved';
                $lessonData['approved_at'] = now();
                $lessonData['approved_by'] = $user->id;
            }
            
            $lesson = Lesson::create($lessonData);
            
            if (!$isAdminOrSupervisor) {
                // Create ContentApproval record
                ContentApproval::create([
                    'approvable_type' => Lesson::class,
                    'approvable_id' => $lesson->id,
                    'status' => 'pending',
                    'submitted_by' => $user->id,
                    'submitted_at' => now(),
                    'notes' => 'New lesson submitted for approval',
                    'priority' => 'normal',
                    'category' => 'new',
                ]);
                
                // Notify admins and supervisors
                $this->notifyApprovers($lesson, 'created');
                session()->flash('message', 'Lesson created and sent for approval!');
            } else {
                session()->flash('message', 'Lesson created successfully!');
            }
        }
        
        // Dispatch event to clear autosave draft
        $this->dispatch('lesson-saved');
        
        // Reload the course to show updated data
        $this->loadCourse();
        
        // Ensure course is loaded before closing form
        if (!$this->course) {
            session()->flash('error', 'Error reloading course. Please refresh the page.');
            return;
        }
        
        $this->closeForm();
    }

    public function deleteLesson($lessonId)
    {
        $lesson = Lesson::where('course_id', $this->courseId)->find($lessonId);

        if (!$lesson) {
            session()->flash('error', 'Lesson not found.');
            return;
        }

        if (!$this->userCanManageCourse()) {
            session()->flash('error', 'You do not have permission to remove this lesson.');
            return;
        }

        DB::transaction(function () use ($lesson) {
            $this->archiveLessonWithChildren($lesson);
        });

        session()->flash('message', 'Lesson removed. You can restore it within the restore window.');
        $this->loadCourse();
    }

    public function restoreLesson($lessonId)
    {
        $lesson = Lesson::withTrashed()->where('course_id', $this->courseId)->find($lessonId);

        if (!$lesson || !$lesson->trashed()) {
            session()->flash('error', 'This lesson is not in the recycle window.');
            return;
        }

        if (!$this->userCanManageCourse()) {
            session()->flash('error', 'You do not have permission to restore this lesson.');
            return;
        }

        try {
            DB::transaction(function () use ($lesson) {
                $this->restoreLessonWithChildren($lesson);
            });

            session()->flash('message', 'Lesson restored.');
            $this->loadCourse();
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteAssessment($assessmentId)
    {
        $assessment = Assessment::with('lesson')->find($assessmentId);

        if (!$assessment || !$assessment->lesson || $assessment->lesson->course_id !== $this->courseId) {
            session()->flash('error', 'Quiz not found for this course.');
            return;
        }

        if (!$this->userCanManageCourse()) {
            session()->flash('error', 'You do not have permission to remove this quiz.');
            return;
        }

        $assessment->delete();

        session()->flash('message', 'Quiz archived. You can restore it within the restore window.');
        $this->loadCourse();
    }

    public function deleteCourse()
    {
        if (!$this->course) {
            session()->flash('error', 'Course not found.');
            return;
        }

        if (!$this->userCanManageCourse(true)) {
            session()->flash('error', 'You do not have permission to delete this course.');
            return;
        }

        DB::transaction(function () {
            $this->archiveCourseWithChildren($this->course);
        });

        session()->flash('message', 'Course archived. You can restore it within the restore window.');
        $this->loadCourse();
    }

    public function restoreCourse()
    {
        $course = Course::withTrashed()->find($this->courseId);

        if (!$course || !$course->trashed()) {
            session()->flash('error', 'Course is not archived.');
            return;
        }

        if (!$this->userCanManageCourse(true)) {
            session()->flash('error', 'You do not have permission to restore this course.');
            return;
        }

        try {
            DB::transaction(function () use ($course) {
                $this->restoreCourseWithChildren($course);
            });

            $this->course = $course;
            session()->flash('message', 'Course restored.');
            $this->loadCourse();
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteModule($moduleId)
    {
        $module = CourseModule::withTrashed()->with('lessons')->find($moduleId);

        if (!$module || $module->course_id !== $this->courseId) {
            session()->flash('error', 'Module not found for this course.');
            return;
        }

        if (!$this->userCanManageCourse()) {
            session()->flash('error', 'You do not have permission to remove this module.');
            return;
        }

        DB::transaction(function () use ($module) {
            $this->archiveModuleWithChildren($module);
        });

        session()->flash('message', 'Module archived. You can restore it within the restore window.');
        $this->loadCourse();
    }

    public function restoreModule($moduleId)
    {
        $module = CourseModule::withTrashed()->find($moduleId);

        if (!$module || $module->course_id !== $this->courseId || !$module->trashed()) {
            session()->flash('error', 'Module is not archived.');
            return;
        }

        if (!$this->userCanManageCourse()) {
            session()->flash('error', 'You do not have permission to restore this module.');
            return;
        }

        try {
            DB::transaction(function () use ($module) {
                $this->restoreModuleWithChildren($module);
            });

            session()->flash('message', 'Module restored.');
            $this->loadCourse();
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function forceDeleteModule($moduleId)
    {
        $module = CourseModule::withTrashed()
            ->where('course_id', $this->courseId)
            ->find($moduleId);

        if (!$module) {
            session()->flash('error', 'Module not found for this course.');
            return;
        }

        if (!$this->userCanManageCourse()) {
            session()->flash('error', 'You do not have permission to remove this module.');
            return;
        }

        $lessonCount = Lesson::withTrashed()->where('module_id', $module->id)->count();

        if ($lessonCount > 0) {
            session()->flash('error', 'Module has lessons. Archive it instead.');
            return;
        }

        DB::transaction(function () use ($module) {
            $module->forceDelete();
        });

        session()->flash('message', 'Module permanently deleted.');
        $this->loadCourse();
    }

    private function userCanManageCourse(bool $requireOwner = false): bool
    {
        $user = Auth::user();

        // Refresh role flags in case session role cache changed mid-request
        $this->isAdmin = method_exists($user, 'isAdmin') ? $user->isAdmin() : ($this->isAdmin ?? false);
        $this->isSupervisor = method_exists($user, 'isSupervisor') ? $user->isSupervisor() : ($this->isSupervisor ?? false);

        $hasEditPermission = method_exists($user, 'hasPermission')
            ? ($user->hasPermission('edit_courses') || $user->hasPermission('review_content'))
            : false;

        if ($this->isAdmin || $this->isSupervisor || $hasEditPermission) {
            return true;
        }

        if (!$this->course) {
            return false;
        }

        if ($requireOwner) {
            return $this->course->instructor_id === $user->id;
        }

        return $this->course->canUserEdit($user);
    }

    private function archiveCourseWithChildren(Course $course): void
    {
        $course->modules()->withTrashed()->each(function ($module) {
            $this->archiveModuleWithChildren($module);
        });

        $course->delete();
    }

    private function restoreCourseWithChildren(Course $course): void
    {
        if ($course->deleted_at && $course->deleted_at->addDays($this->restoreWindowDays)->isPast()) {
            throw new \RuntimeException('Restore period has expired for this course.');
        }

        $course->modules()->withTrashed()->get()->each(function ($module) {
            $this->restoreModuleWithChildren($module, false);
        });

        $course->restore();
    }

    private function archiveModuleWithChildren(CourseModule $module): void
    {
        $module->lessons()->withTrashed()->each(function ($lesson) {
            $this->archiveLessonWithChildren($lesson);
        });

        $module->delete();
    }

    private function restoreModuleWithChildren(CourseModule $module, bool $enforceWindow = true): void
    {
        if ($enforceWindow && $module->deleted_at && $module->deleted_at->addDays($this->restoreWindowDays)->isPast()) {
            throw new \RuntimeException('Restore period has expired for this module.');
        }

        $module->lessons()->withTrashed()->get()->each(function ($lesson) use ($enforceWindow) {
            $this->restoreLessonWithChildren($lesson, $enforceWindow);
        });

        $module->restore();
    }

    private function archiveLessonWithChildren(Lesson $lesson): void
    {
        Assessment::where('lesson_id', $lesson->id)->withTrashed()->get()->each(function ($assessment) {
            $assessment->delete();
        });

        Assignment::where('lesson_id', $lesson->id)->withTrashed()->get()->each(function ($assignment) {
            $assignment->delete();
        });

        $lesson->delete();
    }

    private function restoreLessonWithChildren(Lesson $lesson, bool $enforceWindow = true): void
    {
        if ($enforceWindow && $lesson->deleted_at && $lesson->deleted_at->addDays($this->restoreWindowDays)->isPast()) {
            throw new \RuntimeException('Restore period has expired for this lesson.');
        }

        Assessment::withTrashed()->where('lesson_id', $lesson->id)->get()->each(function ($assessment) use ($enforceWindow) {
            if (!$enforceWindow || !$assessment->deleted_at || !$assessment->deleted_at->addDays($this->restoreWindowDays)->isPast()) {
                $assessment->restore();
            }
        });

        Assignment::withTrashed()->where('lesson_id', $lesson->id)->get()->each(function ($assignment) use ($enforceWindow) {
            if (!$enforceWindow || !$assignment->deleted_at || !$assignment->deleted_at->addDays($this->restoreWindowDays)->isPast()) {
                $assignment->restore();
            }
        });

        $lesson->restore();
    }
    
    public function saveModule()
    {
        $rules = [
            'formData.title' => 'required|string|max:255',
            'formData.description' => 'nullable|string',
            'formData.order_index' => 'required|integer|min:0',
        ];
        
        $this->validate($rules);
        
        $moduleData = [
            'course_id' => $this->courseId,
            'title' => $this->formData['title'],
            'description' => $this->formData['description'],
            'order_index' => $this->formData['order_index'],
        ];
        
        if ($this->selectedId) {
            // Update existing module
            $module = CourseModule::find($this->selectedId);
            $module->update($moduleData);
            session()->flash('message', 'Module updated successfully!');
        } else {
            // Create new module
            CourseModule::create($moduleData);
            session()->flash('message', 'Module created successfully!');
        }
        
        $this->loadCourse();
        $this->closeForm();
    }
    
    public function submitForApproval()
    {
        if (!$this->selectedId) {
            session()->flash('error', 'Please save the lesson first before submitting for approval.');
            return;
        }
        
        $lesson = Lesson::find($this->selectedId);
        
        if (!$lesson) {
            session()->flash('error', 'Lesson not found.');
            return;
        }
        
        // Check if user owns this lesson
        if ($lesson->course->instructor_id !== Auth::id() && !$this->isAdmin && !$this->isSupervisor) {
            session()->flash('error', 'You do not have permission to submit this lesson.');
            return;
        }
        
        $lesson->update([
            'approval_status' => 'pending',
            'submitted_for_approval_at' => now(),
        ]);
        
        $this->formData['approval_status'] = 'pending';
        session()->flash('message', 'Lesson submitted for approval successfully!');
        $this->loadCourse();
    }
    
    public function removeAttachment($index)
    {
        if (!isset($this->formData['attachments'][$index])) {
            return;
        }
        
        $attachment = $this->formData['attachments'][$index];
        
        // Delete the file from storage
        if (isset($attachment['path']) && Storage::exists($attachment['path'])) {
            Storage::delete($attachment['path']);
        }
        
        // Remove from array
        unset($this->formData['attachments'][$index]);
        $this->formData['attachments'] = array_values($this->formData['attachments']); // Reindex
        
        // Update lesson in database if editing existing lesson
        if ($this->selectedId) {
            $lesson = Lesson::find($this->selectedId);
            if ($lesson) {
                $lesson->update(['attachments' => $this->formData['attachments']]);
            }
        }
        
        session()->flash('message', 'Attachment removed successfully!');
    }
    
    public function approveLesson()
    {
        if (!$this->selectedId) {
            session()->flash('error', 'No lesson selected.');
            return;
        }
        
        $lesson = Lesson::find($this->selectedId);
        
        if (!$lesson) {
            session()->flash('error', 'Lesson not found.');
            return;
        }
        
        $lesson->approval_status = 'approved';
        $lesson->approved_at = now();
        $lesson->approved_by = Auth::id();
        $lesson->rejection_reason = null;
        $lesson->save();
        
        $this->formData['approval_status'] = 'approved';
        
        // Update ContentApproval record
        $contentApproval = ContentApproval::where('approvable_type', Lesson::class)
            ->where('approvable_id', $lesson->id)
            ->where('status', 'pending')
            ->latest()
            ->first();
        
        if ($contentApproval) {
            $contentApproval->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        }
        
        // Notify the teacher
        $teacher = $lesson->course->instructor;
        if ($teacher) {
            Notification::create([
                'user_id' => $teacher->id,
                'title' => 'Lesson Approved',
                'message' => "Your lesson \"{$lesson->title}\" has been approved by " . Auth::user()->name,
                'type' => 'lesson_approved',
                'data' => [
                    'lesson_id' => $lesson->id,
                    'lesson_title' => $lesson->title,
                    'course_id' => $lesson->course_id,
                    'approved_by' => Auth::user()->name,
                    'url' => route('curriculum.builder', ['course' => $lesson->course_id]),
                ],
            ]);
        }
        
        session()->flash('message', 'Lesson approved successfully!');
        
        // Reload the course
        $this->course = Course::with(['modules.lessons.assessments'])
            ->find($this->courseId);
    }
    
    public function disapproveLesson()
    {
        if (!$this->selectedId) {
            session()->flash('error', 'No lesson selected.');
            return;
        }
        
        $lesson = Lesson::find($this->selectedId);
        
        if (!$lesson) {
            session()->flash('error', 'Lesson not found.');
            return;
        }
        
        // Validate rejection reason
        if (empty($this->rejectionReason)) {
            session()->flash('error', 'Please provide a reason for disapproval.');
            return;
        }
        
        // Set back to pending so teacher can revise and it stays in approval queue
        $lesson->approval_status = 'pending';
        $lesson->rejection_reason = $this->rejectionReason;
        $lesson->approved_at = null;
        $lesson->approved_by = null;
        $lesson->submitted_for_approval_at = now(); // Update submission time
        $lesson->save();
        
        $this->formData['approval_status'] = 'pending';
        $this->formData['rejection_reason'] = $this->rejectionReason;
        
        // Update or create ContentApproval record
        $contentApproval = ContentApproval::where('approvable_type', Lesson::class)
            ->where('approvable_id', $lesson->id)
            ->latest()
            ->first();
        
        if ($contentApproval) {
            // Update existing record - keep as pending with rejection notes
            $contentApproval->update([
                'status' => 'pending',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'rejection_reason' => $this->rejectionReason,
                'notes' => 'Needs revision: ' . $this->rejectionReason,
            ]);
        } else {
            // Create new ContentApproval record if none exists
            ContentApproval::create([
                'approvable_type' => Lesson::class,
                'approvable_id' => $lesson->id,
                'status' => 'pending',
                'submitted_by' => $lesson->course->instructor_id,
                'submitted_at' => now(),
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'rejection_reason' => $this->rejectionReason,
                'notes' => 'Needs revision: ' . $this->rejectionReason,
                'priority' => 'high',
                'category' => 'revision',
            ]);
        }
        
        // Notify the teacher
        $teacher = $lesson->course->instructor;
        if ($teacher) {
            Notification::create([
                'user_id' => $teacher->id,
                'title' => 'Lesson Needs Revision',
                'message' => "Your lesson \"{$lesson->title}\" needs revision. Reason: {$this->rejectionReason}",
                'type' => 'lesson_rejected',
                'data' => [
                    'lesson_id' => $lesson->id,
                    'lesson_title' => $lesson->title,
                    'course_id' => $lesson->course_id,
                    'rejected_by' => Auth::user()->name,
                    'rejection_reason' => $this->rejectionReason,
                    'url' => route('curriculum.builder', ['course' => $lesson->course_id]),
                ],
            ]);
        }
        
        $this->showRejectModal = false;
        $this->rejectionReason = '';
        
        session()->flash('message', 'Lesson sent back for revision. Teacher has been notified to make changes.');
        
        // Reload the course
        $this->course = Course::with(['modules.lessons.assessments'])
            ->find($this->courseId);
    }
    
    public function openRejectModal()
    {
        $this->showRejectModal = true;
        $this->rejectionReason = '';
    }
    
    public function closeRejectModal()
    {
        $this->showRejectModal = false;
        $this->rejectionReason = '';
    }
    
    public function toggleLessonLock($lessonId)
    {
        $lesson = Lesson::find($lessonId);
        
        if (!$lesson) {
            session()->flash('error', 'Lesson not found.');
            return;
        }
        
        // Toggle the lock status
        $lesson->is_locked = !$lesson->is_locked;
        $lesson->save();
        
        $status = $lesson->is_locked ? 'locked' : 'unlocked';
        session()->flash('message', "Lesson {$status} successfully!");
        
        // Update form data if this is the selected lesson
        if ($this->selectedId === $lessonId && isset($this->formData['is_locked'])) {
            $this->formData['is_locked'] = $lesson->is_locked;
        }
        
        // Reload the course
        $this->loadCourse();
    }
    
    public function toggleAssessmentLock($assessmentId)
    {
        $assessment = Assessment::find($assessmentId);
        
        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }
        
        // Toggle the lock status
        $assessment->is_locked = !$assessment->is_locked;
        $assessment->save();
        
        $status = $assessment->is_locked ? 'locked' : 'unlocked';
        session()->flash('message', "Quiz/Assessment {$status} successfully!");
        
        // Reload the course
        $this->loadCourse();
    }
    
    public function toggleAssignmentLock($assignmentId)
    {
        $assignment = \App\Models\Assignment::find($assignmentId);
        
        if (!$assignment) {
            session()->flash('error', 'Assignment not found.');
            return;
        }
        
        // Toggle the lock status
        $assignment->is_locked = !$assignment->is_locked;
        $assignment->save();
        
        $status = $assignment->is_locked ? 'locked' : 'unlocked';
        session()->flash('message', "Assignment {$status} successfully!");
        
        // Reload the course
        $this->loadCourse();
    }
    
    public function rejectLesson()
    {
        if (!$this->isAdmin && !$this->isSupervisor) {
            session()->flash('error', 'You do not have permission to reject lessons.');
            return;
        }
        
        $this->validate([
            'rejectionReason' => 'required|string|min:10',
        ]);
        
        if (!$this->selectedId) {
            session()->flash('error', 'No lesson selected.');
            return;
        }
        
        $lesson = Lesson::find($this->selectedId);
        
        if (!$lesson) {
            session()->flash('error', 'Lesson not found.');
            return;
        }
        
        $lesson->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $this->rejectionReason,
            'approved_at' => null,
            'approved_by' => null,
        ]);
        
        $this->formData['approval_status'] = 'rejected';
        $this->showRejectModal = false;
        $this->rejectionReason = '';
        session()->flash('message', 'Lesson rejected. Teacher has been notified.');
        $this->loadCourse();
    }
    
    private function parseStepsText($text)
    {
        if (empty($text)) {
            return null;
        }
        
        $lines = explode("\n", $text);
        $steps = [];
        $currentStep = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            // Check if this line starts a new step (starts with "Step", number, or is first line)
            if (preg_match('/^(Step\s*\d+:|^\d+[\.\):]|^-\s*)/i', $line) || $currentStep === null) {
                // Save previous step if exists
                if ($currentStep !== null) {
                    $steps[] = $currentStep;
                }
                
                // Clean up the step title
                $title = preg_replace('/^(Step\s*\d+:|^\d+[\.\):]|^-\s*)/i', '', $line);
                $title = trim($title);
                
                // Start new step
                $currentStep = [
                    'title' => $title ?: $line,
                    'description' => '',
                    'image' => null,
                    'code' => null,
                ];
            } else {
                // Add to description of current step
                if ($currentStep !== null) {
                    $currentStep['description'] .= ($currentStep['description'] ? ' ' : '') . $line;
                }
            }
        }
        
        // Add the last step
        if ($currentStep !== null) {
            $steps[] = $currentStep;
        }
        
        return !empty($steps) ? $steps : null;
    }
    
    private function parseCodeExamplesText($text)
    {
        if (empty($text)) {
            return null;
        }
        
        $lines = explode("\n", $text);
        $blocks = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            // Auto-detect category based on content
            $category = 'operators'; // default
            
            if (preg_match('/^(move|turn|go to|glide|point|change|set x|set y)/i', $line)) {
                $category = 'motion';
            } elseif (preg_match('/^(say|think|show|hide|switch costume|next costume|change size)/i', $line)) {
                $category = 'looks';
            } elseif (preg_match('/^(play sound|stop all sounds|change volume)/i', $line)) {
                $category = 'sound';
            } elseif (preg_match('/^(when|broadcast)/i', $line)) {
                $category = 'events';
            } elseif (preg_match('/^(wait|repeat|forever|if|else)/i', $line)) {
                $category = 'control';
            } elseif (preg_match('/^(touching|key pressed|mouse|distance)/i', $line)) {
                $category = 'sensing';
            } elseif (preg_match('/^(set|change)\s+\[.*\]\s+(to|by)/i', $line)) {
                $category = 'variables';
            }
            
            $blocks[] = [
                'category' => $category,
                'text' => $line,
            ];
        }
        
        return !empty($blocks) ? $blocks : null;
    }
    
    public function toggleSidebar()
    {
        $this->sidebarCollapsed = !$this->sidebarCollapsed;
    }
    
    protected function notifyApprovers($lesson, $action = 'created')
    {
        // Get all admins and supervisors
        $approvers = \App\Models\User::whereHas('roles', function($q) {
            $q->whereIn('name', ['admin', 'supervisor']);
        })->get();
        
        $teacher = Auth::user();
        $actionText = $action === 'updated' ? 'updated and needs re-approval' : 'created and needs approval';
        
        foreach ($approvers as $approver) {
            Notification::create([
                'user_id' => $approver->id,
                'title' => 'Lesson Approval Required',
                'message' => "{$teacher->name} has {$actionText}: \"{$lesson->title}\"",
                'type' => 'lesson_approval',
                'data' => [
                    'lesson_id' => $lesson->id,
                    'lesson_title' => $lesson->title,
                    'course_id' => $lesson->course_id,
                    'teacher_id' => $teacher->id,
                    'teacher_name' => $teacher->name,
                    'action' => $action,
                    'url' => route('curriculum.builder', ['course' => $lesson->course_id]),
                ],
            ]);
        }
    }
    
    public function render()
    {
        // Ensure course is loaded if courseId is set but course is null
        if ($this->courseId && !$this->course) {
            $this->loadCourse();
        }
        
        // Only load module count for course list (not full modules)
        $courses = Course::withCount('modules')
            ->where(function($query) {
                if (!$this->isAdmin && !$this->isSupervisor) {
                    // Show courses where user is instructor OR collaborator
                    $query->where('instructor_id', Auth::id())
                          ->orWhereHas('collaborators', function($q) {
                              $q->where('user_id', Auth::id());
                          });
                }
            })
            ->where('approval_status', '!=', 'deleted')
            ->orderBy('title')
            ->select('id', 'title', 'instructor_id')
            ->get();
            
        return view('livewire.curriculum.new-builder', [
            'courses' => $courses,
        ]);
    }
}
