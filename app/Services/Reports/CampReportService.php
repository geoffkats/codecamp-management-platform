<?php

namespace App\Services\Reports;

use App\Models\AssessmentAttempt;
use App\Models\CampContentRevision;
use App\Models\CampEnrollment;
use App\Models\Certificate;
use App\Models\CodeCamp;
use App\Models\CourseEnrollment;
use App\Models\DailyReport;
use App\Models\DailyReportIssue;
use App\Models\StudentAttendance;
use App\Models\StudentProfile;
use App\Models\StudentUniform;
use App\Models\TeacherFeedback;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Pulls together everything that happened in one Code Camp for the end-of-camp report.
 */
class CampReportService
{
    public const AT_RISK_RATE = 60;

    /** Instructor answers like "None", "No challenges", "N/A" that mean nothing went wrong. */
    public const NO_CHALLENGE = '/^(none|no\b|nil|n\/?a\b|nothing|not really|all (was |went )?(good|well|fine))/i';

    /** Keyword patterns used to group instructor challenges and issues into recurring themes. */
    public const CHALLENGE_THEMES = [
        'Laptops and devices' => 'laptop|computer|\bp\.?c\b|charger|mouse|keyboard|screen|device',
        'Internet and power' => 'internet|network|wi-?fi|connectivity|offline|power|electricity',
        'Logins and the online platform' => 'log ?in|account|password|platform|submit|upload|access',
        'Lateness and absence' => '\blate\b|absent|absence|joined after|missed',
        'Difficult content or pace' => 'difficult|hard|struggl|confus|understand|extra time|too fast|pace|pass mark|fail(ed|ing)? (the |to pass )?(quiz|test|assessment)',
        'Lab equipment and materials' => 'breadboard|wire|component|resistor|\bleds?\b|sensor|arduino|stock',
    ];

    public function build(CodeCamp $camp): array
    {
        $start = $camp->start_date->copy()->startOfDay();
        $end = ($camp->end_date ?? now())->copy()->endOfDay();

        $enrollments = CampEnrollment::where('camp_id', $camp->id)->get(['student_id', 'status']);
        $studentIds = $enrollments->pluck('student_id')->unique()->values();
        $profiles = StudentProfile::whereIn('user_id', $studentIds)
            ->get(['id', 'user_id', 'full_name', 'student_id', 'gender', 'class_grade', 'date_of_birth']);

        $attendance = $this->attendance($camp, $profiles);
        $reports = $this->dailyReports($camp, $start, $end);
        $learning = $this->learning($camp, $studentIds, $start, $end);
        $students = $this->studentRows($attendance['byProfile'], $profiles, $learning['byUser']);

        return [
            'camp' => $camp,
            'period' => [
                'start' => $start,
                'end' => $camp->end_date,
                'teaching_days' => count($attendance['days']) ?: $reports['days'],
            ],
            'enrollment' => $this->enrollment($camp, $enrollments, $profiles),
            'attendance' => $attendance['summary'],
            'attendanceDays' => $attendance['days'],
            'students' => $students,
            'studentSummary' => $this->studentSummary($students),
            'instructors' => $reports['instructors'],
            'reports' => $reports['summary'],
            'notes' => $reports['notes'],
            'issues' => $this->issues($reports['ids']),
            'courses' => $this->courses($camp, $reports['sessionsByCourse']),
            'learning' => $learning['summary'],
            'feedback' => $this->feedback($studentIds, $start, $end),
            'revisions' => $this->revisions($camp),
            'uniforms' => $this->uniforms($profiles),
            'generatedAt' => now(),
        ];
    }

    private function enrollment(CodeCamp $camp, Collection $enrollments, Collection $profiles): array
    {
        $byStatus = $enrollments->countBy('status');
        $total = $enrollments->count();
        $gender = $profiles->countBy(fn ($p) => match (strtolower((string) $p->gender)) {
            'male' => 'Boys',
            'female' => 'Girls',
            default => 'Not recorded',
        });
        $ages = $profiles->map(fn ($p) => $p->date_of_birth ? (int) $p->date_of_birth->diffInYears($camp->start_date) : null)->filter(fn ($age) => $age !== null && $age > 2 && $age < 30);

        return [
            'total' => $total,
            'active' => (int) ($byStatus['active'] ?? 0),
            'completed' => (int) ($byStatus['completed'] ?? 0),
            'transferred' => (int) ($byStatus['transferred'] ?? 0),
            'dropped' => (int) ($byStatus['dropped'] ?? 0),
            'capacity' => $camp->max_capacity,
            'fill_rate' => $camp->max_capacity ? min(100, round($total / $camp->max_capacity * 100)) : null,
            'gender' => $gender->sortKeys()->all(),
            'average_age' => $ages->isNotEmpty() ? round($ages->avg(), 1) : null,
            'classes' => $profiles->countBy(fn ($p) => self::classLabel($p->class_grade))->sortDesc()->take(10)->all(),
        ];
    }

