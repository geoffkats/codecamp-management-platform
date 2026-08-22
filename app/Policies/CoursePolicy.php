<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Determine if the user can view any courses.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_courses');
    }

    /**
     * Determine if the user can view the course.
     */
    public function view(User $user, Course $course): bool
    {
        // Students can view published and approved courses, or courses they're enrolled in
        if ($user->isStudent()) {
            return ($course->is_published && $course->approval_status === 'approved') 
                || $user->enrollments()->where('course_id', $course->id)->exists();
        }

        // Teachers can view courses they own or collaborate on
        if ($user->isTeacher()) {
            return $course->isStaffFor($user);
        }

        // Admins and supervisors can view all
        return $user->isAdmin() || $user->isSupervisor();
    }

    /**
     * Determine if the user can create courses.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_courses');
    }

    /**
     * Determine if the user can update the course.
     */
    public function update(User $user, Course $course): bool
    {
        // Anyone with explicit permission can edit any course
        if ($user->hasPermission('edit_courses')) {
            return true;
        }

        // Admins can edit all courses
        if ($user->isAdmin()) {
            return true;
        }

        // Teachers without explicit permission: instructor or editor collaborator
        if ($user->isTeacher()) {
            return $course->canUserEdit($user);
        }

        return false;
    }

    /**
     * Determine if the user can delete the course.
     */
    public function delete(User $user, Course $course): bool
    {
        if (!$user->hasPermission('delete_courses')) {
            return false;
        }

        // Teachers can only delete their own courses
        if ($user->isTeacher()) {
            return $course->instructor_id === $user->id;
        }

        // Admins can delete all courses
        return $user->isAdmin();
    }

    /**
     * Determine if the user can enroll in the course.
     */
    public function enroll(User $user, Course $course): bool
    {
        // Can enroll if course is published, approved, and user has permission
        return $user->hasPermission('enroll_courses')
            && $course->is_published
            && $course->approval_status === 'approved'
            && !$user->enrollments()->where('course_id', $course->id)->exists();
    }
}
