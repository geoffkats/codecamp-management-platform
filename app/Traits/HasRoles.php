<?php

namespace App\Traits;

use App\Models\ClubSchedule;
use App\Models\CodeClub;
use App\Models\Role;
use App\Services\AttendanceService;
use Carbon\Carbon;

trait HasRoles
{
    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        $roleNames = $this->expandRoleAliases([$roleName]);
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        $roleNames = $this->expandRoleAliases($roleNames);
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereJsonContains('permissions', $permission)
            ->exists();
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get user's primary role name
     */
    public function getPrimaryRoleName(): ?string
    {
        $role = $this->roles()->orderBy('id')->first();
        return $role?->name;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is teacher/instructor
     */
    public function isTeacher(): bool
    {
        return $this->hasAnyRole(['teacher', 'ict_teacher', 'codecamp_trainer']);
    }

    public function isIctTeacher(): bool
    {
        return $this->hasRole('ict_teacher');
    }

    public function isCodecampTrainer(): bool
    {
        return $this->hasRole('codecamp_trainer') || $this->hasRole('teacher');
    }

    public function isClubFacilitator(): bool
    {
        if (! config('features.code_club', false)) {
            return false;
        }

        return $this->assignedCodeClubIds() !== [];
    }

    /**
     * Code club IDs where this user is actively assigned as facilitator or schedule instructor.
     *
     * @return array<int>
     */
    public function assignedCodeClubIds(): array
    {
        if (! config('features.code_club', false)) {
            return [];
        }

        $fromAssignments = $this->codeClubInstructorAssignments()
            ->where('status', 'active')
            ->whereHas('club', fn ($query) => $query->where('status', 'active'))
            ->pluck('code_club_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $fromSchedules = ClubSchedule::query()
            ->where('instructor_id', $this->id)
            ->whereHas('club', fn ($query) => $query->where('status', 'active'))
            ->pluck('code_club_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique(array_merge($fromAssignments, $fromSchedules)));
    }

    public function hasCodeClubAccess(): bool
    {
        if (! config('features.code_club', false)) {
            return false;
        }

        return $this->isAdmin() || $this->isSupervisor() || $this->isClubFacilitator();
    }

    public function isCodeClubStudent(): bool
    {
        if (! config('features.code_club', false)) {
            return false;
        }

        return $this->isStudent()
            && ($this->student_type === 'codeclub' || $this->studentProfile?->program_type === 'codeclub');
    }

    public function canAccessDiscussions(): bool
    {
        return ! $this->isCodeClubStudent();
    }

    /**
     * Club IDs where the user is assigned as a club-level facilitator.
     *
     * @return array<int>
     */
    public function clubFacilitatorClubIds(): array
    {
        if (! config('features.code_club', false)) {
            return [];
        }

        return $this->codeClubInstructorAssignments()
            ->where('status', 'active')
            ->pluck('code_club_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return array<int>
     */
    public function activeClubIds(): array
    {
        if (! config('features.code_club', false)) {
            return [];
        }

        if ($this->isAdmin() || $this->isSupervisor()) {
            return CodeClub::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        $scheduleClubIds = ClubSchedule::query()
            ->where('instructor_id', $this->id)
            ->pluck('code_club_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique(array_merge($this->clubFacilitatorClubIds(), $scheduleClubIds)));
    }

    /**
     * Clubs the user can facilitate on a specific date.
     *
     * @return array<int>
     */
    public function activeClubIdsForDate(Carbon|string $date): array
    {
        if (! config('features.code_club', false)) {
            return [];
        }

        $date = Carbon::parse($date);
        $service = app(AttendanceService::class);

        if ($this->isAdmin() || $this->isSupervisor()) {
            return CodeClub::query()
                ->where('status', 'active')
                ->get(['id', 'day_of_week'])
                ->load('schedules')
                ->filter(fn (CodeClub $club) => $service->clubMeetsOnDate($club, $date))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $ids = [];

        foreach ($this->clubFacilitatorClubIds() as $clubId) {
            $club = CodeClub::query()->with('schedules')->find($clubId);
            if (! $club || ! $service->clubMeetsOnDate($club, $date)) {
                continue;
            }

            $schedule = $service->clubScheduleForDate($club, $date);
            if (! $schedule?->instructor_id || (int) $schedule->instructor_id === (int) $this->id) {
                $ids[] = $clubId;
            }
        }

        $scheduleClubIds = ClubSchedule::query()
            ->where('instructor_id', $this->id)
            ->pluck('code_club_id')
            ->unique();

        foreach ($scheduleClubIds as $clubId) {
            $club = CodeClub::query()->with('schedules')->find($clubId);
            if (! $club || ! $service->clubMeetsOnDate($club, $date)) {
                continue;
            }

            $schedule = $service->clubScheduleForDate($club, $date);
            if ($schedule && (int) $schedule->instructor_id === (int) $this->id) {
                $ids[] = (int) $clubId;
            }
        }

        return array_values(array_unique($ids));
    }

    public function hasDualProgramAccess(): bool
    {
        if (! config('features.code_club', false)) {
            return false;
        }

        if ($this->isAdmin() || $this->isSupervisor() || $this->isIctTeacher()) {
            return false;
        }

        return $this->isCodecampTrainer() && $this->assignedCodeClubIds() !== [];
    }

    public function activeProgramContext(): string
    {
        return \App\Support\ProgramScope::context($this);
    }

    /**
     * Check if user is student
     */
    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    /**
     * Check if user is supervisor
     */
    public function isSupervisor(): bool
    {
        return $this->hasRole('supervisor');
    }

    /**
     * Check if user is operations manager
     */
    public function isOperationsManager(): bool
    {
        return $this->hasRole('operations_manager');
    }

    /**
     * Staff course catalog (/courses) — not for learners.
     */
    public function canAccessCourseCatalog(): bool
    {
        if ($this->isAdmin() || $this->isSupervisor() || $this->isTeacher() || $this->isOperationsManager()) {
            return true;
        }

        return !$this->isStudent();
    }

    /**
     * Expand role aliases for compatibility checks.
     */
    protected function expandRoleAliases(array $roleNames): array
    {
        $expanded = [];

        foreach ($roleNames as $roleName) {
            if ($roleName === 'teacher') {
                $expanded[] = 'teacher';
                $expanded[] = 'ict_teacher';
                $expanded[] = 'codecamp_trainer';
                continue;
            }

            $expanded[] = $roleName;
        }

        return array_values(array_unique($expanded));
    }
}

