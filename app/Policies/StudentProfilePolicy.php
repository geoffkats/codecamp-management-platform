<?php

namespace App\Policies;

use App\Models\CodeClubMembership;
use App\Models\StudentProfile;
use App\Models\User;

class StudentProfilePolicy
{
    public function view(User $user, StudentProfile $student): bool
    {
        if ((int) $user->id === (int) $student->user_id) {
            return true;
        }

        if ($user->isAdmin() || $user->isOperationsManager() || $user->isSupervisor()) {
            return true;
        }

        if ($student->program_type === 'ict' && $user->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();

            return $schoolId !== null && (int) $student->school_id === (int) $schoolId;
        }

        if ($student->program_type === 'codeclub' && $this->facilitatorCanAccessStudent($user, $student)) {
            return true;
        }

        if ($student->program_type === 'codecamp' && $user->isCodecampTrainer()) {
            return true;
        }

        return false;
    }

    public function update(User $user, StudentProfile $student): bool
    {
        return $this->view($user, $student);
    }

    public function delete(User $user, StudentProfile $student): bool
    {
        if (! $user->isAdmin() && ! $user->isSupervisor() && ! $user->isOperationsManager()) {
            return false;
        }

        return $this->view($user, $student);
    }

    public function create(User $user, string $programType = null, int $schoolId = null): bool
    {
        if ($user->isAdmin() || $user->isOperationsManager() || $user->isSupervisor()) {
            return true;
        }

        if ($programType === 'ict' && $user->isIctTeacher()) {
            $teacherSchoolId = $user->ictSchoolId();

            return $teacherSchoolId !== null && (int) $schoolId === (int) $teacherSchoolId;
        }

        if ($programType === 'codeclub' && $user->isClubFacilitator()) {
            return true;
        }

        if ($programType === 'codecamp' && $user->isCodecampTrainer()) {
            return true;
        }

        return false;
    }

    private function facilitatorCanAccessStudent(User $user, StudentProfile $student): bool
    {
        if (! $user->isClubFacilitator()) {
            return false;
        }

        $clubIds = $user->activeClubIds();

        if ($clubIds === [] || ! $student->user_id) {
            return false;
        }

        return CodeClubMembership::query()
            ->where('student_id', $student->user_id)
            ->where('status', 'active')
            ->whereIn('code_club_id', $clubIds)
            ->exists();
    }
}
