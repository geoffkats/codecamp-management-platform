<?php

namespace App\Livewire\Dashboard;

use App\Models\CourseEnrollment;
use App\Models\Notification;
use App\Models\UserPoint;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CodeClubStudentDashboard extends Component
{
    public function mount(): void
    {
        abort_unless(config('features.code_club', false), 404);
        abort_unless(Auth::user()->isCodeClubStudent(), 403);
    }

    public function markNotificationAsRead(int $notificationId): void
    {
        Notification::where('user_id', Auth::id())->findOrFail($notificationId)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function render()
    {
        $user = Auth::user()->load(['studentProfile', 'activeCodeClubMembership.club.school', 'points']);

        $club = $user->currentCodeClub();
        $attendanceService = app(AttendanceService::class);

        $recentAttendance = collect();
        $upcomingSession = null;
        $clubLeaderboard = collect();

        if ($club) {
            $club->load('schedules');

            $recentAttendance = $user->studentAttendances()
                ->where('club_id', $club->id)
                ->whereNull('course_id')
                ->orderByDesc('attendance_date')
                ->take(5)
                ->get();

            $nextDate = $attendanceService->nextClubSessionDate($club);
            if ($nextDate) {
                $upcomingSession = [
                    'date' => $nextDate,
                    'is_today' => $nextDate->isToday(),
                    'schedule_label' => $club->schedule_label,
                ];
            }

            $memberIds = $club->activeMemberships()->pluck('student_id');

            $clubLeaderboard = UserPoint::query()
                ->with('user')
                ->whereHas('user', fn ($q) => $q->whereIn('id', $memberIds))
                ->orderByDesc('total_points')
                ->take(5)
                ->get();
        }

        $enrollments = CourseEnrollment::where('user_id', $user->id)
            ->with('course')
            ->orderByDesc('enrolled_at')
            ->get();

        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $points = $user->points ?? new UserPoint(['total_points' => 0, 'level' => 1]);

        return view('livewire.dashboard.codeclub-student-dashboard', compact(
            'user',
            'club',
            'enrollments',
            'notifications',
            'points',
            'recentAttendance',
            'upcomingSession',
            'clubLeaderboard',
        ));
    }
}
