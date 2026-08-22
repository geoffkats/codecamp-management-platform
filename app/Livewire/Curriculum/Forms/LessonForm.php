<?php

namespace App\Livewire\Curriculum\Forms;

use App\Livewire\Curriculum\NewBuilder;
use App\Models\ContentApproval;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class LessonForm extends Component
{
    use WithFileUploads;

    public $courseId;
    public $lessonId = null;
    public $moduleId = null;
    public $selectedId = null;
    protected $course;
    protected $lesson;
    public $formData = [];
    public $pdfUpload = null;
    public $slideUpload = null;
    public $showRejectModal = false;
    public $rejectionReason = '';
    public $canManageCourse = false;
    public $isApprover = false;

    protected $isAdmin = false;
    protected $isSupervisor = false;

    public function mount($courseId, $lessonId = null, $moduleId = null)
    {
        $this->courseId = $courseId;
        $this->lessonId = $lessonId;
        $this->selectedId = $lessonId;
        $this->moduleId = $moduleId;

        $this->cacheUserRoles();
        $this->loadCourse();
        $this->initializeFormData();
    }

    public function hydrate(): void
    {
        $this->cacheUserRoles();
        $this->loadCourse();
    }

    protected function cacheUserRoles(): void
    {
        $user = Auth::user();
        $this->isAdmin = $user->isAdmin();
        $this->isSupervisor = $user->isSupervisor();
        $this->isApprover = $this->isAdmin || $this->isSupervisor;
    }

    protected function loadCourse(): void
    {
        $this->course = Course::with('modules')->find($this->courseId);

        if ($this->selectedId) {
            $this->lesson = Lesson::with('assessments')->find($this->selectedId);
        }

        $this->canManageCourse = $this->course ? $this->userCanManageCourse() : false;
    }

    protected function userCanManageCourse(): bool
    {
        $user = Auth::user();

        $hasEditPermission = method_exists($user, 'hasPermission')
            ? ($user->hasPermission('edit_courses') || $user->hasPermission('review_content'))
            : false;

        if ($this->isAdmin || $this->isSupervisor || $hasEditPermission) {
            return true;
        }

        if (!$this->course) {
            return false;
        }

        return method_exists($this->course, 'canUserEdit')
            ? $this->course->canUserEdit($user)
            : ($this->course->instructor_id === $user->id);
    }

    public function getLessonStatusColorsProperty(): array
    {
        return [
            'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        ];
    }

    public function getLessonApprovalStatusProperty(): string
    {
        return $this->formData['approval_status'] ?? 'draft';
    }

    public function getLessonRequiresReapprovalProperty(): bool
    {
        return (bool) $this->selectedId
            && $this->lessonApprovalStatus === 'approved'
            && !$this->isApprover;
    }

    public function getSelectedLessonAssessmentsProperty()
    {
        if (!$this->lesson) {
            return collect();
        }

        return $this->lesson->assessments ?? collect();
    }

    protected function initializeFormData(): void
    {
        if ($this->lesson) {
            $this->formData = $this->buildFormDataFromLesson($this->lesson);
            return;
        }

        $this->formData = [
            'title' => '',
            'module_id' => $this->moduleId,
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
            'approval_status' => 'draft',
            'attachments' => [],
            'slide_file_path' => null,
            'html_content' => null,
        ];
    }

    protected function buildFormDataFromLesson(Lesson $lesson): array
    {
        $stepsText = '';
        if ($lesson->lesson_steps && is_array($lesson->lesson_steps)) {
            foreach ($lesson->lesson_steps as $index => $step) {
                $stepNum = $index + 1;
                $title = $step['title'] ?? "Step {$stepNum}";
                $desc = $step['description'] ?? '';
                $stepsText .= "{$title}\n{$desc}\n\n";
            }
        }

        $codeExamplesText = '';
        if ($lesson->scratch_blocks && is_array($lesson->scratch_blocks)) {
            foreach ($lesson->scratch_blocks as $block) {
                $text = $block['text'] ?? '';
                if ($text) {
                    $codeExamplesText .= $text . "\n";
                }
            }
        }

        return [
            'title' => $lesson->title,
            'module_id' => $lesson->module_id,
            'lesson_type' => $lesson->lesson_type,
            'content' => $lesson->content,
            'summary' => $lesson->summary,
            'objectives' => $lesson->objectives,
            'video_url' => $lesson->video_url,
            'video_duration' => $lesson->video_duration,
            'difficulty_level' => $lesson->difficulty_level ?? 'beginner',
            'duration_minutes' => $lesson->duration_minutes,
            'order_index' => $lesson->order_index,
            'is_published' => $lesson->is_published ?? false,
            'is_active' => $lesson->is_active ?? true,
            'is_free_preview' => $lesson->is_free_preview ?? false,
            'is_locked' => $lesson->is_locked ?? false,
            'approval_status' => $lesson->approval_status ?? 'draft',
            'lesson_steps_text' => trim($stepsText),
            'scratch_project_id' => $lesson->scratch_project_id ?? '',
            'code_examples_text' => trim($codeExamplesText),
            'attachments' => $lesson->attachments ?? [],
            'slide_file_path' => $lesson->slide_file_path ?? null,
            'html_content' => $lesson->html_content ?? null,
        ];
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
            'formData.is_published' => 'boolean',
            'formData.is_active' => 'boolean',
            'formData.is_free_preview' => 'boolean',
            'formData.is_locked' => 'boolean',
            'pdfUpload' => 'nullable|file|mimes:pdf|max:51200',
            'slideUpload' => 'nullable|file|mimes:pdf|max:51200',
        ];

        $this->validate($rules);

        $lessonSteps = null;
        if (!empty($this->formData['lesson_steps_text']) && $this->formData['lesson_type'] === 'interactive') {
            $lessonSteps = $this->parseStepsText($this->formData['lesson_steps_text']);
        }

        $codeBlocks = null;
        if (!empty($this->formData['code_examples_text']) && $this->formData['lesson_type'] === 'interactive') {
            $codeBlocks = $this->parseCodeExamplesText($this->formData['code_examples_text']);
        }

        // Auto-calculate order_index for new lessons; preserve existing for edits
        $orderIndex = $this->selectedId
            ? ($this->formData['order_index'] ?? 1)
            : (Lesson::where('module_id', $this->formData['module_id'])->max('order_index') ?? 0) + 1;

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
            'order_index' => $orderIndex,
            'is_published' => $this->formData['is_published'],
            'is_active' => $this->formData['is_active'],
            'is_free_preview' => $this->formData['is_free_preview'],
            'is_locked' => $this->formData['is_locked'],
            'lesson_steps' => $lessonSteps,
            'scratch_project_id' => !empty($this->formData['scratch_project_id']) ? $this->formData['scratch_project_id'] : null,
            'scratch_blocks' => $codeBlocks,
            'html_content' => $this->formData['html_content'] ?: null,
        ];

        $attachments = [];
        if ($this->selectedId) {
            $existingLesson = Lesson::find($this->selectedId);
            $existingAttachments = $existingLesson?->attachments ?? [];
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

        // Slide file upload — replaces any previous slide file
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
            $lesson = Lesson::find($this->selectedId);
            if (!$lesson) {
                $this->flashMessage('error', 'Lesson not found.');
                return;
            }

            $wasApproved = $lesson->approval_status === 'approved';
            $user = Auth::user();
            $isAdminOrSupervisor = $user->isAdmin() || $user->isSupervisor();

            if ($wasApproved && !$isAdminOrSupervisor) {
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
                    'notes' => 'Lesson updated - requires re-approval',
                    'priority' => 'normal',
                    'category' => 'update',
                ]);

                $this->notifyApprovers($lesson, 'updated');
                $this->flashMessage('message', 'Lesson updated and sent for re-approval!');
            } else {
                $lesson->update($lessonData);
                $this->flashMessage('message', 'Lesson updated successfully!');
            }
        } else {
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

                $this->notifyApprovers($lesson, 'created');
                $this->flashMessage('message', 'Lesson created and sent for approval!');
            } else {
                $this->flashMessage('message', 'Lesson created successfully!');
            }
        }

        $this->lesson = $lesson;
        $this->selectedId = $lesson->id;
        $this->lessonId = $lesson->id;
        $this->formData['approval_status'] = $lesson->approval_status;

        $this->dispatch('lesson-saved', $lesson->id);
        $this->dispatch('close-form');
    }

    public function submitForApproval()
    {
        if (!$this->selectedId) {
            $this->flashMessage('error', 'Please save the lesson first before submitting for approval.');
            return;
        }

        $lesson = Lesson::find($this->selectedId);
        if (!$lesson) {
            $this->flashMessage('error', 'Lesson not found.');
            return;
        }

        $user = Auth::user();
        if ($lesson->course->instructor_id !== Auth::id() && !$user->isAdmin() && !$user->isSupervisor()) {
            $this->flashMessage('error', 'You do not have permission to submit this lesson.');
            return;
        }

        $lesson->update([
            'approval_status' => 'pending',
            'submitted_for_approval_at' => now(),
        ]);

        $this->formData['approval_status'] = 'pending';
        $this->flashMessage('message', 'Lesson submitted for approval successfully!');
        $this->loadCourse();
    }

    public function approveLesson()
    {
        if (!$this->selectedId) {
            $this->flashMessage('error', 'No lesson selected.');
            return;
        }

        $lesson = Lesson::find($this->selectedId);
        if (!$lesson) {
            $this->flashMessage('error', 'Lesson not found.');
            return;
        }

        $lesson->approval_status = 'approved';
        $lesson->approved_at = now();
        $lesson->approved_by = Auth::id();
        $lesson->rejection_reason = null;
        $lesson->save();

        $this->formData['approval_status'] = 'approved';

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

        $this->flashMessage('message', 'Lesson approved successfully!');
        $this->loadCourse();
    }

    public function disapproveLesson()
    {
        if (!$this->selectedId) {
            $this->flashMessage('error', 'No lesson selected.');
            return;
        }

        $lesson = Lesson::find($this->selectedId);
        if (!$lesson) {
            $this->flashMessage('error', 'Lesson not found.');
            return;
        }

        if (empty($this->rejectionReason)) {
            $this->flashMessage('error', 'Please provide a reason for disapproval.');
            return;
        }

        $lesson->approval_status = 'pending';
        $lesson->rejection_reason = $this->rejectionReason;
        $lesson->approved_at = null;
        $lesson->approved_by = null;
        $lesson->submitted_for_approval_at = now();
        $lesson->save();

        $this->formData['approval_status'] = 'pending';
        $this->formData['rejection_reason'] = $this->rejectionReason;

        $contentApproval = ContentApproval::where('approvable_type', Lesson::class)
            ->where('approvable_id', $lesson->id)
            ->latest()
            ->first();

        if ($contentApproval) {
            $contentApproval->update([
                'status' => 'pending',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'rejection_reason' => $this->rejectionReason,
                'notes' => 'Needs revision: ' . $this->rejectionReason,
            ]);
        } else {
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

        $this->flashMessage('message', 'Lesson sent back for revision. Teacher has been notified to make changes.');
        $this->loadCourse();
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

        $this->flashMessage('message', 'Attachment removed successfully!');
    }

    public function removeSlideFile(): void
    {
        if (!$this->selectedId) {
            return;
        }

        $lesson = Lesson::find($this->selectedId);
        if ($lesson && $lesson->slide_file_path) {
            Storage::disk('public')->delete($lesson->slide_file_path);
            $lesson->update(['slide_file_path' => null]);
            $this->formData['slide_file_path'] = null;
            $this->flashMessage('message', 'Slide file removed.');
        }
    }

    private function flashMessage(string $type, string $message): void
    {
        if (!in_array($type, ['message', 'error'], true)) {
            $type = 'message';
        }

        session()->flash($type, $message);
        $this->dispatch('flash-message', type: $type, message: $message)
            ->to(\App\Livewire\Curriculum\NewBuilder::class);
    }

    protected function notifyApprovers(Lesson $lesson, string $action): void
    {
        $approvers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'supervisor']);
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

    public function deleteLesson(int $id): void
    {
        if (!$this->canManageCourse) {
            $this->flashMessage('error', 'Cannot archive this lesson.');
            return;
        }

        $lesson = Lesson::where('course_id', $this->courseId)->find($id);
        if (!$lesson) {
            $this->flashMessage('error', 'Cannot archive this lesson.');
            return;
        }

        // Parent NewBuilder owns the cascade archive (lesson + quizzes/assessments/assignments).
        $this->dispatch('archive-lesson', lessonId: $id)->to(NewBuilder::class);
    }

    public function deleteAssessment(int $id): void
    {
        if (!$this->canManageCourse) {
            return;
        }

        \App\Models\Assessment::where('id', $id)
            ->where('lesson_id', $this->selectedId)
            ->delete();

        // Reload lesson assessments
        if ($this->lesson) {
            $this->lesson = Lesson::with('assessments')->find($this->selectedId);
        }

        $this->flashMessage('message', 'Quiz removed.');
    }

    public function closeForm()
    {
        $this->slideUpload = null;
        $this->pdfUpload = null;
        $this->dispatch('close-form');
    }

    public function selectItem($type, $id = null, $parentId = null): void
    {
        // Named params keep null ids from shifting parentId; skipRender avoids
        // re-painting this heavy form when the parent is about to replace it.
        $this->dispatch('select-item', type: $type, id: $id, parentId: $parentId)
            ->to(NewBuilder::class);
        $this->skipRender();
    }

    public function render()
    {
        return view('livewire.curriculum.forms.lesson-form', [
            'course' => $this->course,
            'lesson' => $this->lesson,
            'selectedLessonAssessments' => $this->selectedLessonAssessments,
        ]);
    }
}
