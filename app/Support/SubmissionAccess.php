<?php

namespace App\Support;

use App\Models\AssessmentAttempt;
use App\Models\AssignmentSubmission;
use App\Models\User;

class SubmissionAccess
{
    public static function canView(User $user, AssignmentSubmission|AssessmentAttempt $submission): bool
    {
        if ($user->isAdmin() || $user->isSupervisor()) {
            return true;
        }

        if ($user->isIctTeacher()) {
            if (! $submission instanceof AssessmentAttempt) {
                return false;
            }

            $schoolId = $user->ictSchoolId();

            return $schoolId
                && $submission->student_type === 'ict'
                && (int) $submission->school_id === (int) $schoolId;
        }

        if (ProgramScope::isClubFacilitatorContext($user)) {
            $ids = array_map('intval', ProgramScope::clubStudentUserIds($user));

            return in_array((int) $submission->user_id, $ids, true);
        }

        if ($user->isTeacher()) {
            if ($submission instanceof AssessmentAttempt && $submission->student_type === 'ict') {
                return false;
            }

            $course = $submission instanceof AssessmentAttempt
                ? $submission->assessment?->course
                : $submission->assignment?->course;

            return $course && $course->isStaffFor($user);
        }

        if (config('features.code_club', false) && $user->hasCodeClubAccess()) {
            $ids = array_map('intval', ProgramScope::clubStudentUserIds($user));

            return in_array((int) $submission->user_id, $ids, true);
        }

        return (int) $submission->user_id === (int) $user->id;
    }

    public static function authorizeView(User $user, AssignmentSubmission|AssessmentAttempt $submission): void
    {
        if (! static::canView($user, $submission)) {
            abort(403, 'You do not have permission to view this submission.');
        }
    }

    public static function authorizeGrade(User $user, AssignmentSubmission|AssessmentAttempt $submission): void
    {
        if (! $user->can('grade_submissions')) {
            abort(403, 'You do not have permission to grade submissions.');
        }

        static::authorizeView($user, $submission);
    }
}