    private function attendance(CodeCamp $camp, Collection $profiles): array
    {
        $base = StudentAttendance::whereNull('course_id')->where('camp_id', $camp->id);

        $days = (clone $base)
            ->selectRaw("attendance_date, SUM(status = 'present') as present, SUM(status = 'late') as late, SUM(status = 'absent') as absent")
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get()
            ->map(function ($row) {
                $total = $row->present + $row->late + $row->absent;

                return [
                    'date' => Carbon::parse($row->attendance_date),
                    'present' => (int) $row->present,
                    'late' => (int) $row->late,
                    'absent' => (int) $row->absent,
                    'total' => (int) $total,
                    'rate' => $total ? round(($row->present + $row->late) / $total * 100) : 0,
                ];
            })
            ->values()
            ->all();

        $byProfile = (clone $base)
            ->selectRaw("student_profile_id, SUM(status = 'present') as present, SUM(status = 'late') as late, SUM(status = 'absent') as absent")
            ->groupBy('student_profile_id')
            ->get()
            ->keyBy('student_profile_id');

        $present = array_sum(array_column($days, 'present'));
        $late = array_sum(array_column($days, 'late'));
        $absent = array_sum(array_column($days, 'absent'));
        $records = $present + $late + $absent;
        $ranked = collect($days)->filter(fn ($d) => $d['total'] > 0)->sortBy('rate');

        return [
            'days' => $days,
            'byProfile' => $byProfile,
            'summary' => [
                'records' => $records,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'rate' => $records ? round(($present + $late) / $records * 100, 1) : null,
                'punctuality' => ($present + $late) ? round($present / ($present + $late) * 100, 1) : null,
                'average_daily' => $days ? round(($present + $late) / count($days), 1) : null,
                'best_day' => $ranked->last(),
                'worst_day' => $ranked->count() > 1 ? $ranked->first() : null,
                'students_tracked' => $byProfile->count(),
            ],
        ];
    }

    private function studentRows(Collection $byProfile, Collection $profiles, Collection $scores): array
    {
        return $profiles->map(function ($profile) use ($byProfile, $scores) {
            $row = $byProfile->get($profile->id);
            $present = (int) ($row->present ?? 0);
            $late = (int) ($row->late ?? 0);
            $absent = (int) ($row->absent ?? 0);
            $days = $present + $late + $absent;

            return [
                'name' => $profile->full_name,
                'student_id' => $profile->student_id,
                'class' => self::classLabel($profile->class_grade),
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'days' => $days,
                'rate' => $days ? round(($present + $late) / $days * 100) : null,
                'avg_score' => $scores->get($profile->user_id),
            ];
        })
            ->sortBy([['rate', 'desc'], ['name', 'asc']])
            ->values()
            ->all();
    }

    /** "P.5", "p 5" and "P5" are the same class. */
    private static function classLabel(?string $class): string
    {
        $label = strtoupper(preg_replace('/[\s.]+/', '', (string) $class));

        return $label !== '' ? $label : 'Not recorded';
    }

    private function studentSummary(array $students): array
    {
        $rows = collect($students);
        $recorded = $rows->whereNotNull('rate');

        $bands = [
            ['label' => 'Excellent', 'range' => '90–100%', 'count' => $recorded->where('rate', '>=', 90)->count()],
            ['label' => 'Good', 'range' => '75–89%', 'count' => $recorded->whereBetween('rate', [75, 89.99])->count()],
            ['label' => 'Fair', 'range' => self::AT_RISK_RATE.'–74%', 'count' => $recorded->whereBetween('rate', [self::AT_RISK_RATE, 74.99])->count()],
            ['label' => 'Needs follow-up', 'range' => 'below '.self::AT_RISK_RATE.'%', 'count' => $recorded->where('rate', '<', self::AT_RISK_RATE)->count()],
        ];
        if ($rows->count() > $recorded->count()) {
            $bands[] = ['label' => 'No attendance recorded', 'range' => '–', 'count' => $rows->count() - $recorded->count()];
        }

        $byClass = $recorded->groupBy('class')
            ->map(function (Collection $group, $class) {
                $present = $group->sum('present') + $group->sum('late');
                $days = $group->sum('days');

                return [
                    'class' => $class,
                    'students' => $group->count(),
                    'rate' => $days ? round($present / $days * 100) : null,
                ];
            })
            ->sortByDesc('students')
            ->values()
            ->all();

        return [
            'total' => $rows->count(),
            'bands' => array_map(fn ($band) => $band + [
                'share' => $rows->count() ? round($band['count'] / $rows->count() * 100) : 0,
            ], $bands),
            'perfect' => $recorded->where('absent', 0)->where('late', 0)->count(),
            'follow_up' => $recorded->where('rate', '<', self::AT_RISK_RATE)->sortBy('rate')->values()->all(),
            'by_class' => $byClass,
        ];
    }

