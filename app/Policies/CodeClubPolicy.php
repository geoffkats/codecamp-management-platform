<?php

namespace App\Policies;

use App\Models\CodeClub;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;

class CodeClubPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSupervisor() || $user->hasCodeClubAccess();
    }

    public function view(User $user, CodeClub $club): bool
    {
        if ($user->isAdmin() || $user->isSupervisor()) {
            return true;
        }

        return in_array((int) $club->id, $user->assignedCodeClubIds(), true);
    }

    public function generateReports(User $user, CodeClub $club): bool
    {
        return $this->view($user, $club);
    }

    public function manageAttendance(User $user, CodeClub $club, Carbon|string|null $date = null): bool
    {
        if ($user->isAdmin() || $user->isSupervisor()) {
            return true;
        }

        if (! $user->isClubFacilitator()) {
            return false;
        }

        if (! in_array((int) $club->id, $user->activeClubIds(), true)) {
            return false;
        }

        return true;
    }

    public function submitSessionReport(User $user, CodeClub $club): bool
    {
        return $this->view($user, $club);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSupervisor();
    }

    public function update(User $user, CodeClub $club): bool
    {
        return $user->isAdmin() || $user->isSupervisor();
    }

    public function manageMembers(User $user, CodeClub $club): bool
    {
        return $this->view($user, $club);
    }

    public function manageInstructors(User $user, CodeClub $club): bool
    {
        return $user->isAdmin() || $user->isSupervisor();
    }
}
