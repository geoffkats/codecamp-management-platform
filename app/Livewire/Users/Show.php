<?php

namespace App\Livewire\Users;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ActivityLog;
use App\Models\AssessmentAttempt;
use App\Models\AssignmentSubmission;
use App\Models\DailyChallengeAttempt;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Models\UserProgress;
use App\Models\VideoProgress;
use App\Support\LevelSystem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Show extends Component
{
    use WithPagination;

    public User $user;

    protected $paginationTheme = 'tailwind';
    
    // Password reset modal
    public $showResetModal = false;
    public $newPassword = null;

    // Points override modal
    public $showPointsModal = false;
    public $pointsForm = [
        'total_points' => 0,
        'level' => 1,
        'points_to_next_level' => null,
        'xp_multiplier' => null,
        'multiplier_expires_at' => null,
        'multiplier_reason' => null,
    ];

    // Learning journey filters
    public ?string $journeyDateFrom = null;
    public ?string $journeyDateTo = null;
    public string $journeyCourseId = 'all';

    private int $cardSectionPerPage = 8;
    private int $journeySectionPerPage = 15;

    public function mount(User $user)
    {
        $this->user = $user->load(['roles', 'points', 'badges', 'courses', 'enrollments.course']);
        
        // Authorization check
        if (Auth::id() !== $user->id && !Auth::user()->hasAnyRole(['admin'])) {
            abort(403);
        }
    }

    public function render()
    {
        // Get user statistics
        $stats = [
            'courses_created' => $this->user->courses()->count(),
            'courses_enrolled' => $this->user->enrollments()->count(),
            'courses_completed' => $this->user->enrollments()->whereNotNull('completed_at')->count(),
            'badges_earned' => $this->user->badges()->count(),
            'total_points' => $this->user->points->total_points ?? 0,
            'level' => LevelSystem::levelForXp($this->user->points->total_points ?? 0),
            'rank_name' => LevelSystem::rankName($this->user->points->total_points ?? 0),
            'lessons_completed' => $this->user->enrollments()->sum('lessons_completed'),
            'average_score' => $this->user->enrollments()->avg('average_quiz_score') ?? 0,
        ];

        // Paginated profile activity sections
        $recentActivity = [
            'recent_courses' => $this->user->courses()->withCount('enrollments')->latest()->paginate($this->cardSectionPerPage, ['*'], 'coursesPage'),
            'recent_enrollments' => $this->user->enrollments()->with('course')->latest()->paginate($this->cardSectionPerPage, ['*'], 'enrollmentsPage'),
            'recent_badges' => $this->user->badges()->latest('user_badges.earned_at')->paginate($this->cardSectionPerPage, ['*'], 'badgesPage'),
        ];

        // Get leaderboard position
        $leaderboardPosition = \App\Models\UserPoint::where('total_points', '>', $stats['total_points'])->count() + 1;
        $totalUsers = \App\Models\UserPoint::where('total_points', '>', 0)->count();

        $journeyData = $this->buildJourneyData(false);

        $courseFilterOptions = $this->user->enrollments()
            ->with('course:id,title')
            ->get()
            ->pluck('course')
            ->filter()
            ->unique('id')
            ->sortBy('title')
            ->values();

        return view('livewire.users.show', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'leaderboardPosition' => $leaderboardPosition,
            'totalUsers' => $totalUsers,
            'courseJourney' => $journeyData['courseJourney'],
            'lessonProgress' => $journeyData['lessonProgress'],
            'videoProgress' => $journeyData['videoProgress'],
            'challengeAttempts' => $journeyData['challengeAttempts'],
            'assessmentAttempts' => $journeyData['assessmentAttempts'],
            'assignmentSubmissions' => $journeyData['assignmentSubmissions'],
            'attendanceRecords' => $journeyData['attendanceRecords'],
            'journeyStats' => $journeyData['journeyStats'],
            'learningTimeline' => $journeyData['learningTimeline'],
            'courseFilterOptions' => $courseFilterOptions,
        ]);
    }

    public function resetJourneyFilters(): void
    {
        $this->journeyDateFrom = null;
        $this->journeyDateTo = null;
        $this->journeyCourseId = 'all';
        $this->resetJourneyPages();
    }

    public function updatingJourneyDateFrom(): void
    {
        $this->resetJourneyPages();
    }

    public function updatingJourneyDateTo(): void
    {
        $this->resetJourneyPages();
    }

    public function updatingJourneyCourseId(): void
    {
        $this->resetJourneyPages();
    }

    public function exportJourneyCsv()
    {
        $journeyData = $this->buildJourneyData(true);
        $user = $this->user;
        $journeyDateFrom = $this->journeyDateFrom;
        $journeyDateTo = $this->journeyDateTo;
        $journeyCourseId = $this->journeyCourseId;
        $filename = 'student-learning-journey-' . $user->id . '-' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($journeyData, $user, $journeyDateFrom, $journeyDateTo, $journeyCourseId) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Student Learning Journey Report']);
            fputcsv($out, ['Student', $user->name]);
            fputcsv($out, ['Email', $user->email]);
            fputcsv($out, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($out, ['Date From', $journeyDateFrom ?: 'All']);
            fputcsv($out, ['Date To', $journeyDateTo ?: 'All']);
            fputcsv($out, ['Course Filter', $journeyCourseId === 'all' ? 'All Courses' : ('Course ID ' . $journeyCourseId)]);
            fputcsv($out, []);

            fputcsv($out, ['Overview']);
            foreach ($journeyData['journeyStats'] as $key => $value) {
                fputcsv($out, [str_replace('_', ' ', ucfirst((string) $key)), $value]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Course Journey']);
            fputcsv($out, ['Course', 'Progress %', 'Lessons Completed', 'Quizzes Completed', 'Avg Quiz Score', 'Enrolled At', 'Completed At']);
            foreach ($journeyData['courseJourney'] as $item) {
                fputcsv($out, [
                    $item->course?->title,
                    $item->progress_percentage,
                    $item->lessons_completed,
                    $item->quizzes_completed,
                    $item->average_quiz_score,
                    optional($item->enrolled_at)->format('Y-m-d H:i:s'),
                    optional($item->completed_at)->format('Y-m-d H:i:s'),
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Lesson Progress']);
            fputcsv($out, ['Lesson', 'Course', 'Type', 'Score', 'Points', 'Time Spent (min)', 'Completed At']);
            foreach ($journeyData['lessonProgress'] as $item) {
                fputcsv($out, [
                    $item->lesson?->title,
                    $item->course?->title,
                    $item->type,
                    $item->score,
                    $item->points_earned,
                    $item->time_spent,
                    optional($item->completed_at ?? $item->created_at)->format('Y-m-d H:i:s'),
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Daily Challenges']);
            fputcsv($out, ['Challenge', 'Course', 'Category', 'Difficulty', 'Completed', 'Points Earned', 'Attempted At']);
            foreach ($journeyData['challengeAttempts'] as $item) {
                fputcsv($out, [
                    $item->challenge?->title,
                    $item->challenge?->course?->title,
                    $item->challenge?->category,
                    $item->challenge?->difficulty_level,
                    $item->is_completed ? 'Yes' : 'No',
                    $item->points_earned,
                    optional($item->attempted_at ?? $item->created_at)->format('Y-m-d H:i:s'),
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Assessments']);
            fputcsv($out, ['Assessment', 'Course', 'Type', 'Score', 'Passed', 'Started At', 'Completed At']);
            foreach ($journeyData['assessmentAttempts'] as $item) {
                fputcsv($out, [
                    $item->assessment?->title,
                    $item->assessment?->course?->title,
                    $item->assessment?->assessment_type,
                    $item->score,
                    $item->is_passed ? 'Yes' : 'No',
                    optional($item->started_at)->format('Y-m-d H:i:s'),
                    optional($item->completed_at)->format('Y-m-d H:i:s'),
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Assignments']);
            fputcsv($out, ['Assignment', 'Course', 'Status', 'Points Earned', 'Submitted At', 'Graded At']);
            foreach ($journeyData['assignmentSubmissions'] as $item) {
                fputcsv($out, [
                    $item->assignment?->title,
                    $item->assignment?->course?->title,
                    $item->status,
                    $item->points_earned,
                    optional($item->submitted_at ?? $item->created_at)->format('Y-m-d H:i:s'),
                    optional($item->graded_at)->format('Y-m-d H:i:s'),
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Attendance']);
            fputcsv($out, ['Date', 'Status', 'Course', 'Clock In', 'Clock Out', 'Reason']);
            foreach ($journeyData['attendanceRecords'] as $item) {
                fputcsv($out, [
                    optional($item->attendance_date)->format('Y-m-d'),
                    $item->status,
                    $item->course?->title,
                    optional($item->clock_in)->format('H:i'),
                    optional($item->clock_out)->format('H:i'),
                    $item->reason,
                ]);
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportJourneyPdf()
    {
        $journeyData = $this->buildJourneyData(true);

        $pdf = Pdf::loadView('reports.student-learning-journey-pdf', [
            'user' => $this->user,
            'filters' => [
                'date_from' => $this->journeyDateFrom,
                'date_to' => $this->journeyDateTo,
                'course_id' => $this->journeyCourseId,
            ],
            'courseJourney' => $journeyData['courseJourney'],
            'lessonProgress' => $journeyData['lessonProgress'],
            'challengeAttempts' => $journeyData['challengeAttempts'],
            'assessmentAttempts' => $journeyData['assessmentAttempts'],
            'assignmentSubmissions' => $journeyData['assignmentSubmissions'],
            'attendanceRecords' => $journeyData['attendanceRecords'],
            'journeyStats' => $journeyData['journeyStats'],
            'learningTimeline' => $journeyData['learningTimeline'],
        ])->setPaper('a4', 'portrait');

        $filename = 'student-learning-journey-' . $this->user->id . '-' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    private function buildJourneyData(bool $forExport = false): array
    {
        $courseJourneyQuery = $this->user->enrollments()
            ->with('course:id,title')
            ->orderByDesc('enrolled_at');

        if ($this->journeyCourseId !== 'all') {
            $courseJourneyQuery->where('course_id', (int) $this->journeyCourseId);
        }

        if ($this->journeyDateFrom) {
            $courseJourneyQuery->whereDate('enrolled_at', '>=', $this->journeyDateFrom);
        }

        if ($this->journeyDateTo) {
            $courseJourneyQuery->whereDate('enrolled_at', '<=', $this->journeyDateTo);
        }

        $courseJourney = $forExport
            ? $courseJourneyQuery->get()
            : $courseJourneyQuery->paginate($this->journeySectionPerPage, ['*'], 'courseJourneyPage');

        $lessonProgressQuery = UserProgress::query()
            ->where('user_id', $this->user->id)
            ->whereNotNull('lesson_id')
            ->with([
                'course:id,title',
                'lesson:id,title,module_id,course_id',
                'lesson.module:id,title',
            ])
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at');

        if ($this->journeyCourseId !== 'all') {
            $lessonProgressQuery->where('course_id', (int) $this->journeyCourseId);
        }

        if ($this->journeyDateFrom) {
            $lessonProgressQuery->whereDate('created_at', '>=', $this->journeyDateFrom);
        }

        if ($this->journeyDateTo) {
            $lessonProgressQuery->whereDate('created_at', '<=', $this->journeyDateTo);
        }

        $lessonProgress = $forExport
            ? $lessonProgressQuery->get()
            : $lessonProgressQuery->paginate($this->journeySectionPerPage, ['*'], 'lessonProgressPage');

        $videoProgressQuery = VideoProgress::query()
            ->where('user_id', $this->user->id)
            ->with([
                'lesson:id,title,module_id,course_id',
                'lesson.course:id,title',
                'lesson.module:id,title',
            ])
            ->orderByDesc('last_watched_at');

        if ($this->journeyCourseId !== 'all') {
            $videoProgressQuery->whereHas('lesson', fn ($q) => $q->where('course_id', (int) $this->journeyCourseId));
        }

        if ($this->journeyDateFrom) {
            $videoProgressQuery->whereDate('created_at', '>=', $this->journeyDateFrom);
        }

        if ($this->journeyDateTo) {
            $videoProgressQuery->whereDate('created_at', '<=', $this->journeyDateTo);
        }

        $videoProgress = $forExport ? $videoProgressQuery->get() : collect();

        $challengeAttemptsQuery = DailyChallengeAttempt::query()
            ->where('user_id', $this->user->id)
            ->with([
                'challenge:id,title,course_id,difficulty_level,category,reward_points,date',
                'challenge.course:id,title',
            ])
            ->orderByDesc('attempted_at');

        if ($this->journeyCourseId !== 'all') {
            $challengeAttemptsQuery->whereHas('challenge', fn ($q) => $q->where('course_id', (int) $this->journeyCourseId));
        }

        if ($this->journeyDateFrom) {
            $challengeAttemptsQuery->whereDate('attempted_at', '>=', $this->journeyDateFrom);
        }

        if ($this->journeyDateTo) {
            $challengeAttemptsQuery->whereDate('attempted_at', '<=', $this->journeyDateTo);
        }

        $challengeAttempts = $forExport
            ? $challengeAttemptsQuery->get()
            : $challengeAttemptsQuery->paginate($this->journeySectionPerPage, ['*'], 'challengeAttemptsPage');

        $assessmentAttemptsQuery = AssessmentAttempt::query()
            ->where('user_id', $this->user->id)
            ->with([
                'assessment:id,title,course_id,assessment_type,passing_score',
                'assessment.course:id,title',
            ])
            ->orderByDesc('completed_at')
            ->orderByDesc('started_at');

        if ($this->journeyCourseId !== 'all') {
            $assessmentAttemptsQuery->whereHas('assessment', fn ($q) => $q->where('course_id', (int) $this->journeyCourseId));
        }

        if ($this->journeyDateFrom) {
            $assessmentAttemptsQuery->whereDate('started_at', '>=', $this->journeyDateFrom);
        }

        if ($this->journeyDateTo) {
            $assessmentAttemptsQuery->whereDate('started_at', '<=', $this->journeyDateTo);
        }

        $assessmentAttempts = $forExport
            ? $assessmentAttemptsQuery->get()
            : $assessmentAttemptsQuery->paginate($this->journeySectionPerPage, ['*'], 'assessmentAttemptsPage');

        $assignmentSubmissionsQuery = AssignmentSubmission::query()
            ->where('user_id', $this->user->id)
            ->with([
                'assignment:id,title,course_id,due_date,max_points',
                'assignment.course:id,title',
            ])
            ->orderByDesc('submitted_at');

        if ($this->journeyCourseId !== 'all') {
            $assignmentSubmissionsQuery->whereHas('assignment', fn ($q) => $q->where('course_id', (int) $this->journeyCourseId));
        }

        if ($this->journeyDateFrom) {
            $assignmentSubmissionsQuery->whereDate('submitted_at', '>=', $this->journeyDateFrom);
        }

        if ($this->journeyDateTo) {
            $assignmentSubmissionsQuery->whereDate('submitted_at', '<=', $this->journeyDateTo);
        }

        $assignmentSubmissions = $forExport
            ? $assignmentSubmissionsQuery->get()
            : $assignmentSubmissionsQuery->paginate($this->journeySectionPerPage, ['*'], 'assignmentSubmissionsPage');

        $attendanceRecords = collect();
        if ($this->user->studentProfile) {
            $attendanceQuery = StudentAttendance::query()
                ->where('student_profile_id', $this->user->studentProfile->id)
                ->with('course:id,title')
                ->orderByDesc('attendance_date');

            if ($this->journeyCourseId !== 'all') {
                $attendanceQuery->where('course_id', (int) $this->journeyCourseId);
            }

            if ($this->journeyDateFrom) {
                $attendanceQuery->whereDate('attendance_date', '>=', $this->journeyDateFrom);
            }

            if ($this->journeyDateTo) {
                $attendanceQuery->whereDate('attendance_date', '<=', $this->journeyDateTo);
            }

            $attendanceRecords = $forExport
                ? $attendanceQuery->get()
                : $attendanceQuery->paginate($this->journeySectionPerPage, ['*'], 'attendanceRecordsPage');
        } elseif (!$forExport) {
            $attendanceRecords = new LengthAwarePaginator(
                collect(),
                0,
                $this->journeySectionPerPage,
                1,
                ['path' => request()->url(), 'pageName' => 'attendanceRecordsPage']
            );
        }

        $activityLogsQuery = ActivityLog::query()
            ->where('user_id', $this->user->id)
            ->orderByDesc('created_at');

        if ($this->journeyDateFrom) {
            $activityLogsQuery->whereDate('created_at', '>=', $this->journeyDateFrom);
        }

        if ($this->journeyDateTo) {
            $activityLogsQuery->whereDate('created_at', '<=', $this->journeyDateTo);
        }

        if (!$forExport) {
            $activityLogsQuery->limit(200);
        }

        $activityLogs = $activityLogsQuery->get();

        $challengeCompleted = (clone $challengeAttemptsQuery)->where('is_completed', true)->count();
        $assessmentPassed = (clone $assessmentAttemptsQuery)->where('is_passed', true)->count();
        $assessmentTotal = (clone $assessmentAttemptsQuery)->count();
        $attendancePresent = 0;
        $attendanceTotal = 0;

        if ($this->user->studentProfile) {
            $attendanceStatsQuery = StudentAttendance::query()->where('student_profile_id', $this->user->studentProfile->id);

            if ($this->journeyCourseId !== 'all') {
                $attendanceStatsQuery->where('course_id', (int) $this->journeyCourseId);
            }
            if ($this->journeyDateFrom) {
                $attendanceStatsQuery->whereDate('attendance_date', '>=', $this->journeyDateFrom);
            }
            if ($this->journeyDateTo) {
                $attendanceStatsQuery->whereDate('attendance_date', '<=', $this->journeyDateTo);
            }

            $attendanceTotal = (clone $attendanceStatsQuery)->count();
            $attendancePresent = (clone $attendanceStatsQuery)->whereIn('status', ['present', 'late'])->count();
        }

        $journeyStats = [
            'courses_tracked' => (clone $courseJourneyQuery)->count(),
            'lesson_events' => (clone $lessonProgressQuery)->count(),
            'lessons_covered' => (clone $lessonProgressQuery)->distinct('lesson_id')->count('lesson_id'),
            'video_lessons_completed' => (clone $videoProgressQuery)
                ->where('is_completed', true)
                ->distinct('lesson_id')
                ->count('lesson_id'),
            'challenge_attempts' => (clone $challengeAttemptsQuery)->count(),
            'challenge_completed' => $challengeCompleted,
            'assessment_attempts' => $assessmentTotal,
            'assessment_pass_rate' => $assessmentTotal > 0 ? round(($assessmentPassed / $assessmentTotal) * 100, 1) : 0,
            'assignment_submissions' => (clone $assignmentSubmissionsQuery)->count(),
            'assignment_graded' => (clone $assignmentSubmissionsQuery)->whereNotNull('graded_at')->count(),
            'attendance_rate' => $attendanceTotal > 0 ? round(($attendancePresent / $attendanceTotal) * 100, 1) : null,
            'learning_points_from_progress' => (int) ((clone $lessonProgressQuery)->sum('points_earned') + (clone $challengeAttemptsQuery)->sum('points_earned')),
            'time_spent_minutes' => (int) (clone $lessonProgressQuery)->sum('time_spent'),
        ];

        $timelineLessonEvents = (clone $lessonProgressQuery)->limit(120)->get();
        $timelineChallengeEvents = (clone $challengeAttemptsQuery)->limit(120)->get();
        $timelineAssessmentEvents = (clone $assessmentAttemptsQuery)->limit(120)->get();
        $timelineAssignmentEvents = (clone $assignmentSubmissionsQuery)->limit(120)->get();
        $timelineAttendanceEvents = collect();
        if ($this->user->studentProfile) {
            $timelineAttendanceQuery = StudentAttendance::query()
                ->where('student_profile_id', $this->user->studentProfile->id)
                ->with('course:id,title')
                ->orderByDesc('attendance_date');

            if ($this->journeyCourseId !== 'all') {
                $timelineAttendanceQuery->where('course_id', (int) $this->journeyCourseId);
            }
            if ($this->journeyDateFrom) {
                $timelineAttendanceQuery->whereDate('attendance_date', '>=', $this->journeyDateFrom);
            }
            if ($this->journeyDateTo) {
                $timelineAttendanceQuery->whereDate('attendance_date', '<=', $this->journeyDateTo);
            }

            $timelineAttendanceEvents = $timelineAttendanceQuery->limit(120)->get();
        }

        $learningTimeline = $this->buildLearningTimeline(
            $timelineLessonEvents,
            $timelineChallengeEvents,
            $timelineAssessmentEvents,
            $timelineAssignmentEvents,
            $timelineAttendanceEvents,
            $activityLogs
        );

        return [
            'courseJourney' => $courseJourney,
            'lessonProgress' => $lessonProgress,
            'videoProgress' => $videoProgress,
            'challengeAttempts' => $challengeAttempts,
            'assessmentAttempts' => $assessmentAttempts,
            'assignmentSubmissions' => $assignmentSubmissions,
            'attendanceRecords' => $attendanceRecords,
            'journeyStats' => $journeyStats,
            'learningTimeline' => $learningTimeline,
        ];
    }

    private function resetJourneyPages(): void
    {
        foreach ([
            'courseJourneyPage',
            'lessonProgressPage',
            'challengeAttemptsPage',
            'assessmentAttemptsPage',
            'assignmentSubmissionsPage',
            'attendanceRecordsPage',
        ] as $pageName) {
            $this->resetPage($pageName);
        }
    }

    private function buildLearningTimeline(
        Collection $lessonProgress,
        Collection $challengeAttempts,
        Collection $assessmentAttempts,
        Collection $assignmentSubmissions,
        Collection $attendanceRecords,
        Collection $activityLogs
    ): Collection {
        $timeline = collect();

        foreach ($lessonProgress as $progress) {
            $timeline->push([
                'at' => $progress->completed_at ?? $progress->created_at,
                'type' => 'lesson',
                'title' => $progress->lesson?->title ?? 'Lesson activity',
                'detail' => ($progress->course?->title ?? 'Course') . ' • ' . strtoupper((string) $progress->type),
            ]);
        }

        foreach ($challengeAttempts as $attempt) {
            $timeline->push([
                'at' => $attempt->completed_at ?? $attempt->attempted_at ?? $attempt->created_at,
                'type' => 'challenge',
                'title' => $attempt->challenge?->title ?? 'Daily challenge',
                'detail' => ($attempt->is_completed ? 'Completed' : 'Attempted') . ' • ' . ($attempt->challenge?->course?->title ?? 'No course'),
            ]);
        }

        foreach ($assessmentAttempts as $attempt) {
            $timeline->push([
                'at' => $attempt->completed_at ?? $attempt->started_at ?? $attempt->created_at,
                'type' => 'assessment',
                'title' => $attempt->assessment?->title ?? 'Assessment',
                'detail' => 'Score ' . number_format((float) $attempt->score, 1) . '% • ' . ($attempt->is_passed ? 'Passed' : 'Not passed'),
            ]);
        }

        foreach ($assignmentSubmissions as $submission) {
            $timeline->push([
                'at' => $submission->submitted_at ?? $submission->created_at,
                'type' => 'assignment',
                'title' => $submission->assignment?->title ?? 'Assignment submission',
                'detail' => ucfirst((string) $submission->status) . ' • ' . ($submission->assignment?->course?->title ?? 'No course'),
            ]);
        }

        foreach ($attendanceRecords as $attendance) {
            $timeline->push([
                'at' => $attendance->attendance_date,
                'type' => 'attendance',
                'title' => 'Attendance marked',
                'detail' => ucfirst((string) $attendance->status) . ' • ' . ($attendance->course?->title ?? 'General attendance'),
            ]);
        }

        foreach ($activityLogs as $log) {
            $timeline->push([
                'at' => $log->created_at,
                'type' => 'system',
                'title' => ucfirst((string) $log->action),
                'detail' => ($log->model_name ?: class_basename((string) $log->model_type)) . ' #' . ($log->model_id ?: '-'),
            ]);
        }

        return $timeline
            ->filter(fn ($item) => !empty($item['at']))
            ->sortByDesc('at')
            ->values();
    }

    public function openResetModal()
    {
        $this->showResetModal = true;
        $this->newPassword = null;
    }

    public function openPointsModal()
    {
        if (! Auth::user()->hasAnyRole(['admin'])) {
            abort(403);
        }

        $points = $this->user->points;
        $info = LevelSystem::info($points->total_points ?? 0);

        $this->pointsForm = [
            'total_points' => $points->total_points ?? 0,
            'level' => $info['level'],
            'points_to_next_level' => $info['xp_to_next_level'],
            'xp_multiplier' => $points->xp_multiplier ?? null,
            'multiplier_expires_at' => $points?->multiplier_expires_at?->format('Y-m-d\TH:i'),
            'multiplier_reason' => $points->multiplier_reason ?? null,
        ];

        $this->showPointsModal = true;
    }

    public function updatedPointsFormTotalPoints($value): void
    {
        $info = LevelSystem::info((int) $value);
        $this->pointsForm['level'] = $info['level'];
        $this->pointsForm['points_to_next_level'] = $info['xp_to_next_level'];
    }

    public function closeResetModal()
    {
        $this->showResetModal = false;
        $this->newPassword = null;
    }

    public function closePointsModal()
    {
        $this->showPointsModal = false;
    }

    public function confirmResetPassword()
    {
        // Generate an easier-to-type password (10 characters: letters and numbers only)
        // Format: 3 letters + 2 numbers + 3 letters + 2 numbers (easier to type and remember)
        $newPassword = strtolower(\Illuminate\Support\Str::random(3)) . 
                       rand(10, 99) . 
                       strtolower(\Illuminate\Support\Str::random(3)) . 
                       rand(10, 99);
        
        // Update user's password
        $this->user->update([
            'password' => Hash::make($newPassword)
        ]);
        
        // Store the new password to display in modal
        $this->newPassword = $newPassword;
        
        // Refresh user data
        $this->user->refresh();
    }

    public function updatePoints()
    {
        if (! Auth::user()->hasAnyRole(['admin'])) {
            abort(403);
        }

        $data = $this->validate([
            'pointsForm.total_points' => 'required|integer|min:0',
            'pointsForm.xp_multiplier' => 'nullable|numeric|min:0',
            'pointsForm.multiplier_expires_at' => 'nullable|date',
            'pointsForm.multiplier_reason' => 'nullable|string|max:255',
        ])['pointsForm'];

        $info = LevelSystem::info((int) $data['total_points']);
        $points = $this->user->points()->firstOrNew([]);

        $points->fill([
            'total_points' => (int) $data['total_points'],
            'level' => $info['level'],
            'points_to_next_level' => $info['xp_to_next_level'],
            'xp_multiplier' => $data['xp_multiplier'] ?: null,
            'multiplier_expires_at' => $data['multiplier_expires_at'] ?: null,
            'multiplier_reason' => $data['multiplier_reason'] ?: null,
        ]);
        $points->save();
        LevelSystem::sync($points);

        $this->user->setRelation('points', $points);
        $this->showPointsModal = false;

        session()->flash('success', 'XP updated — Level '.$info['level'].' · '.$info['name']);
    }
}
