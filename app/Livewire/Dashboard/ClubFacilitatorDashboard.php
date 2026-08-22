<?php

namespace App\Livewire\Dashboard;

use App\Models\ClubSessionReport;
use App\Models\CodeClub;
use App\Models\CodeClubMembership;
use App\Models\StudentAttendance;
use App\Models\StudentProfile;
use App\Services\AttendanceService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ClubFacilitatorDashboard extends Component
{
    public function mount(): void
    {
        abort_unless(config('features.code_club', false), 404);
        abort_unless(Auth::user()->hasCodeClubAccess(), 403);
    }

    public function render()
    {
        $user = Auth::user();

        $attendanceService = app(AttendanceService::class);
        $today = now()->toDateString();

        $clubIds = $user->activeClubIds();
        $todayClubIds = $user->activeClubIdsForDate($today);
        $clubs = CodeClub::with(['school', 'schedules', 'activeMemberships'])->whereIn('id', $clubIds)->get();

        $clubSummaries = $clubs->map(function (CodeClub $club) use ($attendanceService, $today, $todayClubIds) {
            $meetsToday = in_array((int) $club->id, $todayClubIds, true)
                && $attendanceService->clubMeetsOnDate($club, $today);
            $pendingReport = $meetsToday && ! ClubSessionReport::query()
                ->where('code_club_id', $club->id)
                ->whereDate('session_date', $today)
                ->exists();

            return [
                'club' => $club,
                'meets_today' => $meetsToday,
                'pending_report' => $pendingReport,
            ];
        });

        $pendingReportClubs = $clubSummaries
            ->filter(fn (array $summary) => $summary['pending_report'])
            ->values();

        $retentionAlerts = $this->absenceAlerts($clubIds, $attendanceService);

        $activeMembers = CodeClubMembership::whereIn('code_club_id', $clubIds)
            ->where('status', 'active')
            ->count();

        $recentReports = ClubSessionReport::with('club')
            ->whereIn('code_club_id', $clubIds)
            ->when(! $user->isAdmin() && ! $user->isSupervisor(), fn ($q) => $q->where('facilitator_id', $user->id))
            ->orderByDesc('session_date')
            ->take(5)
            ->get();

        $attendanceThisMonth = StudentAttendance::whereIn('club_id', $clubIds)
            ->whereNull('course_id')
            ->whereBetween('attendance_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->whereIn('status', ['present', 'late'])
            ->count();

        return view('livewire.dashboard.club-facilitator-dashboard', [
            'user' => $user,
            'clubs' => $clubs,
            'clubSummaries' => $clubSummaries,
            'pendingReportClubs' => $pendingReportClubs,
            'retentionAlerts' => $retentionAlerts,
            'activeMembers' => $activeMembers,
            'recentReports' => $recentReports,
            'attendanceThisMonth' => $attendanceThisMonth,
        ]);
    }

    /**
     * Members with 3+ absences across the last 4 club sessions.
     *
     * @param  array<int>  $clubIds
     * @return Collection<int, array{club: CodeClub, profile: StudentProfile, absences: int}>
     */
    private function absenceAlerts(array $clubIds, AttendanceService $attendanceService): Collection
    {
        if ($clubIds === []) {
            return collect();
        }

        $alerts = collect();

        $clubs = CodeClub::query()->whereIn('id', $clubIds)->with('schedules')->get(['id', 'name', 'day_of_week']);

        foreach ($clubs as $club) {
            $sessionDates = $attendanceService->clubSessionDates($club, 4);
            if ($sessionDates === []) {
                continue;
            }

            $userIds = CodeClubMembership::where('code_club_id', $club->id)
                ->where('status', 'active')
                ->pluck('student_id');

            $profiles = StudentProfile::query()
                ->whereIn('user_id', $userIds)
                ->where('is_active', true)
                ->with('user')
                ->get();

            $records = StudentAttendance::query()
                ->where('club_id', $club->id)
                ->whereNull('course_id')
                ->whereIn('attendance_date', $sessionDates)
                ->whereIn('student_profile_id', $profiles->pluck('id'))
                ->get()
                ->groupBy('student_profile_id');

            foreach ($profiles as $profile) {
                $absences = 0;

                foreach ($sessionDates as $date) {
                    $status = $records->get($profile->id)?->first(
                        fn (StudentAttendance $record) => $record->attendance_date->toDateString() === $date
                    )?->status;
                    if (! in_array($status, ['present', 'late'], true)) {
                        $absences++;
                    }
                }

                if ($absences >= 3) {
                    $alerts->push([
                        'club' => $club,
                        'profile' => $profile,
                        'absences' => $absences,
                    ]);
                }
            }
        }

        return $alerts->sortByDesc('absences')->values();
    }
}