    private function reportQuery(CodeCamp $camp, Carbon $start, Carbon $end): Builder
    {
        return DailyReport::query()->where(function ($q) use ($camp, $start, $end) {
            $q->where('camp_id', $camp->id)
                ->orWhere(fn ($legacy) => $legacy->whereNull('camp_id')
                    ->whereBetween('report_date', [$start->toDateString(), $end->toDateString()]));
        });
    }

    private function dailyReports(CodeCamp $camp, Carbon $start, Carbon $end): array
    {
        $reports = $this->reportQuery($camp, $start, $end)
            ->with(['instructor:id,name', 'course:id,title'])
            ->orderBy('report_date')
            ->get();

        $approaches = collect(DailyReport::PEDAGOGICAL_APPROACHES)
            ->map(fn ($meta, $key) => [
                'label' => $meta['label'],
                'count' => $reports->filter(fn ($r) => collect($r->appliedApproaches())->contains('key', $key))->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();

        $clean = fn (?string $text) => trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)));

        $instructors = $reports->groupBy('instructor_id')->map(function (Collection $rows) use ($clean) {
            $latest = $rows->sortByDesc('report_date')->first(fn ($r) => $clean($r->summary) !== '');

            return [
                'latest_summary' => $latest ? Str::limit($clean($latest->summary), 280) : null,
                'challenges' => $rows->map(fn ($r) => $clean($r->challenges))
                    ->filter(fn ($text) => $text !== '' && ! preg_match(self::NO_CHALLENGE, $text))
                    ->unique()
                    ->take(3)
                    ->map(fn ($text) => Str::limit($text, 180))
                    ->values()
                    ->all(),
                'name' => $rows->first()->instructor?->name ?? 'Unknown',
                'reports' => $rows->count(),
                'days' => $rows->pluck('report_date')->map->toDateString()->unique()->count(),
                'courses' => $rows->map(fn ($r) => $r->course?->title)->filter()->unique()->values()->all(),
                'follow_ups' => $rows->where('follow_up_required', true)->count(),
                'approaches' => round($rows->avg(fn ($r) => $r->usedApproachCount()), 1),
                'last_report' => $rows->max('report_date'),
            ];
        })->sortByDesc('reports')->values()->all();

        $problems = $reports->map(fn ($r) => [
            'instructor_id' => $r->instructor_id,
            'text' => trim($clean($r->challenges).' '.$clean($r->issues)),
        ])->filter(fn ($row) => $row['text'] !== '' && ! preg_match(self::NO_CHALLENGE, $row['text']));

        $themes = collect(self::CHALLENGE_THEMES)->map(function ($pattern, $label) use ($problems) {
            $matches = $problems->filter(fn ($row) => preg_match('/'.$pattern.'/i', $row['text']));

            return [
                'label' => $label,
                'reports' => $matches->count(),
                'instructors' => $matches->pluck('instructor_id')->unique()->count(),
                'example' => $matches->isNotEmpty() ? Str::limit($matches->first()['text'], 160) : null,
            ];
        })->where('reports', '>', 0)->sortByDesc('reports')->values()->all();

