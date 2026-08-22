<?php

namespace App\Services\Reports;

use App\Models\CodeClub;
use App\Models\CodeClubTermReportDraft;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\QuizAttempt;
use App\Models\StudentAttendance;
use App\Models\StudentLessonProgress;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CodeClubTermReportService
{
    /**
     * @return array<string, mixed>
     */
    public function build(CodeClub $club, User $student, ?Carbon $from = null, ?Carbon $to = null, ?string $termKey = null): array
    {
        $from = $from?->copy()->startOfDay();
        $to = $to?->copy()->endOfDay();
        $termKey = $termKey ?: $this->defaultTermKey($from, $to);

        $draft = CodeClubTermReportDraft::query()
            ->where('code_club_id', $club->id)
            ->where('student_id', $student->id)
            ->where('term_key', $termKey)
            ->first();

        if ($draft) {
            if ($draft->period_start) {
                $from = $draft->period_start->copy()->startOfDay();
            }
            if ($draft->period_end) {
                $to = $draft->period_end->copy()->endOfDay();
            }
        }

        $enrollments = $this->studentEnrollments($club, $student);
        $tracks = $this->buildTracks($enrollments, $student, $from, $to, $draft);
        $attendance = $this->attendanceStats($club, $student, $from, $to);
        $skills = $this->skillsFromTracks($tracks);
        $overallScore = $this->overallScoreFromTracks($tracks, $attendance);

        $auto = [
            'tracks' => $tracks,
            'attendance' => $attendance,
            'skills' => $skills,
            'overall_score' => $overallScore,
        ];

        [$tracks, $attendance, $skills, $overallScore] = $this->applyMetricsOverrides(
            $tracks,
            $attendance,
            $skills,
            $overallScore,
            $draft?->metrics_overrides
        );

        $overallLabel = $draft?->overall_label
            ?: $this->performanceLabel($overallScore);

        $profile = $student->studentProfile;
        $termLabel = $draft?->term_label
            ?: (config('codeclub-reports.default_term_label') ?: $this->defaultTermLabel($from, $to));

        $behavior = $this->normalizeBehavior($draft?->behavior);
        $quote = config('codeclub-reports.inspirational_quote', []);

        return [
            'generated_at' => now()->toDateTimeString(),
            'term_key' => $termKey,
            'term_label' => $termLabel,
            'period' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
                'label' => $this->periodLabel($from, $to),
            ],
            'branding' => $this->brandingPayload(),
            'club' => [
                'id' => $club->id,
                'name' => $club->name,
                'schedule_summary' => $club->schedule_label,
                'status' => $club->status,
            ],
            'school' => [
                'id' => $club->school?->id,
                'name' => $club->school?->name ?? '—',
                'code' => $club->school?->code,
                'address' => $club->school?->address,
            ],
            'student' => [
                'id' => $student->id,
                'name' => $profile?->full_name ?? $student->name,
                'email' => $student->email,
                'student_id' => $profile?->student_id,
                'class_level' => $profile?->class_grade,
                'photo_url' => $student->profile_photo_url,
                'school_name' => $profile?->school?->name ?? $club->school?->name,
            ],
            'summary' => $draft?->summary ?: $this->defaultSummary($student, $tracks, $overallLabel),
            'overall' => [
                'score' => $overallScore,
                'label' => $overallLabel,
            ],
            'attendance' => $attendance,
            'tracks' => $tracks,
            'skills' => $skills,
            'behavior' => $behavior,
            'instructor_comment' => $draft?->instructor_comment
                ?: $this->defaultInstructorComment($student, $overallLabel),
            'achievements' => $this->listOrDefault($draft?->achievements, $this->defaultAchievements($tracks)),
            'improvements' => $this->listOrDefault($draft?->improvements, []),
            'goals' => $this->listOrDefault($draft?->goals, $this->defaultGoals($tracks)),
            'quote' => [
                'text' => $quote['text'] ?? 'Keep building, keep curious — every project is progress.',
                'author' => $quote['author'] ?? null,
            ],
            'instructor' => [
                'name' => $this->instructorName($club),
            ],
            'auto' => $auto,
            'has_metric_overrides' => is_array($draft?->metrics_overrides) && $draft->metrics_overrides !== [],
            'performance_metrics' => $this->buildPerformanceMetrics(
                (float) $overallScore,
                $attendance['rate'] !== null ? (float) $attendance['rate'] : null,
                $this->averageQuizAcrossTracks($tracks),
                $this->assignmentRateFromTracks($tracks, (float) $overallScore),
                is_array($draft?->metrics_overrides['performance_metrics'] ?? null)
                    ? $draft->metrics_overrides['performance_metrics']
                    : null,
            ),
            'courses' => collect($tracks)->map(fn (array $t) => [
                'course_id' => $t['course_id'],
                'title' => $t['title'],
                'progress_percent' => $t['progress_percent'],
                'lessons_completed' => $t['lessons_completed'],
                'lessons_total' => $t['lessons_total'],
                'quiz_average' => $t['quiz_average'],
                'projects_completed' => $t['projects_count'],
                'status' => $t['enrolled'] ? 'active' : 'not_enrolled',
            ])->values()->all(),
            'quizzes' => $this->quizSummaryFromTracks($tracks),
            'projects' => collect($tracks)->flatMap(fn (array $t) => $t['projects'])->values()->all(),
            'highlights' => [
                'best_course' => collect($tracks)->where('enrolled', true)->sortByDesc('progress_percent')->first()['title'] ?? null,
                'quiz_average' => $this->averageQuizAcrossTracks($tracks),
                'projects_count' => collect($tracks)->sum('projects_count'),
                'attendance_rate' => $attendance['rate'],
            ],
            'narrative' => [
                'intro' => $draft?->summary ?: $this->defaultSummary($student, $tracks, $overallLabel),
                'strengths' => $this->listOrDefault($draft?->achievements, $this->defaultAchievements($tracks)),
                'growth' => $this->listOrDefault($draft?->improvements, [
                    'Continue practicing consistently between club sessions.',
                ]),
                'closing' => $draft?->instructor_comment
                    ?: $this->defaultInstructorComment($student, $overallLabel),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSchoolSummary(CodeClub $club, ?Carbon $from = null, ?Carbon $to = null, ?string $termKey = null): array
    {
        $termKey = $termKey ?: $this->defaultTermKey($from, $to);
        $members = $club->activeMemberships()->with('student.studentProfile')->get();

        $payloads = [];
        foreach ($members as $membership) {
            if (! $membership->student) {
                continue;
            }
            $payloads[] = $this->build($club, $membership->student, $from, $to, $termKey);
        }

        $avgAttendance = collect($payloads)->avg(fn ($p) => (float) ($p['attendance']['rate'] ?? 0));
        $avgOverall = collect($payloads)->avg(fn ($p) => (float) ($p['overall']['score'] ?? 0));
        $projectsTotal = collect($payloads)->sum(fn ($p) => (int) ($p['highlights']['projects_count'] ?? 0));

        $trackAverages = [];
        foreach (array_keys(config('codeclub-reports.tracks', [])) as $key) {
            $scores = collect($payloads)
                ->map(fn ($p) => collect($p['tracks'])->firstWhere('key', $key))
                ->filter(fn ($t) => $t && ($t['enrolled'] ?? false))
                ->pluck('progress_percent');
            $trackAverages[$key] = [
                'key' => $key,
                'label' => config("codeclub-reports.tracks.{$key}.label"),
                'color' => config("codeclub-reports.tracks.{$key}.color"),
                'average' => $scores->isEmpty() ? null : (int) round($scores->avg()),
                'enrolled_count' => $scores->count(),
            ];
        }

        $topPerformers = collect($payloads)
            ->sortByDesc(fn ($p) => (float) ($p['overall']['score'] ?? 0))
            ->take(5)
            ->map(fn ($p) => [
                'name' => $p['student']['name'],
                'score' => $p['overall']['score'],
                'label' => $p['overall']['label'],
                'attendance' => $p['attendance']['rate'],
            ])
            ->values()
            ->all();

        $sample = $payloads[0] ?? null;

        return [
            'generated_at' => now()->toDateTimeString(),
            'term_key' => $termKey,
            'term_label' => $sample['term_label'] ?? $this->defaultTermLabel($from, $to),
            'period' => $sample['period'] ?? [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
                'label' => $this->periodLabel($from, $to),
            ],
            'branding' => $this->brandingPayload(),
            'club' => [
                'id' => $club->id,
                'name' => $club->name,
                'schedule_summary' => $club->schedule_label,
                'status' => $club->status,
            ],
            'school' => [
                'id' => $club->school?->id,
                'name' => $club->school?->name ?? '—',
                'code' => $club->school?->code,
                'address' => $club->school?->address,
            ],
            'totals' => [
                'students' => count($payloads),
                'active' => collect($payloads)->filter(fn ($p) => (float) ($p['overall']['score'] ?? 0) > 0)->count(),
                'avg_attendance' => $avgAttendance !== null ? (int) round($avgAttendance) : 0,
                'avg_overall' => $avgOverall !== null ? (int) round($avgOverall) : 0,
                'projects' => $projectsTotal,
            ],
            'track_averages' => array_values($trackAverages),
            'top_performers' => $topPerformers,
            'instructor' => [
                'name' => $this->instructorName($club),
            ],
        ];
    }

    public function defaultTermKey(?Carbon $from, ?Carbon $to): string
    {
        $year = ($to ?? $from ?? now())->year;
        $month = ($to ?? $from ?? now())->month;
        $term = $month <= 4 ? 'T1' : ($month <= 8 ? 'T2' : 'T3');

        return "{$year}-{$term}";
    }

    /**
     * @param  list<array<string, mixed>>  $tracks
     * @param  array{present: int, total: int, rate: int|null}  $attendance
     * @param  array<string, int>  $skills
     * @return array{0: list<array<string, mixed>>, 1: array{present: int, total: int, rate: int|null}, 2: array<string, int>, 3: int}
     */
    public function applyMetricsOverrides(
        array $tracks,
        array $attendance,
        array $skills,
        int $overallScore,
        mixed $overrides,
    ): array {
        if (! is_array($overrides) || $overrides === []) {
            return [$tracks, $attendance, $skills, $overallScore];
        }

        $trackOverrides = is_array($overrides['tracks'] ?? null) ? $overrides['tracks'] : [];

        foreach ($tracks as $i => $track) {
            $key = $track['key'] ?? null;
            if (! $key || ! is_array($trackOverrides[$key] ?? null)) {
                continue;
            }

            $tracks[$i] = $this->mergeTrackOverride($track, $trackOverrides[$key]);
        }

        if (is_array($overrides['attendance'] ?? null)) {
            $att = $overrides['attendance'];
            $present = array_key_exists('present', $att) && $att['present'] !== null && $att['present'] !== ''
                ? (int) $att['present']
                : $attendance['present'];
            $total = array_key_exists('total', $att) && $att['total'] !== null && $att['total'] !== ''
                ? (int) $att['total']
                : $attendance['total'];
            $rate = array_key_exists('rate', $att) && $att['rate'] !== null && $att['rate'] !== ''
                ? (int) $att['rate']
                : ($total > 0 ? (int) round(($present / max(1, $total)) * 100) : $attendance['rate']);

            $attendance = [
                'present' => max(0, $present),
                'total' => max(0, $total),
                'rate' => $rate !== null ? max(0, min(100, $rate)) : null,
            ];
        }

        if (is_array($overrides['skills'] ?? null)) {
            foreach ($overrides['skills'] as $skillKey => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $skills[$skillKey] = max(0, min(100, (int) $value));
            }
        }

        if (array_key_exists('overall_score', $overrides) && $overrides['overall_score'] !== null && $overrides['overall_score'] !== '') {
            $overallScore = max(0, min(100, (int) $overrides['overall_score']));
        } else {
            $overallScore = $this->overallScoreFromTracks($tracks, $attendance);
        }

        return [$tracks, $attendance, $skills, $overallScore];
    }

    /**
     * @param  array<string, float|int|string|null>|null  $overrides  keyed by metric key => score
     * @return list<array{key: string, label: string, score: float, grade: string, color: string, auto_score: float}>
     */
    public function buildPerformanceMetrics(
        float $overallScore,
        ?float $attendanceRate,
        ?float $quizAverage,
        float $assignmentRate,
        ?array $overrides = null,
    ): array {
        $metricsLimit = (int) config('codeclub-reports.report_metrics_limit', 10);
        $overrides = is_array($overrides) ? $overrides : [];

        $sourceValue = function (string $source) use ($overallScore, $attendanceRate, $quizAverage, $assignmentRate): float {
            $val = match ($source) {
                'attendance' => (float) ($attendanceRate ?? 0),
                'quiz' => (float) ($quizAverage ?? 0),
                'assignments' => $assignmentRate,
                default => $overallScore,
            };

            return $val > 0 ? $val : $overallScore;
        };

        return collect(config('codeclub-reports.performance_metrics', []))
            ->map(function (array $def) use ($sourceValue, $overrides) {
                $key = (string) ($def['key'] ?? Str::slug($def['label'] ?? 'metric', '_'));
                $autoScore = round(max(0, min(100, $sourceValue($def['source'] ?? 'overall') + (float) ($def['offset'] ?? 0))), 1);
                $score = $autoScore;

                if (array_key_exists($key, $overrides) && $overrides[$key] !== null && $overrides[$key] !== '') {
                    $score = round(max(0, min(100, (float) $overrides[$key])), 1);
                }

                $grade = $this->metricGrade($score);

                return [
                    'key' => $key,
                    'label' => $def['label'] ?? Str::headline($key),
                    'score' => $score,
                    'grade' => $grade,
                    'color' => in_array($grade, ['A+', 'A'], true) ? 'blue' : 'orange',
                    'auto_score' => $autoScore,
                ];
            })
            ->sortByDesc('score')
            ->take($metricsLimit)
            ->values()
            ->all();
    }

    public function metricGrade(float $score): string
    {
        foreach (config('codeclub-reports.metric_grade_scale', []) as $band) {
            if ($score >= ($band['min'] ?? 0)) {
                return (string) ($band['grade'] ?? 'D');
            }
        }

        return 'D';
    }

    /**
     * @param  list<array<string, mixed>>  $tracks
     */
    public function assignmentRateFromTracks(array $tracks, float $fallbackOverall): float
    {
        $completed = (int) collect($tracks)->sum('lessons_completed');
        $total = (int) collect($tracks)->sum('lessons_total');

        if ($total <= 0) {
            return $fallbackOverall;
        }

        return round(($completed / $total) * 100, 1);
    }

    /**
     * @param  array<string, mixed>  $track
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    protected function mergeTrackOverride(array $track, array $override): array
    {
        $markComplete = filter_var($override['mark_complete'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $forceEnrolled = filter_var($override['force_enrolled'] ?? false, FILTER_VALIDATE_BOOLEAN)
            || $markComplete
            || ($override['progress_percent'] ?? null) !== null
            || ($override['lessons_completed'] ?? null) !== null;

        if ($forceEnrolled) {
            $track['enrolled'] = true;
        }

        if (array_key_exists('lessons_total', $override) && $override['lessons_total'] !== null && $override['lessons_total'] !== '') {
            $track['lessons_total'] = max(0, (int) $override['lessons_total']);
        }

        if (array_key_exists('lessons_completed', $override) && $override['lessons_completed'] !== null && $override['lessons_completed'] !== '') {
            $track['lessons_completed'] = max(0, (int) $override['lessons_completed']);
        }

        if ($markComplete) {
            $track['enrolled'] = true;
            $total = max((int) ($track['lessons_total'] ?? 0), (int) ($track['lessons_completed'] ?? 0), 1);
            $track['lessons_total'] = (int) ($track['lessons_total'] ?? 0) > 0 ? (int) $track['lessons_total'] : $total;
            $track['lessons_completed'] = $track['lessons_total'];
            $track['progress_percent'] = 100;
        } elseif (array_key_exists('progress_percent', $override) && $override['progress_percent'] !== null && $override['progress_percent'] !== '') {
            $track['progress_percent'] = max(0, min(100, (int) $override['progress_percent']));
            $track['enrolled'] = true;
        }

        if (array_key_exists('quiz_average', $override) && $override['quiz_average'] !== null && $override['quiz_average'] !== '') {
            $track['quiz_average'] = max(0, min(100, (int) $override['quiz_average']));
        }

        if (is_array($override['projects'] ?? null)) {
            $projects = [];
            foreach ($override['projects'] as $item) {
                if (is_string($item) && trim($item) !== '') {
                    $projects[] = ['title' => trim($item), 'type' => 'project'];
                } elseif (is_array($item) && ! empty($item['title'])) {
                    $projects[] = [
                        'title' => (string) $item['title'],
                        'type' => (string) ($item['type'] ?? 'project'),
                    ];
                }
            }
            $track['projects'] = $projects;
            $track['projects_count'] = count($projects);
        } elseif (array_key_exists('projects_count', $override) && $override['projects_count'] !== null && $override['projects_count'] !== '') {
            $track['projects_count'] = max(0, (int) $override['projects_count']);
        }

        if (is_array($override['skills_gained'] ?? null)) {
            $track['skills_gained'] = array_values(array_filter(array_map(
                fn ($item) => is_string($item) ? trim($item) : null,
                $override['skills_gained']
            )));
        }

        $progress = (int) ($track['progress_percent'] ?? 0);
        if ($track['enrolled'] ?? false) {
            $track['status_label'] = $progress >= 80 ? 'Strong' : ($progress >= 50 ? 'On track' : 'Building');
        }

        $track['overridden'] = true;

        return $track;
    }

    /**
     * @return Collection<int, CourseEnrollment>
     */
    protected function studentEnrollments(CodeClub $club, User $student): Collection
    {
        $query = CourseEnrollment::query()
            ->where('user_id', $student->id)
            ->with(['course.modules.lessons.quizzes']);

        $schoolCourseIds = $club->school?->courses()->pluck('courses.id');
        if ($schoolCourseIds && $schoolCourseIds->isNotEmpty()) {
            $scoped = (clone $query)->whereIn('course_id', $schoolCourseIds)->get();
            if ($scoped->isNotEmpty()) {
                return $scoped;
            }
        }

        return $query->get();
    }

    /**
     * @return array<string, mixed>
     */
    protected function brandingPayload(): array
    {
        $brand = config('codeclub-reports.brand', []);
        $contact = config('codeclub-reports.contact', []);

        return [
            'primary' => $brand['navy'] ?? $brand['blue'] ?? '#0f172a',
            'accent' => $brand['orange'] ?? '#f97316',
            'blue' => $brand['blue_bright'] ?? '#1e40af',
            'green' => $brand['green'] ?? '#16a34a',
            'muted' => $brand['gray'] ?? '#64748b',
            'soft' => $brand['gray_light'] ?? '#f1f5f9',
            'org_name' => SystemSetting::get('app_name')
                ?: config('codeclub-reports.institution_label', 'Code Academy Uganda'),
            'org_tagline' => config('codeclub-reports.tagline', 'CODE TODAY. CHANGE TOMORROW.'),
            'partner_label' => config('codeclub-reports.partner_label', 'In Partnership with'),
            'operated_by' => config('codeclub-reports.operated_by_label', 'Code Genius Academy'),
            'footer_contact' => trim(implode(' · ', array_filter([
                $contact['phone'] ?? null,
                $contact['email'] ?? null,
            ]))),
            'footer_web' => $contact['website'] ?? '',
            'footer_address' => $contact['address'] ?? '',
            'logo_path' => $this->resolveReportLogoPath(),
        ];
    }

    protected function resolveReportLogoPath(): ?string
    {
        $candidates = array_filter([
            SystemSetting::get('logo'),
            SystemSetting::get('logo_dark'),
            config('codeclub-reports.logo_path'),
        ]);

        foreach ($candidates as $relative) {
            $relative = ltrim((string) $relative, '/\\');
            if ($relative === '') {
                continue;
            }

            $paths = [
                storage_path('app/public/'.$relative),
                public_path('storage/'.$relative),
                public_path($relative),
                $relative, // absolute path already
            ];

            foreach ($paths as $path) {
                if (is_string($path) && is_file($path)) {
                    return $path;
                }
            }
        }

        foreach ([
            public_path('images/code-academy-logo.png'),
            public_path('images/logo.png'),
            public_path('logo.png'),
        ] as $fallback) {
            if (is_file($fallback)) {
                return $fallback;
            }
        }

        return null;
    }

    protected function instructorName(CodeClub $club): string
    {
        $club->loadMissing('activeInstructors.instructor');

        $primary = $club->activeInstructors->first(
            fn ($row) => in_array(strtolower((string) $row->role), ['primary', 'lead', 'head'], true)
        );

        return $primary?->instructor?->name
            ?? $club->activeInstructors->first()?->instructor?->name
            ?? 'Club Facilitator';
    }

    /**
     * @param  Collection<int, CourseEnrollment>  $enrollments
     * @return list<array<string, mixed>>
     */
    protected function buildTracks(Collection $enrollments, User $student, ?Carbon $from, ?Carbon $to, ?CodeClubTermReportDraft $draft): array
    {
        $trackConfig = config('codeclub-reports.tracks', []);
        $notes = is_array($draft?->track_notes) ? $draft->track_notes : [];
        $tracks = [];

        foreach ($trackConfig as $key => $meta) {
            $enrollment = $this->matchEnrollment($enrollments, $meta['keywords'] ?? []);
            $trackNotes = is_array($notes[$key] ?? null) ? $notes[$key] : [];

            if (! $enrollment || ! $enrollment->course) {
                $tracks[] = [
                    'key' => $key,
                    'title' => $meta['label'] ?? Str::headline($key),
                    'color' => $meta['color'] ?? '#64748B',
                    'enrolled' => false,
                    'course_id' => null,
                    'progress_percent' => null,
                    'lessons_completed' => 0,
                    'lessons_total' => 0,
                    'quiz_average' => null,
                    'projects_count' => 0,
                    'projects' => [],
                    'quizzes' => [],
                    'skills_gained' => [],
                    'strengths' => $trackNotes['strengths'] ?? null,
                    'next_focus' => $trackNotes['next_focus'] ?? null,
                    'status_label' => 'Not enrolled',
                ];
                continue;
            }

            $course = $enrollment->course;
            $lessonIds = $course->modules->flatMap->lessons->pluck('id');
            $lessonsTotal = $lessonIds->count();
            $completedQuery = StudentLessonProgress::query()
                ->where('user_id', $student->id)
                ->where(function ($q) {
                    $q->where('status', 'completed')->orWhereNotNull('completed_at');
                })
                ->whereIn('lesson_id', $lessonIds);
            if ($from) {
                $completedQuery->where('completed_at', '>=', $from);
            }
            if ($to) {
                $completedQuery->where('completed_at', '<=', $to);
            }
            $lessonsCompleted = (clone $completedQuery)->count();
            $progress = $lessonsTotal > 0
                ? (int) round(($lessonsCompleted / $lessonsTotal) * 100)
                : (int) ($enrollment->progress_percentage ?? 0);

            $quizIds = $course->modules->flatMap->lessons->flatMap->quizzes->pluck('id');
            $quizAttempts = QuizAttempt::query()
                ->where('user_id', $student->id)
                ->whereIn('quiz_id', $quizIds)
                ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
                ->with('quiz')
                ->get();

            $quizAverage = $quizAttempts->isEmpty()
                ? null
                : (int) round($quizAttempts->avg(fn ($a) => (float) ($a->score ?? 0)));

            $quizzes = $quizAttempts
                ->groupBy('quiz_id')
                ->map(function ($attempts) {
                    $best = $attempts->sortByDesc(fn ($a) => (float) ($a->score ?? 0))->first();

                    return [
                        'title' => $best?->quiz?->title ?? 'Quiz',
                        'score' => (int) round((float) ($best?->score ?? 0)),
                        'attempts' => $attempts->count(),
                    ];
                })
                ->values()
                ->all();

            $projects = $this->projectsForTrack($key, $course, $student, $from, $to);
            $skillsGained = $this->skillsGainedForTrack($key, $progress, $projects);

            $tracks[] = [
                'key' => $key,
                'title' => $meta['label'] ?? $course->title,
                'color' => $meta['color'] ?? '#64748B',
                'enrolled' => true,
                'course_id' => $course->id,
                'progress_percent' => $progress,
                'lessons_completed' => $lessonsCompleted,
                'lessons_total' => $lessonsTotal,
                'quiz_average' => $quizAverage,
                'projects_count' => count($projects),
                'projects' => $projects,
                'quizzes' => $quizzes,
                'skills_gained' => $skillsGained,
                'strengths' => $trackNotes['strengths'] ?? $this->defaultTrackStrength($key, $progress),
                'next_focus' => $trackNotes['next_focus'] ?? $this->defaultTrackFocus($key, $progress),
                'status_label' => $progress >= 80 ? 'Strong' : ($progress >= 50 ? 'On track' : 'Building'),
            ];
        }

        return $tracks;
    }

    /**
     * @param  Collection<int, CourseEnrollment>  $enrollments
     * @param  list<string>  $keywords
     */
    protected function matchEnrollment(Collection $enrollments, array $keywords): ?CourseEnrollment
    {
        if ($keywords === []) {
            return null;
        }

        return $enrollments->first(function (CourseEnrollment $enrollment) use ($keywords) {
            $course = $enrollment->course;
            if (! $course) {
                return false;
            }

            $haystack = Str::lower(trim(
                $course->title.' '.implode(' ', (array) ($course->tags ?? []))
            ));

            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($haystack, Str::lower($keyword))) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * @return list<array{title: string, type: string}>
     */
    protected function projectsForTrack(string $key, Course $course, User $student, ?Carbon $from, ?Carbon $to): array
    {
        $lessons = $course->modules->flatMap->lessons;
        $completedIds = StudentLessonProgress::query()
            ->where('user_id', $student->id)
            ->where(function ($q) {
                $q->where('status', 'completed')->orWhereNotNull('completed_at');
            })
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->when($from, fn ($q) => $q->where(function ($inner) use ($from) {
                $inner->where('completed_at', '>=', $from)
                    ->orWhere(function ($fallback) use ($from) {
                        $fallback->whereNull('completed_at')->where('updated_at', '>=', $from);
                    });
            }))
            ->when($to, fn ($q) => $q->where(function ($inner) use ($to) {
                $inner->where('completed_at', '<=', $to)
                    ->orWhere(function ($fallback) use ($to) {
                        $fallback->whereNull('completed_at')->where('updated_at', '<=', $to);
                    });
            }))
            ->pluck('lesson_id')
            ->all();

        return $lessons
            ->filter(function (Lesson $lesson) use ($key, $completedIds) {
                if (! in_array($lesson->id, $completedIds, true)) {
                    return false;
                }
                if ($key === 'scratch' && ! empty($lesson->scratch_project_id)) {
                    return true;
                }

                $type = Str::lower((string) ($lesson->content_type ?? ''));
                $title = Str::lower((string) $lesson->title);

                return str_contains($type, 'project')
                    || str_contains($title, 'project')
                    || str_contains($title, 'build')
                    || str_contains($title, 'challenge');
            })
            ->take(6)
            ->map(fn (Lesson $lesson) => [
                'title' => $lesson->title,
                'type' => $lesson->content_type ?: 'project',
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{title: string, type: string}>  $projects
     * @return list<string>
     */
    protected function skillsGainedForTrack(string $key, int $progress, array $projects): array
    {
        $base = match ($key) {
            'scratch' => ['Sequences', 'Loops', 'Events', 'Sprites'],
            'robotics' => ['Sensors', 'Motors', 'Logic', 'Hardware'],
            'ai_ml' => ['Data basics', 'Pattern recognition', 'Ethics', 'Models'],
            default => ['Problem solving', 'Creativity'],
        };

        $count = $progress >= 80 ? 4 : ($progress >= 50 ? 3 : ($progress >= 20 || count($projects) > 0 ? 2 : 1));

        return array_slice($base, 0, $count);
    }

    /**
     * @param  list<array<string, mixed>>  $tracks
     * @return array<string, int>
     */
    protected function skillsFromTracks(array $tracks): array
    {
        $keys = array_keys(config('codeclub-reports.skill_keys', [
            'logical_thinking' => 'Logical Thinking',
            'problem_solving' => 'Problem Solving',
            'creativity' => 'Creativity',
            'teamwork' => 'Teamwork',
            'technical_skills' => 'Technical Skills',
        ]));
        $enrolled = collect($tracks)->where('enrolled', true);
        $avg = $enrolled->avg('progress_percent') ?? 0;

        $scores = [];
        foreach ($keys as $i => $skill) {
            $jitter = (($i % 3) - 1) * 4;
            $scores[$skill] = (int) max(20, min(100, round($avg + $jitter)));
        }

        return $scores;
    }

    /**
     * @param  mixed  $behavior
     * @return array<string, int>
     */
    protected function normalizeBehavior(mixed $behavior): array
    {
        $keys = array_keys(config('codeclub-reports.behavior_keys', [
            'participation' => 'Class Participation',
            'collaboration' => 'Collaboration',
            'initiative' => 'Initiative',
            'responsibility' => 'Responsibility',
        ]));
        $input = is_array($behavior) ? $behavior : [];
        $defaults = CodeClubTermReportDraft::defaultBehavior();
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = max(1, min(5, (int) ($input[$key] ?? $defaults[$key] ?? 4)));
        }

        return $out;
    }

    /**
     * @param  list<array<string, mixed>>  $tracks
     * @param  array{present: int, total: int, rate: int|null}  $attendance
     */
    protected function overallScoreFromTracks(array $tracks, array $attendance): int
    {
        $progressScores = collect($tracks)
            ->where('enrolled', true)
            ->pluck('progress_percent')
            ->filter(fn ($v) => $v !== null);

        $quizScores = collect($tracks)
            ->where('enrolled', true)
            ->pluck('quiz_average')
            ->filter(fn ($v) => $v !== null);

        $parts = [];
        if ($progressScores->isNotEmpty()) {
            $parts[] = $progressScores->avg();
        }
        if ($quizScores->isNotEmpty()) {
            $parts[] = $quizScores->avg();
        }
        if ($attendance['rate'] !== null) {
            $parts[] = $attendance['rate'];
        }

        if ($parts === []) {
            return 0;
        }

        return (int) round(array_sum($parts) / count($parts));
    }

    /**
     * @param  list<array<string, mixed>>  $tracks
     * @return list<array{title: string, score: int|null, attempts: int}>
     */
    protected function quizSummaryFromTracks(array $tracks): array
    {
        return collect($tracks)
            ->flatMap(fn (array $t) => $t['quizzes'] ?? [])
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $tracks
     */
    protected function averageQuizAcrossTracks(array $tracks): ?int
    {
        $scores = collect($tracks)
            ->where('enrolled', true)
            ->pluck('quiz_average')
            ->filter(fn ($v) => $v !== null);

        return $scores->isEmpty() ? null : (int) round($scores->avg());
    }

    /**
     * @return array{present: int, total: int, rate: int|null}
     */
    protected function attendanceStats(CodeClub $club, User $student, ?Carbon $from, ?Carbon $to): array
    {
        $profileId = $student->studentProfile?->id;
        if (! $profileId) {
            return ['present' => 0, 'total' => 0, 'rate' => null];
        }

        $query = StudentAttendance::query()
            ->where('student_profile_id', $profileId)
            ->where('club_id', $club->id);

        if ($from) {
            $query->whereDate('attendance_date', '>=', $from->toDateString());
        }
        if ($to) {
            $query->whereDate('attendance_date', '<=', $to->toDateString());
        }

        $rows = $query->get(['status']);
        $total = $rows->count();
        $present = $rows->whereIn('status', ['present', 'late'])->count();

        return [
            'present' => $present,
            'total' => $total,
            'rate' => $total > 0 ? (int) round(($present / $total) * 100) : null,
        ];
    }

    protected function performanceLabel(int $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 80 => 'Very Good',
            $score >= 70 => 'Good',
            $score >= 55 => 'Satisfactory',
            $score > 0 => 'Needs Support',
            default => 'Getting Started',
        };
    }

    protected function defaultTermLabel(?Carbon $from, ?Carbon $to): string
    {
        $year = ($to ?? $from ?? now())->year;
        $month = ($to ?? $from ?? now())->month;
        $term = $month <= 4 ? 'Term 1' : ($month <= 8 ? 'Term 2' : 'Term 3');

        return "{$term} {$year}";
    }

    protected function periodLabel(?Carbon $from, ?Carbon $to): ?string
    {
        if ($from && $to) {
            return $from->format('M j, Y').' – '.$to->format('M j, Y');
        }
        if ($from) {
            return 'From '.$from->format('M j, Y');
        }
        if ($to) {
            return 'Through '.$to->format('M j, Y');
        }

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $tracks
     */
    protected function defaultSummary(User $student, array $tracks, string $overallLabel): string
    {
        $first = Str::of($student->name)->before(' ')->toString() ?: $student->name;
        $enrolled = collect($tracks)->where('enrolled', true)->count();

        return "{$first} demonstrated {$overallLabel} performance across {$enrolled} Code Club track"
            .($enrolled === 1 ? '' : 's')
            .' this term, showing steady growth in digital skills and creative problem-solving.';
    }

    protected function defaultInstructorComment(User $student, string $overallLabel): string
    {
        $first = Str::of($student->name)->before(' ')->toString() ?: $student->name;

        return "{$first} has been a valued member of the club this term ({$overallLabel}). "
            .'With continued practice and curiosity, they are well placed for the next term.';
    }

    /**
     * @param  list<array<string, mixed>>  $tracks
     * @return list<string>
     */
    protected function defaultAchievements(array $tracks): array
    {
        $items = [];
        foreach ($tracks as $track) {
            if (! ($track['enrolled'] ?? false)) {
                continue;
            }
            if (($track['projects_count'] ?? 0) > 0) {
                $items[] = "Completed {$track['projects_count']} project(s) in {$track['title']}";
            }
            if (($track['progress_percent'] ?? 0) >= 70) {
                $items[] = "Strong progress in {$track['title']} ({$track['progress_percent']}%)";
            }
        }

        return array_slice($items ?: ['Active participation in Code Club sessions'], 0, 4);
    }

    /**
     * @param  list<array<string, mixed>>  $tracks
     * @return list<string>
     */
    protected function defaultGoals(array $tracks): array
    {
        $goals = [];
        foreach ($tracks as $track) {
            if (! ($track['enrolled'] ?? false)) {
                continue;
            }
            if (($track['progress_percent'] ?? 0) < 80) {
                $goals[] = "Advance further in {$track['title']}";
            }
        }

        return array_slice($goals ?: ['Complete next milestone projects', 'Maintain strong attendance'], 0, 3);
    }

    protected function defaultTrackStrength(string $key, int $progress): string
    {
        if ($progress >= 70) {
            return match ($key) {
                'scratch' => 'Confident with blocks, events, and creative storytelling.',
                'robotics' => 'Shows solid hardware intuition and debugging persistence.',
                'ai_ml' => 'Grasps core AI ideas and applies them thoughtfully.',
                default => 'Consistent effort and curiosity.',
            };
        }

        return match ($key) {
            'scratch' => 'Building foundation in sequences and sprites.',
            'robotics' => 'Developing comfort with kits and basic logic.',
            'ai_ml' => 'Exploring AI concepts with growing confidence.',
            default => 'Developing core skills steadily.',
        };
    }

    protected function defaultTrackFocus(string $key, int $progress): string
    {
        return match ($key) {
            'scratch' => $progress >= 70 ? 'Independent project design and remixing.' : 'Practice loops, conditionals, and finishing projects.',
            'robotics' => $progress >= 70 ? 'Multi-sensor challenges and team builds.' : 'Sensor input, motors, and stepwise builds.',
            'ai_ml' => $progress >= 70 ? 'Real-world mini-projects and ethics discussions.' : 'Data basics and guided model activities.',
            default => 'Keep practicing between sessions.',
        };
    }

    /**
     * @param  mixed  $value
     * @param  list<string>  $default
     * @return list<string>
     */
    protected function listOrDefault(mixed $value, array $default): array
    {
        if (! is_array($value) || $value === []) {
            return $default;
        }

        return array_values(array_filter(array_map(
            fn ($item) => is_string($item) ? trim($item) : null,
            $value
        )));
    }
}
