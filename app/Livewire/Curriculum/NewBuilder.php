<?php

namespace App\Livewire\Curriculum;

use App\Livewire\Curriculum\Concerns\ComputesBuilderStatus;
use App\Livewire\Curriculum\Concerns\ComputesBuilderStructure;
use App\Livewire\Curriculum\Concerns\HandlesBuilderArchiving;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\ContentApproval;
use App\Services\Curriculum\ApprovalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class NewBuilder extends Component
{
    use WithFileUploads;
    use ComputesBuilderStructure;
    use ComputesBuilderStatus;
    use HandlesBuilderArchiving;

    public $courseId;
    public $course;
    
    // State machine - single source of truth for UI state
    public $viewState = 'welcome'; // welcome | lesson-form | module-form | assessment-form | build-empty | not-found
    
    // Supporting data for current view state
    public $selectedType = null; // 'module', 'lesson', 'assessment'
    public $selectedId = null;
    public $selectedModuleId = null;
    public $formData = [];
    public $showRejectModal = false;
    public $rejectionReason = '';
    public $lesson = null;
    public $selectedAssessment = null;
    public $lessonId = null;  // For creating assessments
    public $sidebarCollapsed = false; // Toggle sidebar visibility
    public $pdfUpload = null;
    public $slideUpload = null;
    public $restoreWindowDays = 30;
    public $structureTab = 'active'; // active | archived
    public $canManageCourse = false;
    public $isApprover = false;
    
    // Computed property for backward compatibility
    public $showForm = false;
    
    // Cache user permissions to avoid N+1 queries
    protected $isAdmin;
    protected $isSupervisor;
    protected $listeners = [
        'lesson-saved' => 'handleLessonSaved',
        'close-form' => 'closeForm',
        'flash-message' => 'handleFlashMessage',
        'archive-lesson' => 'deleteLesson',
        'archive-module' => 'deleteModule',
        'restore-module' => 'restoreModule',
        'restore-lesson' => 'restoreLesson',
        'structure-reordered' => 'reloadStructure',
    ];
    protected $queryString = [
        'structureTab' => ['except' => 'active'],
    ];

    public function mount($course = null)
    {
        // Cache user roles once
        $user = Auth::user();
        $user->load('roles'); // Eager load roles
        $this->isAdmin = $user->isAdmin();
        $this->isSupervisor = $user->isSupervisor();
        $this->isApprover = $this->isAdmin || $this->isSupervisor;
        $this->restoreWindowDays = (int) config('app.restore_window_days', 30);
        
        if ($course) {
            $this->courseId = is_object($course) ? (int) $course->id : (int) $course;
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
        $this->computeViewState();
    }

    /**
     * Compute view state from current properties
     * This is the single source of truth for determining which view to show
     */
    protected function computeViewState(): void
    {
        // No course selected
        if (!$this->courseId) {
            $this->viewState = 'welcome';
            $this->showForm = false;
            return;
        }

        // Course not found
        if (!$this->course) {
            $this->viewState = 'not-found';
            $this->showForm = false;
            return;
        }

        // Form is being shown
        if ($this->showForm && $this->selectedType) {
            $this->viewState = match($this->selectedType) {
                'lesson' => 'lesson-form',
                'module' => 'module-form',
                'assessment' => 'assessment-form',
                default => 'other-form',
            };
            return;
        }

        // Debug state (shouldn't happen but handle gracefully)
        if ($this->showForm && !$this->selectedType) {
            $this->viewState = 'build-empty';
            $this->showForm = false;
            return;
        }

        // Default: empty state
        $this->viewState = 'build-empty';
        $this->showForm = false;
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
                'slide_file_path' => null,
                'html_content' => null,
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
        $this->isApprover = $this->isAdmin || $this->isSupervisor;
        
        $loadAssessments = false;

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
                  ->select('id', 'module_id', 'title', 'order_index', 'lesson_type', 'approval_status', 'is_locked', 'deleted_at')
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
        $this->computeViewState();
    }

    public function handleLessonSaved($lessonId = null): void
    {
        if ($lessonId) {
            $this->selectedId = $lessonId;
        }

        $this->loadCourse();
        $this->computeViewState();
        $this->dispatch('course-structure-updated');
    }

    public function reloadStructure(): void
    {
        $this->loadCourse();
        $this->computeViewState();
    }

    public function handleFlashMessage(string $type, string $message): void
    {
        if (!in_array($type, ['message', 'error'], true)) {
            $type = 'message';
        }

        session()->flash($type, $message);
    }
    
    public function approveCourse()
    {
        if (!$this->course || (!$this->isAdmin && !$this->isSupervisor)) {
            session()->flash('error', 'You do not have permission to approve courses.');
            return;
        }

        $this->approvalService()->approveCourse($this->course, Auth::id());
        
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

        $this->approvalService()->rejectCourse($this->course, $this->rejectionReason, Auth::id());
        
        session()->flash('message', 'Course rejected.');
        $this->showRejectModal = false;
        $this->rejectionReason = '';
        $this->loadCourse();
    }
    
    #[On('select-item')]
    public function selectItem($type, $id = null, $parentId = null)
    {
        // Simple approach: just update the values directly
        $this->selectedType = $type;
        $this->selectedId = $id;
        $this->selectedAssessment = null;
        $this->lessonId = null;

        // Track the active module so "Add Lesson" button in header knows which module to target
        if ($type === 'module' && $id) {
            $this->selectedModuleId = (int) $id;
        } elseif ($type === 'lesson') {
            $this->selectedModuleId = $parentId ? (int) $parentId : $this->selectedModuleId;
        }

        if ($type === 'assessment') {
            // Store lesson ID for creating new assessments
            if (!$id && $parentId) {
                $this->lessonId = $parentId;
            }
            if ($id) {
                $this->selectedAssessment = Assessment::find($id);
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
                $this->lesson = Lesson::with('assessments')->find($id);
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
                        'slide_file_path' => $this->lesson->slide_file_path ?? null,
                        'html_content' => $this->lesson->html_content ?? null,
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
        
        $this->showForm = true;
        $this->computeViewState();
    }
    
    public function closeForm()
    {
        $this->showForm = false;
        $this->selectedType = null;
        $this->selectedId = null;
        $this->selectedModuleId = null;
        $this->lesson = null;
        $this->selectedAssessment = null;
        $this->lessonId = null;
        $this->pdfUpload = null;
        $this->slideUpload = null;
        $this->initializeFormData();
        $this->computeViewState();
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
            'formData.order_index' => 'nullable|integer|min:0',
            'formData.is_published' => 'boolean',
            'formData.is_active' => 'boolean',
            'formData.is_free_preview' => 'boolean',
            'formData.is_locked' => 'boolean',
            'pdfUpload' => 'nullable|file|mimes:pdf|max:51200',
            'slideUpload' => 'nullable|file|mimes:pdf|max:51200',
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
            'is_published' => $this->formData['is_published'],
            'is_active' => $this->formData['is_active'],
            'is_free_preview' => $this->formData['is_free_preview'],
            'is_locked' => $this->formData['is_locked'],
            'lesson_steps' => $lessonSteps,
            'scratch_project_id' => !empty($this->formData['scratch_project_id']) ? $this->formData['scratch_project_id'] : null,
            'scratch_blocks' => $codeBlocks,
            'html_content' => $this->formData['html_content'] ?: null,
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

        // Handle slide file upload — replaces the previous slide file
        if ($this->slideUpload) {
            if ($this->selectedId) {
                $existing = Lesson::find($this->selectedId);
                if ($existing && $existing->slide_file_path) {
                    Storage::disk('public')->delete($existing->slide_file_path);
                }
            }
            $lessonData['slide_file_path'] = $this->slideUpload->store("lessons/slides/{$this->courseId}", 'public');
        }

        if ($this->selectedId) {
            // Update existing lesson
            $lesson = $existingLesson ?? Lesson::find($this->selectedId);
            $wasApproved = $lesson->approval_status === 'approved';

            $user = Auth::user();
            $isAdminOrSupervisor = $user->isAdmin() || $user->isSupervisor();

            if ($wasApproved && !$isAdminOrSupervisor) {
                // Teacher edited an approved lesson — reset to pending for re-approval
                $lessonData['approval_status'] = 'pending';
                $lessonData['submitted_for_approval_at'] = now();
                $lessonData['approved_at'] = null;
                $lessonData['approved_by'] = null;

                $lesson->update($lessonData);

                ContentApproval::create([
                    'approvable_type' => Lesson::class,
                    'approvable_id' => $lesson->id,
                    'status' => 'pending',
                    'submitted_by' => $user->id,
                    'submitted_at' => now(),
                    'notes' => 'Lesson updated — requires re-approval',
                    'priority' => 'normal',
                    'category' => 'update',
                ]);

                $this->approvalService()->notifyApprovers($lesson, 'updated', $user);
                session()->flash('message', 'Lesson updated and sent back for approval.');
            } else {
                // Admin/supervisor update, or updating a non-approved lesson
                $lesson->update($lessonData);
                session()->flash('message', 'Lesson saved successfully!');
            }
        } else {
            // Create new lesson — auto-assign order position
            $maxOrder = Lesson::where('module_id', $this->formData['module_id'])->max('order_index') ?? 0;
            $lessonData['order_index'] = $maxOrder + 1;

            $user = Auth::user();
            $isAdminOrSupervisor = $user->isAdmin() || $user->isSupervisor();

            if (!$isAdminOrSupervisor) {
                $lessonData['approval_status'] = 'pending';
                $lessonData['submitted_for_approval_at'] = now();
            } else {
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
                $this->approvalService()->notifyApprovers($lesson, 'created', $user);
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

    
    public function saveModule()
    {
        $this->validate([
            'formData.title' => 'required|string|max:255',
            'formData.description' => 'nullable|string',
        ]);

        $moduleData = [
            'course_id' => $this->courseId,
            'title' => $this->formData['title'],
            'description' => $this->formData['description'],
        ];

        if ($this->selectedId) {
            $module = CourseModule::find($this->selectedId);
            $module->update($moduleData);
            session()->flash('message', 'Module updated successfully!');
        } else {
            // Auto-assign position at the end
            $moduleData['order_index'] = (CourseModule::where('course_id', $this->courseId)->max('order_index') ?? 0) + 1;
            CourseModule::create($moduleData);
            session()->flash('message', 'Module created successfully!');
        }

        $this->loadCourse();
        $this->dispatch('course-structure-updated');
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

        if ($lesson->course->instructor_id !== Auth::id() && !$this->isAdmin && !$this->isSupervisor) {
            session()->flash('error', 'You do not have permission to submit this lesson.');
            return;
        }

        $this->approvalService()->submitLessonForApproval($lesson);

        $this->formData['approval_status'] = 'pending';
        session()->flash('message', 'Lesson submitted for approval successfully!');
        $this->loadCourse();
    }
    public function removeSlideFile()
    {
        if (!$this->selectedId) {
            return;
        }

        $lesson = Lesson::find($this->selectedId);
        if ($lesson && $lesson->slide_file_path) {
            Storage::disk('public')->delete($lesson->slide_file_path);
            $lesson->update(['slide_file_path' => null]);
            $this->formData['slide_file_path'] = null;
            session()->flash('message', 'Slide file removed.');
        }
    }

    public function removeAttachment($index)
    {
        if (!isset($this->formData['attachments'][$index])) {
            return;
        }

        $attachment = $this->formData['attachments'][$index];

        if (isset($attachment['path']) && Storage::exists($attachment['path'])) {
            Storage::delete($attachment['path']);
        }

        unset($this->formData['attachments'][$index]);
        $this->formData['attachments'] = array_values($this->formData['attachments']);

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

        $this->approvalService()->approveLesson($lesson, Auth::user());

        $this->formData['approval_status'] = 'approved';

        session()->flash('message', 'Lesson approved successfully!');

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

        if (empty($this->rejectionReason)) {
            session()->flash('error', 'Please provide a reason for disapproval.');
            return;
        }

        $this->approvalService()->disapproveLesson($lesson, Auth::user(), $this->rejectionReason);

        $this->formData['approval_status'] = 'pending';
        $this->formData['rejection_reason'] = $this->rejectionReason;

        $this->showRejectModal = false;
        $this->rejectionReason = '';

        session()->flash('message', 'Lesson sent back for revision. Teacher has been notified to make changes.');

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

        $lesson->is_locked = !$lesson->is_locked;
        $lesson->save();

        $status = $lesson->is_locked ? 'locked' : 'unlocked';
        session()->flash('message', "Lesson {$status} successfully!");

        if ($this->selectedId === $lessonId && isset($this->formData['is_locked'])) {
            $this->formData['is_locked'] = $lesson->is_locked;
        }

        $this->loadCourse();
    }

    public function toggleAssessmentLock($assessmentId)
    {
        $assessment = Assessment::find($assessmentId);

        if (!$assessment) {
            session()->flash('error', 'Assessment not found.');
            return;
        }

        $assessment->is_locked = !$assessment->is_locked;
        $assessment->save();

        $status = $assessment->is_locked ? 'locked' : 'unlocked';
        session()->flash('message', "Quiz/Assessment {$status} successfully!");

        $this->loadCourse();
    }

    public function toggleAssignmentLock($assignmentId)
    {
        $assignment = Assignment::find($assignmentId);

        if (!$assignment) {
            session()->flash('error', 'Assignment not found.');
            return;
        }

        $assignment->is_locked = !$assignment->is_locked;
        $assignment->save();

        $status = $assignment->is_locked ? 'locked' : 'unlocked';
        session()->flash('message', "Assignment {$status} successfully!");

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

        $this->approvalService()->rejectLesson($lesson, Auth::user(), $this->rejectionReason);

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

            if (preg_match('/^(Step\s*\d+:|^\d+[\.\):]|^-\s*)/i', $line) || $currentStep === null) {
                if ($currentStep !== null) {
                    $steps[] = $currentStep;
                }

                $title = preg_replace('/^(Step\s*\d+:|^\d+[\.\):]|^-\s*)/i', '', $line);
                $title = trim($title);

                $currentStep = [
                    'title' => $title ?: $line,
                    'description' => '',
                    'image' => null,
                    'code' => null,
                ];
            } else {
                if ($currentStep !== null) {
                    $currentStep['description'] .= ($currentStep['description'] ? ' ' : '') . $line;
                }
            }
        }

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

            $category = 'operators';

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
        $this->sidebarCollapsed = ! $this->sidebarCollapsed;
        $this->skipRender();
    }

    private function approvalService(): ApprovalService
    {
        return app(ApprovalService::class);
    }
    
    public function render()
    {
        // Ensure course is loaded if courseId is set but course is null
        if ($this->courseId && !$this->course) {
            $this->loadCourse();
        }

        // Full course list is only needed on the welcome / picker screen.
        // Skip this query while editing (Add Quiz, lessons, etc.).
        $courses = collect();
        if (!$this->courseId) {
            $authUser = Auth::user();
            $isAdmin = $authUser->isAdmin();
            $isSupervisor = $authUser->isSupervisor();
            $courses = Course::withCount('modules')
                ->where(function ($query) use ($isAdmin, $isSupervisor) {
                    if (!$isAdmin && !$isSupervisor) {
                        $query->where('instructor_id', Auth::id())
                              ->orWhereHas('collaborators', function ($q) {
                                  $q->where('user_id', Auth::id());
                              });
                    }
                })
                ->where('approval_status', '!=', 'deleted')
                ->orderBy('title')
                ->select('id', 'title', 'instructor_id')
                ->get();
        }

        return view('livewire.curriculum.new-builder', [
            'courses' => $courses,
        ]);
    }
}
