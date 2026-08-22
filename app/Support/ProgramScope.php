<?php

namespace App\Support;

use App\Models\CodeClub;
use App\Models\CodeClubMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ProgramScope
{
    public static function featureEnabled(): bool
    {
        return (bool) config('features.code_club', false);
    }

    public static function context(User $user): string
    {
        if (! static::featureEnabled()) {
            return 'codecamp';
        }

        if ($user->hasDualProgramAccess()) {
            $ctx = session('active_program_context', 'codecamp');

            return in_array($ctx, ['codecamp', 'codeclub'], true) ? $ctx : 'codecamp';
        }

        if ($user->hasCodeClubAccess() && ! $user->isCodecampTrainer() && ! $user->isIctTeacher()) {
            return 'codeclub';
        }

        return 'codecamp';
    }

    public static function applyStudentProfileScope(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return $query;
        }

        if ($user->isStudent()) {
            return static::scopeToStudentPeers($query, $user);
        }

        if ($user->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();
            if (! $schoolId) {
                return $query->whereRaw('1 = 0');
            }

            return $query
                ->where('program_type', 'ict')
                ->where('school_id', $schoolId);
        }

        if (! static::featureEnabled()) {
            return $query->where('program_type', '!=', 'codeclub');
        }

        if (! $user->hasCodeClubAccess()) {
            $query->where('program_type', '!=', 'codeclub');
        }

        if ($user->isCodecampTrainer() && ! $user->hasCodeClubAccess()) {
            return $query->where('program_type', 'codecamp');
        }

        if ($user->hasDualProgramAccess()) {
            if (static::context($user) === 'codecamp') {
                return $query->where('program_type', 'codecamp');
            }

            return static::scopeToFacilitatorClubs($query, $user);
        }

        if ($user->isClubFacilitator() && ! $user->isAdmin() && ! $user->isSupervisor()) {
            return static::scopeToFacilitatorClubs($query, $user);
        }

        return $query;
    }

    public static function scopeToStudentPeers(Builder $query, User $user): Builder
    {
        $profile = $user->studentProfile;
        $programType = $profile?->program_type ?? 'codecamp';

        if ($programType === 'codeclub') {
            $clubId = $user->activeCodeClubMembership?->code_club_id;

            if (! $clubId) {
                return $query->whereRaw('1 = 0');
            }

            return $query
                ->where('program_type', 'codeclub')
                ->whereHas('user.codeClubMemberships', fn ($m) => $m
                    ->where('code_club_id', $clubId)
                    ->where('status', 'active'));
        }

        if ($programType === 'ict') {
            $schoolId = $profile?->school_id;

            if (! $schoolId) {
                return $query->whereRaw('1 = 0');
            }

            return $query
                ->where('program_type', 'ict')
                ->where('school_id', $schoolId);
        }

        return $query->where('program_type', 'codecamp');
    }

    public static function scopeToFacilitatorClubs(Builder $query, User $user): Builder
    {
        $clubIds = $user->activeClubIds();

        if (empty($clubIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where('program_type', 'codeclub')
            ->whereHas('user.codeClubMemberships', function ($membership) use ($clubIds) {
                $membership->whereIn('code_club_id', $clubIds)->where('status', 'active');
            });
    }

    public static function applyCourseEnrollmentScope(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();

        if (! $user || ! static::featureEnabled()) {
            return $query->whereHas('user.studentProfile', fn ($q) => $q->where('program_type', '!=', 'codeclub'));
        }

        if ($user->isAdmin() || $user->isSupervisor()) {
            return $query;
        }

        if ($user->isIctTeacher()) {
            return $query->whereHas('user.studentProfile', fn ($q) => $q->where('program_type', 'ict'));
        }

        if (! $user->hasCodeClubAccess()) {
            return $query->whereHas('user.studentProfile', fn ($q) => $q->where('program_type', '!=', 'codeclub'));
        }

        if ($user->hasDualProgramAccess() && static::context($user) === 'codecamp') {
            return $query->whereHas('user.studentProfile', fn ($q) => $q->where('program_type', 'codecamp'));
        }

        if (static::context($user) === 'codeclub' || ($user->isClubFacilitator() && ! $user->isCodecampTrainer())) {
            $clubIds = $user->activeClubIds();

            if (empty($clubIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where(function ($q) use ($clubIds) {
                $q->whereIn('club_id', $clubIds)
                    ->orWhereHas('user.studentProfile', function ($profile) use ($clubIds) {
                        $profile->where('program_type', 'codeclub')
                            ->whereHas('user.codeClubMemberships', fn ($m) => $m->whereIn('code_club_id', $clubIds)->where('status', 'active'));
                    });
            });
        }

        return $query->whereHas('user.studentProfile', fn ($q) => $q->where('program_type', '!=', 'codeclub'));
    }

    public static function isClubFacilitatorContext(?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (! $user || ! static::featureEnabled() || ! $user->hasCodeClubAccess()) {
            return false;
        }

        if ($user->isAdmin() || $user->isSupervisor()) {
            return false;
        }

        return static::context($user) === 'codeclub';
    }

    /**
     * @return array<int>
     */
    public static function clubStudentUserIds(?User $user = null): array
    {
        $user = $user ?? auth()->user();

        if (! $user || ! static::featureEnabled()) {
            return [];
        }

        $clubIds = $user->activeClubIds();

        if ($clubIds === []) {
            return [];
        }

        return CodeClubMembership::query()
            ->whereIn('code_club_id', $clubIds)
            ->where('status', 'active')
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public static function visibleClubs(?User $user = null)
    {
        $user = $user ?? auth()->user();

        if (! $user || ! static::featureEnabled()) {
            return collect();
        }

        if ($user->isAdmin() || $user->isSupervisor()) {
            return CodeClub::query()->orderBy('name')->get(['id', 'name', 'status', 'school_id']);
        }

        $clubIds = $user->activeClubIds();

        if (empty($clubIds)) {
            return collect();
        }

        return CodeClub::query()
            ->whereIn('id', $clubIds)
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'school_id']);
    }
}
