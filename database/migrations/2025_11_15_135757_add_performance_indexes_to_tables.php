<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Course Enrollments
        $this->addIndex('course_enrollments', ['user_id', 'course_id'], 'idx_enrollments_user_course');
        $this->addIndex('course_enrollments', ['course_id', 'completed_at'], 'idx_enrollments_course_completed');
        $this->addIndex('course_enrollments', ['user_id', 'progress_percentage'], 'idx_enrollments_user_progress');
        $this->addIndex('course_enrollments', 'enrolled_at');
        $this->addIndex('course_enrollments', 'completed_at');

        // Student Lesson Progress
        $this->addIndex('student_lesson_progress', ['user_id', 'lesson_id'], 'idx_progress_user_lesson');
        $this->addIndex('student_lesson_progress', ['lesson_id', 'status'], 'idx_progress_lesson_status');
        $this->addIndex('student_lesson_progress', ['user_id', 'status'], 'idx_progress_user_status');
        $this->addIndex('student_lesson_progress', 'completed_at');

        // Lesson Progress
        if (Schema::hasTable('lesson_progress')) {
            $this->addIndex('lesson_progress', ['user_id', 'lesson_id'], 'idx_lesson_progress_user_lesson');
            $this->addIndex('lesson_progress', ['lesson_id', 'is_completed'], 'idx_lesson_progress_completed');
        }

        // Video Progress
        if (Schema::hasTable('video_progress')) {
            $this->addIndex('video_progress', ['user_id', 'lesson_id'], 'idx_video_progress_user_lesson');
            $this->addIndex('video_progress', ['lesson_id', 'is_completed'], 'idx_video_progress_completed');
        }

        // User Points
        $this->addIndex('user_points', 'total_points');
        $this->addIndex('user_points', ['user_id', 'total_points'], 'idx_user_points_user_total');
        $this->addIndex('user_points', 'level');

        // Courses
        $this->addIndex('courses', ['is_published', 'approval_status'], 'idx_courses_published_status');
        $this->addIndex('courses', ['instructor_id', 'is_published'], 'idx_courses_instructor_published');
        $this->addIndex('courses', 'is_featured');

        // Lessons
        $this->addIndex('lessons', ['module_id', 'order_index'], 'idx_lessons_module_order');
        $this->addIndex('lessons', 'lesson_type');
        $this->addIndex('lessons', 'is_published');

        // Course Modules
        $this->addIndex('course_modules', ['course_id', 'order_index'], 'idx_modules_course_order');

        // Assignments
        if (Schema::hasTable('assignments')) {
            $this->addIndex('assignments', 'due_date');
            $this->addIndex('assignments', ['lesson_id', 'due_date'], 'idx_assignments_lesson_due');
        }

        // Assignment Submissions
        if (Schema::hasTable('assignment_submissions')) {
            $this->addIndex('assignment_submissions', ['user_id', 'assignment_id'], 'idx_submissions_user_assignment');
            $this->addIndex('assignment_submissions', ['assignment_id', 'status'], 'idx_submissions_assignment_status');
            $this->addIndex('assignment_submissions', 'submitted_at');
            $this->addIndex('assignment_submissions', 'graded_at');
        }

        // Assessments
        if (Schema::hasTable('assessments')) {
            $this->addIndex('assessments', 'lesson_id');
        }

        // Assessment Attempts
        if (Schema::hasTable('assessment_attempts')) {
            $this->addIndex('assessment_attempts', ['user_id', 'assessment_id'], 'idx_attempts_user_assessment');
            $this->addIndex('assessment_attempts', ['assessment_id', 'is_passed'], 'idx_attempts_assessment_passed');
            $this->addIndex('assessment_attempts', 'completed_at');
        }

        // Notifications
        $this->addIndex('notifications', ['user_id', 'is_read'], 'idx_notifications_user_read');
        $this->addIndex('notifications', ['user_id', 'created_at'], 'idx_notifications_user_created');

        // User Badges
        if (Schema::hasTable('user_badges')) {
            $this->addIndex('user_badges', ['user_id', 'badge_id'], 'idx_user_badges_user_badge');
            $this->addIndex('user_badges', 'earned_at');
        }

        // Daily Challenges
        if (Schema::hasTable('daily_challenges')) {
            $this->addIndex('daily_challenges', ['is_active', 'date'], 'idx_challenges_active_date');
        }

        // Daily Challenge Attempts
        if (Schema::hasTable('daily_challenge_attempts')) {
            $this->addIndex('daily_challenge_attempts', ['user_id', 'challenge_id'], 'idx_challenge_attempts_user_challenge');
            $this->addIndex('daily_challenge_attempts', 'completed_at');
        }

        // Content Approvals
        if (Schema::hasTable('content_approvals')) {
            $this->addIndex('content_approvals', ['status', 'submitted_at'], 'idx_approvals_status_submitted');
            $this->addIndex('content_approvals', ['approvable_type', 'approvable_id'], 'idx_approvals_approvable');
        }

        // Certificates
        if (Schema::hasTable('certificates')) {
            $this->addIndex('certificates', ['user_id', 'course_id'], 'idx_certificates_user_course');
            $this->addIndex('certificates', 'issued_at');
        }

        // User Progress
        if (Schema::hasTable('user_progress')) {
            $this->addIndex('user_progress', ['user_id', 'course_id'], 'idx_user_progress_user_course');
            $this->addIndex('user_progress', ['user_id', 'type'], 'idx_user_progress_user_type');
            $this->addIndex('user_progress', 'completed_at');
        }

        // Leaderboards
        if (Schema::hasTable('leaderboards')) {
            $this->addIndex('leaderboards', ['type', 'rank'], 'idx_leaderboards_type_rank');
            $this->addIndex('leaderboards', ['user_id', 'type'], 'idx_leaderboards_user_type');
        }
    }

    private function addIndex($table, $columns, $indexName = null)
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                if ($indexName) {
                    $table->index($columns, $indexName);
                } else {
                    $table->index($columns);
                }
            });
        } catch (\Exception $e) {
            // Index might already exist, skip
            if (!str_contains($e->getMessage(), 'Duplicate key name')) {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        // Rollback is optional for indexes - they don't hurt if left in place
        // But we'll provide it for completeness
    }
};