        return [
            'ids' => $reports->pluck('id'),
            'days' => $reports->pluck('report_date')->map->toDateString()->unique()->count(),
            'instructors' => $instructors,
            'sessionsByCourse' => $reports->groupBy('course_id')->map->count(),
            'summary' => [
                'total' => $reports->count(),
                'follow_ups' => $reports->where('follow_up_required', true)->count(),
                'with_challenges' => $problems->count(),
                'themes' => $themes,
                'approaches' => $approaches,
                'average_approaches' => $reports->isNotEmpty() ? round($reports->avg(fn ($r) => $r->usedApproachCount()), 1) : 0,
            ],
            'notes' => $reports->map(fn ($r) => [
                'date' => $r->report_date,
                'instructor' => $r->instructor?->name,
                'course' => $r->course?->title,
                'summary' => Str::limit($clean($r->summary), 400),
                'challenges' => Str::limit($clean($r->challenges), 300),
                'issues' => Str::limit($clean($r->issues), 300),
                'approaches' => collect($r->appliedApproaches())->pluck('label')->all(),
            ])->values()->all(),
        ];
    }

    private function issues(Collection $reportIds): array
    {
        $issues = DailyReportIssue::whereIn('daily_report_id', $reportIds)->latest()->get();

        return [
            'total' => $issues->count(),
            'open' => $issues->where('status', '!=', 'resolved')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
            'by_severity' => $issues->countBy('severity')->all(),
            'items' => $issues
                ->sortBy(fn ($i) => [$i->status === 'resolved' ? 1 : 0, match ($i->severity) {
                    'critical' => 0, 'high' => 1, default => 2
                }])
                ->take(10)
                ->map(fn ($i) => [
                    'title' => $i->title,
                    'description' => Str::limit((string) $i->description, 160),
                    'severity' => $i->severity,
                    'status' => $i->status,
                ])
                ->values()
                ->all(),
        ];
    }

    private function courses(CodeCamp $camp, Collection $sessionsByCourse): array
    {
        $enrolled = CourseEnrollment::where('camp_id', $camp->id)
            ->with('course:id,title')
            ->get(['course_id', 'progress_percentage', 'completed_at']);

        $courseIds = $enrolled->pluck('course_id')->merge($sessionsByCourse->keys())->unique()->filter();
        $titles = \App\Models\Course::whereIn('id', $courseIds)->pluck('title', 'id');

        return $courseIds->map(function ($courseId) use ($enrolled, $sessionsByCourse, $titles) {
            $rows = $enrolled->where('course_id', $courseId);

            return [
                'title' => $titles[$courseId] ?? 'Course #'.$courseId,
                'sessions' => (int) ($sessionsByCourse[$courseId] ?? 0),
                'students' => $rows->count(),
                'avg_progress' => $rows->isNotEmpty() ? round($rows->avg('progress_percentage')) : null,
                'completed' => $rows->whereNotNull('completed_at')->count(),
            ];
        })->sortByDesc('sessions')->values()->all();
    }

    private function learning(CodeCamp $camp, Collection $studentIds, Carbon $start, Carbon $end): array
    {
        $attempts = AssessmentAttempt::whereIn('user_id', $studentIds)
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$start, $end->copy()->addDays(3)]);

        $percentage = AssessmentAttempt::percentageSql();
        $byUser = (clone $attempts)
            ->selectRaw("user_id, ROUND(AVG({$percentage})) as avg_score")
            ->groupBy('user_id')
            ->pluck('avg_score', 'user_id')
            ->map(fn ($score) => $score === null ? null : (int) $score);

        $count = (clone $attempts)->count();
        $passed = (clone $attempts)->where('is_passed', true)->count();
        $average = (clone $attempts)->selectRaw("AVG({$percentage}) as avg")->value('avg');

        return [
            'byUser' => $byUser,
            'summary' => [
                'attempts' => $count,
                'students_assessed' => $byUser->count(),
                'average_score' => $average !== null ? round((float) $average, 1) : null,
                'pass_rate' => $count ? round($passed / $count * 100, 1) : null,
                'certificates' => Certificate::whereIn('user_id', $studentIds)
                    ->whereBetween('issued_at', [$start, $end->copy()->addDays(14)])
                    ->count(),
            ],
        ];
    }

    private function feedback(Collection $studentIds, Carbon $start, Carbon $end): array
    {
        $feedback = TeacherFeedback::whereIn('student_id', $studentIds)
            ->whereBetween('created_at', [$start, $end->copy()->addDays(3)])
            ->latest()
            ->get(['rating', 'feedback', 'is_anonymous', 'created_at']);

        $rated = $feedback->whereNotNull('rating');

        return [
            'count' => $feedback->count(),
            'average' => $rated->isNotEmpty() ? round($rated->avg('rating'), 1) : null,
            'distribution' => collect([5, 4, 3, 2, 1])->mapWithKeys(fn ($n) => [$n => $rated->where('rating', $n)->count()])->all(),
            'comments' => $feedback
                ->map(fn ($f) => trim(preg_replace('/^Lesson ID:.*?\n/s', '', (string) $f->feedback)))
                ->filter(fn ($text) => mb_strlen($text) >= 8)
                ->take(6)
                ->map(fn ($text) => Str::limit($text, 200))
                ->values()
                ->all(),
        ];
    }

    private function revisions(CodeCamp $camp): array
    {
        $byStatus = CampContentRevision::where('camp_id', $camp->id)->get(['status'])->countBy('status');

        return [
            'total' => $byStatus->sum(),
            'approved' => (int) ($byStatus['approved'] ?? 0),
            'pending' => (int) ($byStatus['pending'] ?? 0),
            'changes_requested' => (int) ($byStatus['changes_requested'] ?? 0),
        ];
    }

    private function uniforms(Collection $profiles): array
    {
        $uniforms = StudentUniform::whereIn('student_profile_id', $profiles->pluck('id'))->get(['student_profile_id', 'paid']);

        return [
            'students' => $uniforms->pluck('student_profile_id')->unique()->count(),
            'total' => $uniforms->count(),
            'paid' => $uniforms->where('paid', true)->count(),
        ];
    }
}
