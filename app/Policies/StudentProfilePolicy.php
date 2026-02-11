<?php

namespace App\Policies;

use App\Models\StudentProfile;
use App\Models\User;

class StudentProfilePolicy
{
    public function view(User $user, StudentProfile $student): bool
    {
        if ($user->isAdmin() || $user->isOperationsManager() || $user->isSupervisor()) {
            return true;
        }

        if ($user->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();
            return $schoolId !== null
                && $student->program_type === 'ict'
                && (int) $student->school_id === (int) $schoolId;
        }

        if ($user->isCodecampTrainer()) {
            return $student->program_type === 'codecamp';
        }

        return false;
    }

    public function update(User $user, StudentProfile $student): bool
    {
        return $this->view($user, $student);
    }

    public function create(User $user, string $programType = null, int $schoolId = null): bool
    {
        if ($user->isAdmin() || $user->isOperationsManager() || $user->isSupervisor()) {
            return true;
        }

        if ($user->isIctTeacher()) {
            $teacherSchoolId = $user->ictSchoolId();
            return $teacherSchoolId !== null
                && $programType === 'ict'
                && (int) $schoolId === (int) $teacherSchoolId;
        }

        if ($user->isCodecampTrainer()) {
            return $programType === 'codecamp';
        }

        return false;
    }
}
