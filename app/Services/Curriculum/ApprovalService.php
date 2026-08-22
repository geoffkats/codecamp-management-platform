<?php

namespace App\Services\Curriculum;

use App\Models\ContentApproval;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Notification;
use App\Models\User;

class ApprovalService
{
    public function approveCourse(Course $course, int $approvedBy): void
    {
        $course->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy,
        ]);

        Notification::create([
            'user_id' => $course->instructor_id,
            'title' => 'Course Approved',
            'message' => 'Your course "' . $course->title . '" has been approved and is now published.',
            'type' => 'success',
        ]);
    }

    public function rejectCourse(Course $course, string $reason, int $rejectedBy): void
    {
        $course->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        Notification::create([
            'user_id' => $course->instructor_id,
            'title' => 'Course Rejected',
            'message' => 'Your course "' . $course->title . '" has been rejected. Reason: ' . $reason,
            'type' => 'error',
        ]);
    }

    public function submitLessonForApproval(Lesson $lesson): void
    {
        $lesson->update([
            'approval_status' => 'pending',
            'submitted_for_approval_at' => now(),
        ]);
    }

    public function approveLesson(Lesson $lesson, User $approver): void
    {
        $lesson->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'rejection_reason' => null,
        ]);

        $contentApproval = ContentApproval::where('approvable_type', Lesson::class)
            ->where('approvable_id', $lesson->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($contentApproval) {
            $contentApproval->update([
                'status' => 'approved',
                'reviewed_by' => $approver->id,
                'reviewed_at' => now(),
            ]);
        }

        $teacher = $lesson->course->instructor;
        if ($teacher) {
            Notification::create([
                'user_id' => $teacher->id,
                'title' => 'Lesson Approved',
                'message' => 'Your lesson "' . $lesson->title . '" has been approved by ' . $approver->name,
                'type' => 'lesson_approved',
                'data' => [
                    'lesson_id' => $lesson->id,
                    'lesson_title' => $lesson->title,
                    'course_id' => $lesson->course_id,
                    'approved_by' => $approver->name,
                    'url' => route('curriculum.builder', ['course' => $lesson->course_id]),
                ],
            ]);
        }
    }

    public function disapproveLesson(Lesson $lesson, User $reviewer, string $reason): void
    {
        $lesson->update([
            'approval_status' => 'pending',
            'rejection_reason' => $reason,
            'approved_at' => null,
            'approved_by' => null,
            'submitted_for_approval_at' => now(),
        ]);

        $contentApproval = ContentApproval::where('approvable_type', Lesson::class)
            ->where('approvable_id', $lesson->id)
            ->latest()
            ->first();

        if ($contentApproval) {
            $contentApproval->update([
                'status' => 'pending',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
                'notes' => 'Needs revision: ' . $reason,
            ]);
        } else {
            ContentApproval::create([
                'approvable_type' => Lesson::class,
                'approvable_id' => $lesson->id,
                'status' => 'pending',
                'submitted_by' => $lesson->course->instructor_id,
                'submitted_at' => now(),
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
                'notes' => 'Needs revision: ' . $reason,
                'priority' => 'high',
                'category' => 'revision',
            ]);
        }

        $teacher = $lesson->course->instructor;
        if ($teacher) {
            Notification::create([
                'user_id' => $teacher->id,
                'title' => 'Lesson Needs Revision',
                'message' => 'Your lesson "' . $lesson->title . '" needs revision. Reason: ' . $reason,
                'type' => 'lesson_rejected',
                'data' => [
                    'lesson_id' => $lesson->id,
                    'lesson_title' => $lesson->title,
                    'course_id' => $lesson->course_id,
                    'rejected_by' => $reviewer->name,
                    'rejection_reason' => $reason,
                    'url' => route('curriculum.builder', ['course' => $lesson->course_id]),
                ],
            ]);
        }
    }

    public function rejectLesson(Lesson $lesson, int $reviewedBy, string $reason): void
    {
        $lesson->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function notifyApprovers(Lesson $lesson, string $action, User $teacher): void
    {
        $approvers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'supervisor']);
        })->get();

        $actionText = $action === 'updated' ? 'updated and needs re-approval' : 'created and needs approval';

        foreach ($approvers as $approver) {
            Notification::create([
                'user_id' => $approver->id,
                'title' => 'Lesson Approval Required',
                'message' => $teacher->name . ' has ' . $actionText . ': "' . $lesson->title . '"',
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
}
