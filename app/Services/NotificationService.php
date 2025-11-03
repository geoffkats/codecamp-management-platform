<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Events\BadgeEarned;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create and broadcast a notification
     */
    public function notify(User $user, string $title, string $message, string $type = 'info', array $data = []): ?Notification
    {
        // Verify user exists and is active
        if (!$user || !$user->exists || !$user->is_active) {
            \Illuminate\Support\Facades\Log::warning('Attempted to notify inactive or non-existent user', [
                'user_id' => $user->id ?? null,
            ]);
            return null;
        }

        // Check if user should receive this notification type
        if (!$this->shouldNotifyUser($user, $type)) {
            return null;
        }

        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
        ]);

        // Broadcast real-time notification
        try {
            broadcast(new \App\Events\NotificationCreated($notification))->toOthers();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to broadcast notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Send email if user preferences allow
        if ($this->shouldSendEmail($user, $type)) {
            $this->sendEmailNotification($user, $title, $message, $type, $data);
        }

        return $notification;
    }

    /**
     * Check if user should receive this notification
     */
    private function shouldNotifyUser(User $user, string $type): bool
    {
        // Always send critical notifications
        if (in_array($type, ['error', 'critical'])) {
            return true;
        }

        // Later: Check user notification preferences
        // For now, send all notifications to active users
        return $user->is_active;
    }

    /**
     * Notify user about badge earned
     */
    public function notifyBadgeEarned(User $user, $badge): void
    {
        $this->notify(
            $user,
            '🎉 New Badge Earned!',
            "Congratulations! You've earned the '{$badge->name}' badge!",
            'achievement',
            [
                'badge_id' => $badge->id,
                'badge_name' => $badge->name,
                'badge_icon' => $badge->icon,
                'points_reward' => $badge->points_reward,
                'action_url' => route('badges.show', $badge),
            ]
        );

        // Trigger badge earned event for broadcasting
        event(new BadgeEarned($user, $badge));
    }

    /**
     * Notify user about course completion
     */
    public function notifyCourseCompleted(User $user, $course): void
    {
        $this->notify(
            $user,
            '🎓 Course Completed!',
            "Congratulations! You've completed '{$course->title}'",
            'success',
            [
                'course_id' => $course->id,
                'course_title' => $course->title,
                'action_url' => route('courses.show', $course),
            ]
        );

        // Send email notification
        try {
            Mail::to($user->email)->send(new \App\Mail\CourseCompletedMail($user, $course));
        } catch (\Exception $e) {
            Log::error('Failed to send course completion email', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify user about achievement
     */
    public function notifyAchievement(User $user, string $achievement, array $data = []): void
    {
        $this->notify(
            $user,
            '⭐ Achievement Unlocked!',
            $achievement,
            'achievement',
            $data
        );
    }

    /**
     * Notify user about assessment result
     */
    public function notifyAssessmentResult(User $user, $assessment, $passed, $score): void
    {
        $message = $passed
            ? "Great job! You passed '{$assessment->title}' with {$score}%"
            : "You scored {$score}% on '{$assessment->title}'. Keep studying!";

        $this->notify(
            $user,
            $passed ? '✅ Assessment Passed!' : '📝 Assessment Completed',
            $message,
            $passed ? 'success' : 'info',
            [
                'assessment_id' => $assessment->id,
                'assessment_title' => $assessment->title,
                'score' => $score,
                'passed' => $passed,
                'action_url' => route('assessments.show', $assessment),
            ]
        );
    }

    /**
     * Check if email should be sent based on user preferences
     */
    private function shouldSendEmail(User $user, string $type): bool
    {
        // For now, always send for important notifications
        // Later: Check user notification preferences
        return in_array($type, ['achievement', 'success', 'error']);
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification(User $user, string $title, string $message, string $type, array $data = []): void
    {
        try {
            Mail::to($user->email)->send(new \App\Mail\NotificationMail($title, $message, $type, $data));
        } catch (\Exception $e) {
            Log::error('Failed to send notification email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}


