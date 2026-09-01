<?php

namespace App\Services;

use App\Models\CampEnrollment;
use App\Models\ClubSchedule;
use App\Models\CodeCamp;
use App\Models\CodeClub;
use App\Models\CodeClubMembership;
use App\Models\DailyAttendanceCode;
use App\Models\DailyReport;
use App\Models\StudentAttendance;
use App\Models\StudentProfile;
use App\Models\User;
use App\Support\TimeOfDay;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AttendanceService
{
    public function checkInWindow(): array
    {
        return [
            'start' => config('attendance.check_in_start', '08:00'),
            'end'   => config('attendance.check_in_end', '10:00'),
        ];
    }

    public function isWithinCheckInWindow(?Carbon $at = null): bool
    {
        $at = $at ?? now();
        $window = $this->checkInWindow();

        return $at->between(
            Carbon::parse($at->toDateString() . ' ' . $window['start']),
            Carbon::parse($at->toDateString() . ' ' . $window['end'])
        );
    }

    public function isLateCheckIn(?Carbon $at = null): bool
    {
        $at = $at ?? now();
        $lateAfter = config('attendance.late_after', '09:30');

        return $at->gt(Carbon::parse($at->toDateString() . ' ' . $lateAfter))
            && $at->lte(Carbon::parse($at->toDateString() . ' ' . config('attendance.check_in_end', '10:00')));
    }

    public function canCheckInNow(): bool
    {
        return $this->isWithinCheckInWindow() || $this->isLateCheckIn();
    }

    public function isCodeClubProfile(StudentProfile $profile): bool
    {
        return $profile->program_type === 'codeclub';
    }

    public function resolveClubForProfile(StudentProfile $profile): ?CodeClub
    {
        $clubId = $this->resolveClubIdForProfile($profile);

        if (! $clubId) {
            return null;
        }

        return CodeClub::with('schedules')->find($clubId);
    }

    public function checkInWindowForProfile(?StudentProfile $profile, ?Carbon $at = null): array
    {
        if (! $profile || ! $this->isCodeClubProfile($profile)) {
            return array_merge($this->checkInWindow(), ['session_day' => true]);
        }

        $at = $at ?? now();
        $club = $this->resolveClubForProfile($profile);

        if (! $club || ! $this->clubMeetsOnDate($club, $at)) {
            return [
                'start' => null,
                'end' => null,
                'session_day' => false,
                'club_name' => $club?->name,
            ];
        }

        $schedule = $this->clubScheduleForDate($club, $at);
        $sessionStart = $schedule?->effectiveSessionStart($club)
            ?? ($club->session_start ? substr((string) $club->session_start, 0, 5) : null);
        $sessionEnd = $schedule?->effectiveSessionEnd($club)
            ?? ($club->session_end ? substr((string) $club->session_end, 0, 5) : null);

        if (! $sessionStart || ! $sessionEnd) {
            $sessionStart = config('attendance.club_default_session_start', '15:00');
            $sessionEnd = config('attendance.club_default_session_end', '16:30');
        }

        $earlyMinutes = (int) config('attendance.club_check_in_early_minutes', 15);
        $checkInStart = Carbon::parse($at->toDateString() . ' ' . $sessionStart)
            ->subMinutes($earlyMinutes)
            ->format('H:i');

        return [
            'start' => $checkInStart,
            'end' => $sessionEnd,
            'session_start' => $sessionStart,
            'session_end' => $sessionEnd,
            'session_start_display' => TimeOfDay::toDisplay($sessionStart),
            'session_end_display' => TimeOfDay::toDisplay($sessionEnd),
            'check_in_opens_display' => TimeOfDay::toDisplay($checkInStart),
            'session_day' => true,
            'club_name' => $club->name,
        ];
    }

    public function checkInStatusForProfile(?StudentProfile $profile, ?Carbon $at = null): string
    {
        $at = $at ?? now();

        if (! $profile || ! $this->isCodeClubProfile($profile)) {
            return $this->canCheckInNow() ? 'open' : 'closed';
        }

        $window = $this->checkInWindowForProfile($profile, $at);

        if (! ($window['session_day'] ?? false)) {
            return 'no_session';
        }

        if ($this->canCheckInNowForProfile($profile, $at)) {
            return 'open';
        }

        if (! $window['start']) {
            return 'closed';
        }

        $opensAt = Carbon::parse($at->toDateString() . ' ' . $window['start']);

        return $at->lt($opensAt) ? 'before' : 'after';
    }

    public function canCheckInNowForProfile(?StudentProfile $profile, ?Carbon $at = null): bool
    {
        $at = $at ?? now();

        if (! $profile || ! $this->isCodeClubProfile($profile)) {
            return $this->canCheckInNow();
        }

        $window = $this->checkInWindowForProfile($profile, $at);

        if (! ($window['session_day'] ?? false) || ! $window['start'] || ! $window['end']) {
            return false;
        }

        return $at->between(
            Carbon::parse($at->toDateString() . ' ' . $window['start']),
            Carbon::parse($at->toDateString() . ' ' . $window['end'])
        );
    }

    public function isLateCheckInForProfile(?StudentProfile $profile, ?Carbon $at = null): bool
    {
        $at = $at ?? now();

        if (! $profile || ! $this->isCodeClubProfile($profile)) {
            return $this->isLateCheckIn($at);
        }

        $window = $this->checkInWindowForProfile($profile, $at);

        if (! ($window['session_day'] ?? false) || ! isset($window['session_start'], $window['end'])) {
            return false;
        }

        $graceMinutes = (int) config('attendance.club_late_grace_minutes', 15);
        $lateAfter = Carbon::parse($at->toDateString() . ' ' . $window['session_start'])->addMinutes($graceMinutes);
        $windowEnd = Carbon::parse($at->toDateString() . ' ' . $window['end']);

        return $at->gt($lateAfter) && $at->lte($windowEnd);
    }

    public function minCheckoutMinutesForProfile(StudentProfile $profile): int
    {
        if ($this->isCodeClubProfile($profile)) {
            return (int) config('attendance.club_min_checkout_minutes', 30);
        }

        return (int) config('attendance.min_checkout_minutes', 60);
    }

    public function isLocked(Carbon|string $date, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if ($user && ($user->isAdmin() || $user->isSupervisor() || $user->isOperationsManager())) {
            return false;
        }

        $date = Carbon::parse($date)->startOfDay();
        $today = today()->startOfDay();

        if ($date->isFuture()) {
            return true;
        }

        if ($date->lt($today)) {
            $backfillDays = (int) config('attendance.teacher_backfill_days', 30);

            if ($user && $user->isTeacher() && $date->gte($today->copy()->subDays($backfillDays))) {
                return false;
            }

            return true;
        }

        $lockTime = config('attendance.lock_time', '17:00');

        return now()->format('H:i') >= $lockTime;
    }

    public function resolveCampIdForProfile(StudentProfile $profile): ?int
    {
        return CampEnrollment::where('student_id', $profile->user_id)
            ->where('status', 'active')
            ->orderByDesc('enrolled_at')
            ->value('camp_id');
    }

    public function getRecord(StudentProfile $profile, Carbon|string $date, ?int $courseId = null): ?StudentAttendance
    {
        $query = StudentAttendance::where('student_profile_id', $profile->id)
            ->where('attendance_date', Carbon::parse($date)->toDateString())
            ->when(
                $courseId,
                fn ($q) => $q->where('course_id', $courseId),
                fn ($q) => $q->whereNull('course_id')
            );

        if ($profile->program_type === 'codeclub') {
            $clubId = $this->resolveClubIdForProfile($profile);
            if ($clubId) {
                $query->where('club_id', $clubId);
            }
        }

        return $query->first();
    }

    public function roster(Carbon|string $date, ?int $campId = null, ?string $search = null): Collection
    {
        $dateStr = Carbon::parse($date)->toDateString();

        $query = StudentProfile::query()
            ->with('user')
            ->where('is_active', true)
            ->where('program_type', 'codecamp')
            ->where(function ($q) {
                $q->where('student_category', 'codecamp')
                    ->orWhereNull('student_category');
            });

        if ($campId) {
            $userIds = CampEnrollment::where('camp_id', $campId)
                ->where('status', 'active')
                ->pluck('student_id');
            $query->whereIn('user_id', $userIds);
        }

        if ($search) {
            $like = '%' . trim($search) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('full_name', 'like', $like)
                    ->orWhere('student_id', 'like', $like);
            });
        }

        $students = $query->orderBy('full_name')->get();

        $records = StudentAttendance::where('attendance_date', $dateStr)
            ->whereNull('course_id')
            ->when($campId, fn ($q) => $q->where(function ($q) use ($campId) {
                $q->where('camp_id', $campId)->orWhereNull('camp_id');
            }))
            ->whereIn('student_profile_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_profile_id');

        return $students->map(fn ($profile) => [
            'profile'  => $profile,
            'record'   => $records->get($profile->id),
            'status'   => $records->get($profile->id)?->status,
            'clock_in' => $records->get($profile->id)?->clock_in,
            'clock_out'=> $records->get($profile->id)?->clock_out,
            'source'   => $records->get($profile->id)?->source,
        ]);
    }

    public function checkIn(StudentProfile $profile, ?string $code = null): StudentAttendance
    {
        if (! $this->canCheckInNowForProfile($profile)) {
            $window = $this->checkInWindowForProfile($profile);

            if ($this->isCodeClubProfile($profile) && ! ($window['session_day'] ?? false)) {
                throw new \RuntimeException('There is no club session scheduled for today.');
            }

            $start = $window['start'] ?? '?';
            $end = $window['end'] ?? '?';
            throw new \RuntimeException("Check-in is only allowed during your session window ({$start}–{$end}).");
        }

        $todayCode = DailyAttendanceCode::getTodayCode() ?? DailyAttendanceCode::createTodayCode();
        $submitted = strtoupper(trim((string) $code));

        if ($submitted !== '' && $submitted !== strtoupper((string) $todayCode->code)) {
            throw new \RuntimeException('Invalid attendance code.');
        }

        $existing = $this->getRecord($profile, today());
        if ($existing?->clock_in) {
            throw new \RuntimeException('Already checked in today at ' . ($existing->formattedClockIn() ?? 'unknown time') . '.');
        }

        $now = now();
        $status = $this->isLateCheckInForProfile($profile, $now) ? 'late' : 'present';

        return $this->upsertDailyRecord($profile, today(), [
            'status'      => $status,
            'clock_in'    => $now->format('H:i:s'),
            'code_used'   => $submitted !== '' ? $todayCode->code : 'SELF',
            'source'      => $submitted !== '' ? 'check_in' : 'self_tap',
            'recorded_by' => $profile->user_id,
            'recorded_at' => $now,
        ]);
    }

    public function checkOut(StudentProfile $profile, ?string $code = null): StudentAttendance
    {
        $submitted = strtoupper(trim((string) $code));
        if ($submitted !== '') {
            $todayCode = DailyAttendanceCode::getTodayCode();
            if (! $todayCode || $submitted !== strtoupper((string) $todayCode->code)) {
                throw new \RuntimeException('Invalid attendance code.');
            }
        }

        $record = $this->getRecord($profile, today());
        if (! $record?->clock_in) {
            throw new \RuntimeException('You must check in first.');
        }
        if ($record->clock_out) {
            throw new \RuntimeException('Already checked out today.');
        }

        $checkIn = $record->clockInCarbon();
        $minMinutes = $this->minCheckoutMinutesForProfile($profile);
        if ($checkIn->diffInMinutes(now()) < $minMinutes) {
            $remaining = $minMinutes - $checkIn->diffInMinutes(now());
            throw new \RuntimeException("Please wait {$remaining} more minute(s) before checking out.");
        }

        $record->update([
            'clock_out'   => now()->format('H:i:s'),
            'recorded_at' => now(),
        ]);

        return $record->fresh();
    }

    public function markManual(
        StudentProfile $profile,
        string $status,
        Carbon|string $date,
        User $recorder,
        ?int $campId = null,
        ?string $reason = null,
        ?string $clockIn = null,
        ?string $clockOut = null,
    ): StudentAttendance {
        if ($this->isLocked($date, $recorder)) {
            throw new \RuntimeException('Attendance is locked for this date. Contact an admin to make changes.');
        }

        return $this->upsertDailyRecord($profile, $date, [
            'status'      => $status,
            'reason'      => $reason,
            'clock_in'    => $clockIn,
            'clock_out'   => $clockOut,
            'camp_id'     => $campId ?? $this->resolveCampIdForProfile($profile),
            'source'      => 'manual',
            'recorded_by' => $recorder->id,
            'recorded_at' => now(),
        ]);
    }

    public function markBulkPresent(Collection $profiles, Carbon|string $date, User $recorder, ?int $campId = null): int
    {
        $count = 0;
        $defaultOut = config('attendance.default_clock_out', '17:00') . ':00';

        foreach ($profiles as $profile) {
            if ($this->isLocked($date, $recorder)) {
                continue;
            }

            $existing = $this->getRecord($profile, $date);
            if ($existing?->clock_in) {
                continue;
            }

            $this->upsertDailyRecord($profile, $date, [
                'status'      => 'present',
                'clock_in'    => '08:00:00',
                'clock_out'   => $defaultOut,
                'code_used'   => 'MANUAL',
                'camp_id'     => $campId ?? $this->resolveCampIdForProfile($profile),
                'source'      => 'bulk',
                'recorded_by' => $recorder->id,
                'recorded_at' => now(),
            ]);
            $count++;
        }

        return $count;
    }

    public function prefillDailyReport(int $courseId, ?int $campId, Carbon|string $date): array
    {
        $dateStr = Carbon::parse($date)->toDateString();

        $studentIds = User::whereHas('enrollments', fn ($q) => $q->where('course_id', $courseId))
            ->where('student_type', 'codecamp')
            ->pluck('id');

        if ($campId) {
            $campStudentIds = CampEnrollment::where('camp_id', $campId)
                ->where('status', 'active')
                ->pluck('student_id');
            $studentIds = $studentIds->intersect($campStudentIds);
        }

        $profiles = StudentProfile::whereIn('user_id', $studentIds)
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        $records = StudentAttendance::where('attendance_date', $dateStr)
            ->whereNull('course_id')
            ->whereIn('student_profile_id', $profiles->pluck('id'))
            ->get()
            ->keyBy('student_profile_id');

        return $profiles->map(function ($profile) use ($records) {
            $record = $records->get($profile->id);
            $status = $record?->status ?? 'absent';

            if (in_array($status, ['present', 'late'], true) && ! $record?->clock_in) {
                $status = 'absent';
            }

            return [
                'student_id' => $profile->user_id,
                'status'     => in_array($status, ['present', 'late', 'absent'], true) ? $status : 'absent',
                'reason'     => $record?->reason ?? '',
            ];
        })->values()->all();
    }

    public function syncFromDailyReport(DailyReport $report, User $recorder): void
    {
        $rows = $report->attendance()->get();
        $campId = $report->camp_id;

        foreach ($rows as $row) {
            $profile = StudentProfile::where('user_id', $row->student_id)->first();
            if (! $profile) {
                continue;
            }

            $status = match ($row->status) {
                'present', 'late', 'absent' => $row->status,
                default => 'absent',
            };

            $existing = $this->getRecord($profile, $report->report_date);

            $this->upsertDailyRecord($profile, $report->report_date, [
                'status'      => $status,
                'reason'      => $row->reason,
                'camp_id'     => $campId ?? $this->resolveCampIdForProfile($profile),
                'clock_in'    => in_array($status, ['present', 'late'], true)
                    ? ($existing?->clock_in ?? '08:00:00')
                    : null,
                'clock_out'   => $existing?->clock_out,
                'source'      => 'report',
                'recorded_by' => $recorder->id,
                'recorded_at' => now(),
            ]);
        }
    }

    public function stats(Carbon|string $from, Carbon|string $to, ?int $campId = null): array
    {
        $query = StudentAttendance::whereNull('course_id')
            ->whereBetween('attendance_date', [
                Carbon::parse($from)->toDateString(),
                Carbon::parse($to)->toDateString(),
            ]);

        if ($campId) {
            $query->where('camp_id', $campId);
        }

        $toStr = Carbon::parse($to)->toDateString();

        return [
            'total'             => (clone $query)->count(),
            'present'           => (clone $query)->whereIn('status', ['present', 'late'])->count(),
            'absent'            => (clone $query)->where('status', 'absent')->count(),
            'late'              => (clone $query)->where('status', 'late')->count(),
            'checked_in_today'  => StudentAttendance::where('attendance_date', $toStr)
                ->whereNull('course_id')
                ->when($campId, fn ($q) => $q->where('camp_id', $campId))
                ->whereNotNull('clock_in')
                ->count(),
            'still_in_today'    => StudentAttendance::where('attendance_date', $toStr)
                ->whereNull('course_id')
                ->when($campId, fn ($q) => $q->where('camp_id', $campId))
                ->whereNotNull('clock_in')
                ->whereNull('clock_out')
                ->count(),
        ];
    }

    public function exportQuery(Carbon|string $from, Carbon|string $to, ?int $campId = null, ?string $search = null)
    {
        return StudentAttendance::with(['studentProfile', 'camp', 'recorder'])
            ->whereNull('course_id')
            ->whereBetween('attendance_date', [
                Carbon::parse($from)->toDateString(),
                Carbon::parse($to)->toDateString(),
            ])
            ->when($campId, fn ($q) => $q->where('camp_id', $campId))
            ->when($search, function ($q) use ($search) {
                $like = '%' . trim($search) . '%';
                $q->whereHas('studentProfile', fn ($sq) => $sq
                    ->where('full_name', 'like', $like)
                    ->orWhere('student_id', 'like', $like));
            })
            ->orderByDesc('attendance_date')
            ->orderByDesc('clock_in');
    }

    private function upsertDailyRecord(StudentProfile $profile, Carbon|string $date, array $data): StudentAttendance
    {
        $dateStr = Carbon::parse($date)->toDateString();

        $keys = [
            'student_profile_id' => $profile->id,
            'attendance_date'    => $dateStr,
            'course_id'          => null,
        ];

        if ($profile->program_type === 'codeclub') {
            $clubId = $data['club_id'] ?? $this->resolveClubIdForProfile($profile);
            $payload = array_merge([
                'club_id' => $clubId,
                'camp_id' => null,
            ], $data);
        } else {
            $campId = $data['camp_id'] ?? $this->resolveCampIdForProfile($profile);
            $payload = array_merge([
                'camp_id' => $campId,
                'club_id' => null,
            ], $data);
        }

        if (isset($payload['clock_in']) && $payload['clock_in'] instanceof Carbon) {
            $payload['clock_in'] = $payload['clock_in']->format('H:i:s');
        }

        if (isset($payload['clock_out']) && $payload['clock_out'] instanceof Carbon) {
            $payload['clock_out'] = $payload['clock_out']->format('H:i:s');
        }

        return StudentAttendance::updateOrCreate($keys, $payload);
    }

    public function resolveClubIdForProfile(StudentProfile $profile): ?int
    {
        return CodeClubMembership::where('student_id', $profile->user_id)
            ->where('status', 'active')
            ->orderByDesc('enrolled_at')
            ->value('code_club_id');
    }

    public function clubRoster(Carbon|string $date, int $clubId, ?string $search = null): Collection
    {
        $dateStr = Carbon::parse($date)->toDateString();

        $userIds = CodeClubMembership::where('code_club_id', $clubId)
            ->where('status', 'active')
            ->pluck('student_id');

        $query = StudentProfile::query()
            ->with('user')
            ->where('program_type', 'codeclub')
            ->where('is_active', true)
            ->whereIn('user_id', $userIds);

        if ($search) {
            $like = '%' . trim($search) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('full_name', 'like', $like)
                    ->orWhere('student_id', 'like', $like);
            });
        }

        $students = $query->orderBy('full_name')->get();

        $records = StudentAttendance::where('attendance_date', $dateStr)
            ->whereNull('course_id')
            ->where('club_id', $clubId)
            ->whereIn('student_profile_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_profile_id');

        return $students->map(fn ($profile) => [
            'profile'  => $profile,
            'record'   => $records->get($profile->id),
            'status'   => $records->get($profile->id)?->status ?? 'absent',
            'clock_in' => $records->get($profile->id)?->clock_in,
            'clock_out'=> $records->get($profile->id)?->clock_out,
            'source'   => $records->get($profile->id)?->source,
        ]);
    }

    public function markClubAttendance(
        StudentProfile $profile,
        Carbon|string $date,
        int $clubId,
        string $status,
        User $recorder
    ): StudentAttendance {
        $status = in_array($status, ['present', 'late', 'absent'], true) ? $status : 'absent';

        return $this->upsertClubRecord($profile, $date, $clubId, [
            'status'      => $status,
            'clock_in'    => in_array($status, ['present', 'late'], true) ? '15:00:00' : null,
            'clock_out'   => in_array($status, ['present', 'late'], true) ? '16:30:00' : null,
            'source'      => 'manual',
            'recorded_by' => $recorder->id,
            'recorded_at' => now(),
        ]);
    }

    private function upsertClubRecord(StudentProfile $profile, Carbon|string $date, int $clubId, array $data): StudentAttendance
    {
        $dateStr = Carbon::parse($date)->toDateString();

        $keys = [
            'student_profile_id' => $profile->id,
            'attendance_date'    => $dateStr,
            'course_id'          => null,
            'club_id'            => $clubId,
        ];

        $payload = array_merge(['club_id' => $clubId, 'camp_id' => null], $data);

        return StudentAttendance::updateOrCreate($keys, $payload);
    }

    public function clubMeetsOnDate(CodeClub|string|null $clubOrDay, Carbon|string|null $date = null): bool
    {
        $date = Carbon::parse($date ?? now());

        if ($clubOrDay instanceof CodeClub) {
            return $this->clubScheduleForDate($clubOrDay, $date) !== null
                || (
                    ! $clubOrDay->hasScheduleRows()
                    && $clubOrDay->day_of_week
                    && $this->dayOfWeekMatches($date, $clubOrDay->day_of_week)
                );
        }

        if (! $clubOrDay) {
            return false;
        }

        return $this->dayOfWeekMatches($date, $clubOrDay);
    }

    public function clubScheduleForDate(CodeClub $club, Carbon|string $date): ?ClubSchedule
    {
        $date = Carbon::parse($date)->startOfDay();

        if ($club->hasScheduleRows()) {
            $schedules = $club->relationLoaded('schedules')
                ? $club->schedules
                : $club->schedules()->get();

            return $schedules->first(
                fn (ClubSchedule $schedule) => $this->dayOfWeekMatches($date, $schedule->day_of_week)
            );
        }

        if ($club->day_of_week && $this->dayOfWeekMatches($date, $club->day_of_week)) {
            return null;
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public function clubScheduleDays(CodeClub $club): array
    {
        if ($club->hasScheduleRows()) {
            $schedules = $club->relationLoaded('schedules')
                ? $club->schedules
                : $club->schedules()->get();

            return $schedules->pluck('day_of_week')->filter()->values()->all();
        }

        return $club->day_of_week ? [$club->day_of_week] : [];
    }

    public function nextClubSessionDate(CodeClub|string|null $clubOrDay, Carbon|string|null $from = null): ?Carbon
    {
        if ($clubOrDay instanceof CodeClub) {
            $days = $this->clubScheduleDays($clubOrDay);
            if ($days === []) {
                return null;
            }

            $cursor = Carbon::parse($from ?? now())->startOfDay();

            for ($i = 0; $i < 14; $i++) {
                foreach ($days as $day) {
                    if ($this->dayOfWeekMatches($cursor, $day)) {
                        return $cursor->copy();
                    }
                }

                $cursor->addDay();
            }

            return null;
        }

        if (! $clubOrDay) {
            return null;
        }

        $cursor = Carbon::parse($from ?? now())->startOfDay();

        for ($i = 0; $i < 7; $i++) {
            if ($this->dayOfWeekMatches($cursor, $clubOrDay)) {
                return $cursor->copy();
            }

            $cursor->addDay();
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public function clubSessionDates(CodeClub|string|null $clubOrDay, int $count = 4, Carbon|string|null $until = null): array
    {
        if ($count < 1) {
            return [];
        }

        if ($clubOrDay instanceof CodeClub) {
            $days = $this->clubScheduleDays($clubOrDay);
            if ($days === []) {
                return [];
            }

            $cursor = Carbon::parse($until ?? now())->startOfDay();
            $dates = [];

            for ($i = 0; $i < 366 && count($dates) < $count; $i++) {
                foreach ($days as $day) {
                    if ($this->dayOfWeekMatches($cursor, $day)) {
                        $dates[] = $cursor->toDateString();
                        break;
                    }
                }

                $cursor->subDay();
            }

            return $dates;
        }

        if (! $clubOrDay) {
            return [];
        }

        $cursor = Carbon::parse($until ?? now())->startOfDay();
        $dates = [];

        for ($i = 0; $i < 366 && count($dates) < $count; $i++) {
            if ($this->dayOfWeekMatches($cursor, $clubOrDay)) {
                $dates[] = $cursor->toDateString();
            }

            $cursor->subDay();
        }

        return $dates;
    }

    public function userFacilitatesClubOnDate(User $user, CodeClub $club, Carbon|string $date): bool
    {
        if ($user->isAdmin() || $user->isSupervisor()) {
            return $this->clubMeetsOnDate($club, $date);
        }

        if (! $this->clubMeetsOnDate($club, $date)) {
            return false;
        }

        $schedule = $this->clubScheduleForDate($club, $date);

        if ($schedule?->instructor_id) {
            return (int) $schedule->instructor_id === (int) $user->id;
        }

        return in_array((int) $club->id, $user->clubFacilitatorClubIds(), true);
    }

    /**
     * Students absent for consecutive club sessions (default: 3+).
     *
     * @param  array<int>  $clubIds
     * @return \Illuminate\Support\Collection<int, array{club: CodeClub, profile: StudentProfile, missed_sessions: int}>
     */
    public function clubRetentionAlerts(array $clubIds, int $threshold = 3): Collection
    {
        if ($clubIds === []) {
            return collect();
        }

        $clubs = CodeClub::query()->whereIn('id', $clubIds)->with('schedules')->get(['id', 'name', 'day_of_week']);
        $alerts = collect();

        foreach ($clubs as $club) {
            $sessionDates = $this->clubSessionDates($club, max($threshold + 1, 4));
            if ($sessionDates === []) {
                continue;
            }

            $userIds = CodeClubMembership::where('code_club_id', $club->id)
                ->where('status', 'active')
                ->pluck('student_id');

            $profiles = StudentProfile::query()
                ->whereIn('user_id', $userIds)
                ->where('is_active', true)
                ->get();

            $records = StudentAttendance::query()
                ->where('club_id', $club->id)
                ->whereNull('course_id')
                ->whereIn('attendance_date', $sessionDates)
                ->whereIn('student_profile_id', $profiles->pluck('id'))
                ->get()
                ->groupBy('student_profile_id');

            foreach ($profiles as $profile) {
                $missed = 0;
                $studentRecords = $records->get($profile->id) ?? collect();

                foreach ($sessionDates as $date) {
                    $record = $studentRecords->first(
                        fn ($row) => Carbon::parse($row->attendance_date)->toDateString() === $date
                    );
                    $status = $record?->status;

                    if (in_array($status, ['present', 'late'], true)) {
                        break;
                    }

                    $missed++;
                }

                if ($missed >= $threshold) {
                    $alerts->push([
                        'club' => $club,
                        'profile' => $profile,
                        'missed_sessions' => $missed,
                    ]);
                }
            }
        }

        return $alerts->sortByDesc('missed_sessions')->values();
    }

    private function dayOfWeekMatches(Carbon $date, string $dayOfWeek): bool
    {
        $target = strtolower(trim($dayOfWeek));
        $full = strtolower($date->format('l'));
        $short = strtolower($date->format('D'));

        return $full === $target
            || $short === $target
            || str_starts_with($full, $target)
            || str_starts_with($target, $short);
    }
}
