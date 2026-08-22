<?php

namespace App\Livewire\Admin\Clubs;

use App\Models\ClubSchedule;
use App\Support\TimeOfDay;
use App\Models\CodeClub;
use App\Models\CodeClubInstructor;
use App\Models\CodeClubMembership;
use App\Models\CodeClubTermReportDraft;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\CodeClubStudentImportService;
use App\Services\Reports\CodeClubTermReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Show extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;
    use WithPagination;

    public CodeClub $club;
    public string $activeTab = 'students';

    public bool $isEditing = false;
    public string $editName = '';
    public string $editDescription = '';
    public string $editDayOfWeek = '';
    public string $editSessionStart = '';
    public string $editSessionEnd = '';
    public string $editMaxCapacity = '';
    public string $editStatus = 'active';

    public bool $showAddStudentModal = false;
    public string $studentSearch = '';
    public array $studentResults = [];

    public bool $showAddInstructorModal = false;
    public string $instructorSearch = '';
    public array $instructorResults = [];

    public string $searchStudents = '';
    public string $memberFilter = 'active';
    public string $filterClass = '';

    public const CLASS_FILTER_UNASSIGNED = '__unassigned__';

    public ?int $bulkCourseId = null;

    public bool $canManageSettings = false;

    public $importCsv = null;

    public ?array $importReport = null;

    public bool $showImportPanel = false;

    public string $importDefaultClassGrade = '';

    public bool $importApplyClassToAll = false;

    public array $selectedMembers = [];

    public bool $selectAllMembers = false;

    public bool $showBulkClassModal = false;

    public string $bulkClassGrade = '';

    /** @var array<int, array{id: ?int, day_of_week: string, session_start: string, session_end: string, instructor_id: ?int}> */
    public array $scheduleRows = [];

    public bool $canGenerateReports = false;

    public string $reportTermKey = '';

    public string $reportTermLabel = '';

    public string $reportPeriodStart = '';

    public string $reportPeriodEnd = '';

    public string $reportDefaultComment = '';

    /** @var array<int, array<string, mixed>> */
    public array $reportDraftRows = [];

    public ?int $editingReportStudentId = null;

    public array $reportEditor = [];

    /** @var array<string, mixed> keyed metric key => score */
    public array $bulkPerformanceMetrics = [];

    public string $bulkOverallScore = '';

    public const SCHEDULE_DAYS = [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    ];

    public function mount(CodeClub $club): void
    {
        abort_unless(config('features.code_club', false), 404);
        $this->authorize('view', $club);

        $this->club = $club->load(['school', 'activeInstructors.instructor', 'schedules.instructor']);
        $this->canManageSettings = Auth::user()->isAdmin() || Auth::user()->isSupervisor();
        $this->canGenerateReports = Auth::user()->can('generateReports', $club);
        $this->fillEditForm();
        $this->loadScheduleRows();
        $this->initializeReportTerm();

        CodeClubMembership::query()
            ->where('code_club_id', $this->club->id)
            ->whereDoesntHave('student')
            ->delete();
    }

    public function updatedMemberFilter(): void
    {
        $this->resetPage();
    }

    public function updatedFilterClass(): void
    {
        $this->resetPage();
    }

    public function selectClass(string $class): void
    {
        $this->filterClass = $class;
        $this->resetPage();
    }

    private function fillEditForm(): void
    {
        $this->editName = $this->club->name;
        $this->editDescription = $this->club->description ?? '';
        $this->editDayOfWeek = $this->club->day_of_week ?? '';
        $this->editSessionStart = TimeOfDay::toHi($this->club->session_start) ?? '';
        $this->editSessionEnd = TimeOfDay::toHi($this->club->session_end) ?? '';
        $this->editMaxCapacity = (string) ($this->club->max_capacity ?? '');
        $this->editStatus = $this->club->status;
    }

    private function loadScheduleRows(): void
    {
        $this->scheduleRows = $this->club->schedules->map(fn (ClubSchedule $row) => [
            'id' => $row->id,
            'day_of_week' => $row->day_of_week,
            'session_start' => TimeOfDay::toHi($row->session_start) ?? '',
            'session_end' => TimeOfDay::toHi($row->session_end) ?? '',
            'instructor_id' => $row->instructor_id,
        ])->values()->all();
    }

    public function addScheduleRow(): void
    {
        $this->authorize('update', $this->club);

        $this->scheduleRows[] = [
            'id' => null,
            'day_of_week' => '',
            'session_start' => TimeOfDay::toHi($this->club->session_start) ?? '',
            'session_end' => TimeOfDay::toHi($this->club->session_end) ?? '',
            'instructor_id' => null,
        ];
    }

    public function removeScheduleRow(int $index): void
    {
        $this->authorize('update', $this->club);

        if (! isset($this->scheduleRows[$index])) {
            return;
        }

        unset($this->scheduleRows[$index]);
        $this->scheduleRows = array_values($this->scheduleRows);
    }

    public function saveSchedules(): void
    {
        $this->authorize('update', $this->club);

        $this->validate([
            'scheduleRows' => 'array',
            'scheduleRows.*.day_of_week' => 'required|in:' . implode(',', array_keys(self::SCHEDULE_DAYS)),
            'scheduleRows.*.session_start' => 'nullable|date_format:H:i',
            'scheduleRows.*.session_end' => 'nullable|date_format:H:i',
            'scheduleRows.*.instructor_id' => 'nullable|exists:users,id',
        ]);

        $days = collect($this->scheduleRows)->pluck('day_of_week');
        if ($days->duplicates()->isNotEmpty()) {
            $this->addError('scheduleRows', 'Each day can only appear once in the schedule.');

            return;
        }

        $keepIds = [];

        foreach ($this->scheduleRows as $row) {
            $payload = [
                'day_of_week' => strtolower($row['day_of_week']),
                'session_start' => TimeOfDay::toStorage($row['session_start'] ?? null),
                'session_end' => TimeOfDay::toStorage($row['session_end'] ?? null),
                'instructor_id' => $row['instructor_id'] ?: null,
            ];

            if ($row['id']) {
                ClubSchedule::where('id', $row['id'])
                    ->where('code_club_id', $this->club->id)
                    ->update($payload);
                $keepIds[] = (int) $row['id'];
            } else {
                $created = ClubSchedule::create(array_merge($payload, ['code_club_id' => $this->club->id]));
                $keepIds[] = $created->id;
            }
        }

        ClubSchedule::where('code_club_id', $this->club->id)
            ->whereNotIn('id', $keepIds)
            ->delete();

        $this->club->load('schedules.instructor');
        $this->loadScheduleRows();
        session()->flash('message', 'Schedule saved successfully.');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();

        if ($tab === 'reports' && $this->canGenerateReports) {
            $this->loadReportDraftRows();
            $this->initializeBulkPerformanceMetrics();
        }
    }

    protected function initializeReportTerm(): void
    {
        $service = app(CodeClubTermReportService::class);
        $this->reportTermKey = $service->defaultTermKey(null, null);
        $existing = CodeClubTermReportDraft::query()
            ->where('code_club_id', $this->club->id)
            ->where('term_key', $this->reportTermKey)
            ->orderByDesc('updated_at')
            ->first();

        $this->reportTermLabel = $existing?->term_label
            ?: (config('codeclub-reports.default_term_label') ?: $this->defaultReportTermLabel());
        $this->reportPeriodStart = $existing?->period_start?->toDateString() ?? '';
        $this->reportPeriodEnd = $existing?->period_end?->toDateString() ?? '';
        $this->reportDefaultComment = (string) (config('codeclub-reports.default_instructor_comment') ?? '');
        $this->initializeBulkPerformanceMetrics();
    }

    protected function initializeBulkPerformanceMetrics(): void
    {
        $metrics = [];
        foreach (config('codeclub-reports.performance_metrics', []) as $def) {
            $key = (string) ($def['key'] ?? Str::slug($def['label'] ?? 'metric', '_'));
            $metrics[$key] = [
                'label' => $def['label'] ?? Str::headline($key),
                'value' => $this->bulkPerformanceMetrics[$key]['value'] ?? '',
            ];
        }
        $this->bulkPerformanceMetrics = $metrics;
    }

    protected function defaultReportTermLabel(): string
    {
        $month = now()->month;
        $term = $month <= 4 ? 'Term 1' : ($month <= 8 ? 'Term 2' : 'Term 3');

        return $term.' '.now()->year;
    }

    public function loadReportDraftRows(): void
    {
        if (! $this->canGenerateReports) {
            return;
        }

        $memberships = CodeClubMembership::query()
            ->with(['student.studentProfile'])
            ->where('code_club_id', $this->club->id)
            ->where('status', 'active')
            ->whereHas('student')
            ->orderBy('enrolled_at')
            ->get();

        $drafts = CodeClubTermReportDraft::query()
            ->where('code_club_id', $this->club->id)
            ->where('term_key', $this->reportTermKey)
            ->get()
            ->keyBy('student_id');

        $rows = [];
        foreach ($memberships as $membership) {
            $student = $membership->student;
            if (! $student) {
                continue;
            }
            $draft = $drafts->get($student->id);
            $behavior = is_array($draft?->behavior) ? $draft->behavior : CodeClubTermReportDraft::defaultBehavior();
            $trackNotes = is_array($draft?->track_notes) ? $draft->track_notes : [];
            $metrics = is_array($draft?->metrics_overrides) ? $draft->metrics_overrides : [];

            $rows[$student->id] = [
                'student_id' => $student->id,
                'name' => $student->studentProfile?->full_name ?? $student->name,
                'student_code' => $student->studentProfile?->student_id,
                'class_grade' => $student->studentProfile?->class_grade,
                'summary' => $draft?->summary ?? '',
                'overall_label' => $draft?->overall_label ?? '',
                'instructor_comment' => $draft?->instructor_comment ?? '',
                'achievements_text' => implode("\n", CodeClubTermReportDraft::normalizeList($draft?->achievements)),
                'improvements_text' => implode("\n", CodeClubTermReportDraft::normalizeList($draft?->improvements)),
                'goals_text' => implode("\n", CodeClubTermReportDraft::normalizeList($draft?->goals)),
                'behavior' => $behavior,
                'track_notes' => $trackNotes,
                'metrics_overrides' => $metrics,
                'has_draft' => (bool) $draft,
                'has_metric_overrides' => $metrics !== [],
            ];
        }

        $this->reportDraftRows = $rows;
    }

    public function updatedReportTermKey(): void
    {
        if ($this->activeTab === 'reports') {
            $this->loadReportDraftRows();
        }
    }

    public function saveReportTermSettings(): void
    {
        $this->authorize('generateReports', $this->club);

        $this->validate([
            'reportTermKey' => 'required|string|max:32',
            'reportTermLabel' => 'nullable|string|max:120',
            'reportPeriodStart' => 'nullable|date',
            'reportPeriodEnd' => 'nullable|date|after_or_equal:reportPeriodStart',
            'reportDefaultComment' => 'nullable|string|max:2000',
        ]);

        if ($this->reportDraftRows === []) {
            $this->loadReportDraftRows();
        }

        foreach ($this->reportDraftRows as $studentId => $row) {
            $comment = trim((string) ($row['instructor_comment'] ?? ''));
            if ($comment === '' && trim($this->reportDefaultComment) !== '') {
                $comment = trim($this->reportDefaultComment);
            }

            CodeClubTermReportDraft::updateOrCreate(
                [
                    'code_club_id' => $this->club->id,
                    'student_id' => (int) $studentId,
                    'term_key' => $this->reportTermKey,
                ],
                [
                    'term_label' => $this->reportTermLabel ?: null,
                    'period_start' => $this->reportPeriodStart ?: null,
                    'period_end' => $this->reportPeriodEnd ?: null,
                    'instructor_comment' => $comment !== '' ? $comment : null,
                ]
            );
        }

        $this->loadReportDraftRows();
        session()->flash('message', 'Term settings saved for '.$this->reportTermKey.'.');
    }

    public function openReportEditor(int $studentId): void
    {
        $this->authorize('generateReports', $this->club);

        if (! isset($this->reportDraftRows[$studentId])) {
            $this->loadReportDraftRows();
        }

        if (! isset($this->reportDraftRows[$studentId])) {
            session()->flash('error', 'Student not found on the active roster.');

            return;
        }

        $row = $this->reportDraftRows[$studentId];
        $student = User::with('studentProfile')->find($studentId);
        if (! $student) {
            session()->flash('error', 'Student not found.');

            return;
        }

        $from = $this->reportPeriodStart ? Carbon::parse($this->reportPeriodStart)->startOfDay() : null;
        $to = $this->reportPeriodEnd ? Carbon::parse($this->reportPeriodEnd)->endOfDay() : null;
        $payload = app(CodeClubTermReportService::class)->build(
            $this->club,
            $student,
            $from,
            $to,
            $this->reportTermKey
        );

        $auto = $payload['auto'] ?? [
            'tracks' => $payload['tracks'],
            'attendance' => $payload['attendance'],
            'skills' => $payload['skills'],
            'overall_score' => $payload['overall']['score'] ?? 0,
        ];

        $trackConfig = config('codeclub-reports.tracks', []);
        $tracks = [];
        foreach ($trackConfig as $key => $meta) {
            $effective = collect($payload['tracks'])->firstWhere('key', $key) ?? [];
            $baseline = collect($auto['tracks'] ?? [])->firstWhere('key', $key) ?? [];
            $existingNotes = is_array($row['track_notes'][$key] ?? null) ? $row['track_notes'][$key] : [];
            $existingOverride = is_array(($row['metrics_overrides']['tracks'][$key] ?? null))
                ? $row['metrics_overrides']['tracks'][$key]
                : [];

            $tracks[$key] = [
                'label' => $meta['label'] ?? Str::headline($key),
                'color' => $meta['color'] ?? '#64748b',
                'auto_enrolled' => (bool) ($baseline['enrolled'] ?? false),
                'auto_progress' => $baseline['progress_percent'] ?? null,
                'auto_lessons' => ($baseline['lessons_completed'] ?? 0).'/'.($baseline['lessons_total'] ?? 0),
                'force_enrolled' => (bool) ($existingOverride['force_enrolled'] ?? ($effective['enrolled'] ?? false)),
                'mark_complete' => (bool) ($existingOverride['mark_complete'] ?? false),
                'progress_percent' => $effective['progress_percent'] ?? '',
                'lessons_completed' => $effective['lessons_completed'] ?? 0,
                'lessons_total' => $effective['lessons_total'] ?? 0,
                'quiz_average' => $effective['quiz_average'] ?? '',
                'projects_count' => $effective['projects_count'] ?? 0,
                'projects_text' => collect($effective['projects'] ?? [])->pluck('title')->filter()->implode("\n"),
                'skills_gained_text' => implode("\n", $effective['skills_gained'] ?? []),
                'strengths' => $existingNotes['strengths'] ?? ($effective['strengths'] ?? ''),
                'next_focus' => $existingNotes['next_focus'] ?? ($effective['next_focus'] ?? ''),
            ];
        }

        $skillKeys = config('codeclub-reports.skill_keys', []);
        $skills = [];
        foreach ($skillKeys as $key => $label) {
            $skills[$key] = [
                'label' => $label,
                'auto' => $auto['skills'][$key] ?? null,
                'value' => $payload['skills'][$key] ?? '',
            ];
        }

        $perfOverrides = is_array($row['metrics_overrides']['performance_metrics'] ?? null)
            ? $row['metrics_overrides']['performance_metrics']
            : [];
        $performanceMetrics = [];
        foreach ($payload['performance_metrics'] ?? [] as $metric) {
            $key = $metric['key'];
            $performanceMetrics[$key] = [
                'label' => $metric['label'],
                'auto' => $metric['auto_score'] ?? null,
                'value' => array_key_exists($key, $perfOverrides) ? $perfOverrides[$key] : ($metric['score'] ?? ''),
                'grade' => $metric['grade'] ?? '',
            ];
        }
        // Keep config order for editing (payload is sorted by score)
        $ordered = [];
        foreach (config('codeclub-reports.performance_metrics', []) as $def) {
            $key = (string) ($def['key'] ?? Str::slug($def['label'] ?? 'metric', '_'));
            if (isset($performanceMetrics[$key])) {
                $ordered[$key] = $performanceMetrics[$key];
            } else {
                $ordered[$key] = [
                    'label' => $def['label'] ?? Str::headline($key),
                    'auto' => null,
                    'value' => $perfOverrides[$key] ?? '',
                    'grade' => '',
                ];
            }
        }

        $this->editingReportStudentId = $studentId;
        $this->reportEditor = [
            'summary' => $row['summary'] ?: ($payload['summary'] ?? ''),
            'overall_label' => $row['overall_label'] ?? '',
            'overall_score' => $payload['overall']['score'] ?? '',
            'auto_overall_score' => $auto['overall_score'] ?? null,
            'instructor_comment' => $row['instructor_comment'] ?: ($this->reportDefaultComment ?: ($payload['instructor_comment'] ?? '')),
            'achievements_text' => $row['achievements_text'] ?: implode("\n", $payload['achievements'] ?? []),
            'improvements_text' => $row['improvements_text'] ?? '',
            'goals_text' => $row['goals_text'] ?: implode("\n", $payload['goals'] ?? []),
            'behavior' => $row['behavior'] ?? CodeClubTermReportDraft::defaultBehavior(),
            'attendance_present' => $payload['attendance']['present'] ?? 0,
            'attendance_total' => $payload['attendance']['total'] ?? 0,
            'attendance_rate' => $payload['attendance']['rate'] ?? '',
            'auto_attendance' => $auto['attendance'] ?? null,
            'tracks' => $tracks,
            'skills' => $skills,
            'performance_metrics' => $ordered,
            'override_metrics' => (bool) ($row['has_metric_overrides'] ?? false),
        ];
    }

    public function setEditorPerformanceMetricsTo(int $score = 100): void
    {
        foreach ($this->reportEditor['performance_metrics'] ?? [] as $key => $metric) {
            $this->reportEditor['performance_metrics'][$key]['value'] = $score;
        }
        $this->reportEditor['overall_score'] = $score;
        $this->reportEditor['override_metrics'] = true;
    }

    public function setBulkPerformanceMetricsTo(int $score = 100): void
    {
        foreach ($this->bulkPerformanceMetrics as $key => $metric) {
            $this->bulkPerformanceMetrics[$key]['value'] = $score;
        }
        $this->bulkOverallScore = (string) $score;
    }

    public function applyBulkPerformanceMetrics(): void
    {
        $this->authorize('generateReports', $this->club);

        if ($this->reportTermKey === '') {
            session()->flash('error', 'Set a term key before bulk-filling metrics.');

            return;
        }

        $this->validate([
            'bulkOverallScore' => 'nullable|integer|min:0|max:100',
            'bulkPerformanceMetrics.*.value' => 'nullable|numeric|min:0|max:100',
        ]);

        $metricScores = [];
        foreach ($this->bulkPerformanceMetrics as $key => $metric) {
            $value = $this->nullableFloat($metric['value'] ?? null);
            if ($value !== null) {
                $metricScores[$key] = $value;
            }
        }

        $overall = $this->nullableInt($this->bulkOverallScore);
        if ($metricScores === [] && $overall === null) {
            session()->flash('error', 'Enter at least one metric score (or overall) to apply.');

            return;
        }

        $studentIds = array_keys($this->reportDraftRows);
        if ($studentIds === []) {
            $this->loadReportDraftRows();
            $studentIds = array_keys($this->reportDraftRows);
        }

        $updated = 0;
        foreach ($studentIds as $studentId) {
            $draft = CodeClubTermReportDraft::query()->firstOrNew([
                'code_club_id' => $this->club->id,
                'student_id' => $studentId,
                'term_key' => $this->reportTermKey,
            ]);

            $overrides = is_array($draft->metrics_overrides) ? $draft->metrics_overrides : [];
            $existingPerf = is_array($overrides['performance_metrics'] ?? null) ? $overrides['performance_metrics'] : [];
            $overrides['performance_metrics'] = array_merge($existingPerf, $metricScores);
            if ($overall !== null) {
                $overrides['overall_score'] = $overall;
            }

            $draft->fill([
                'term_label' => $this->reportTermLabel ?: null,
                'period_start' => $this->reportPeriodStart ?: null,
                'period_end' => $this->reportPeriodEnd ?: null,
                'metrics_overrides' => $overrides,
            ]);
            if (! $draft->exists && blank($draft->instructor_comment) && $this->reportDefaultComment !== '') {
                $draft->instructor_comment = $this->reportDefaultComment;
            }
            $draft->save();
            $updated++;
        }

        $this->loadReportDraftRows();
        session()->flash('message', "Applied performance metrics to {$updated} student(s).");
    }

    public function markTrackComplete(string $trackKey): void
    {
        if (! isset($this->reportEditor['tracks'][$trackKey])) {
            return;
        }

        $track = $this->reportEditor['tracks'][$trackKey];
        $total = max(1, (int) ($track['lessons_total'] ?? 0), (int) ($track['lessons_completed'] ?? 0));
        $this->reportEditor['tracks'][$trackKey]['force_enrolled'] = true;
        $this->reportEditor['tracks'][$trackKey]['mark_complete'] = true;
        $this->reportEditor['tracks'][$trackKey]['lessons_total'] = (int) ($track['lessons_total'] ?? 0) > 0
            ? (int) $track['lessons_total']
            : $total;
        $this->reportEditor['tracks'][$trackKey]['lessons_completed'] = $this->reportEditor['tracks'][$trackKey]['lessons_total'];
        $this->reportEditor['tracks'][$trackKey]['progress_percent'] = 100;
        $this->reportEditor['override_metrics'] = true;
    }

    public function markAllTracksComplete(): void
    {
        foreach (array_keys($this->reportEditor['tracks'] ?? []) as $trackKey) {
            $this->markTrackComplete($trackKey);
        }
    }

    public function resetReportMetricsToAuto(): void
    {
        $this->authorize('generateReports', $this->club);

        if (! $this->editingReportStudentId) {
            return;
        }

        $studentId = $this->editingReportStudentId;

        CodeClubTermReportDraft::query()
            ->where('code_club_id', $this->club->id)
            ->where('student_id', $studentId)
            ->where('term_key', $this->reportTermKey)
            ->update(['metrics_overrides' => null]);

        if (isset($this->reportDraftRows[$studentId])) {
            $this->reportDraftRows[$studentId]['metrics_overrides'] = [];
            $this->reportDraftRows[$studentId]['has_metric_overrides'] = false;
        }

        $this->openReportEditor($studentId);
        session()->flash('message', 'Progress metrics reset to LMS auto values.');
    }

    public function closeReportEditor(): void
    {
        $this->editingReportStudentId = null;
        $this->reportEditor = [];
    }

    public function saveReportDraft(): void
    {
        $this->authorize('generateReports', $this->club);

        if (! $this->editingReportStudentId) {
            return;
        }

        $this->validate([
            'reportEditor.summary' => 'nullable|string|max:2000',
            'reportEditor.overall_label' => 'nullable|string|max:64',
            'reportEditor.overall_score' => 'nullable|integer|min:0|max:100',
            'reportEditor.instructor_comment' => 'nullable|string|max:2000',
            'reportEditor.achievements_text' => 'nullable|string|max:3000',
            'reportEditor.improvements_text' => 'nullable|string|max:3000',
            'reportEditor.goals_text' => 'nullable|string|max:3000',
            'reportEditor.attendance_present' => 'nullable|integer|min:0|max:500',
            'reportEditor.attendance_total' => 'nullable|integer|min:0|max:500',
            'reportEditor.attendance_rate' => 'nullable|integer|min:0|max:100',
            'reportEditor.behavior.participation' => 'nullable|integer|min:1|max:5',
            'reportEditor.behavior.collaboration' => 'nullable|integer|min:1|max:5',
            'reportEditor.behavior.initiative' => 'nullable|integer|min:1|max:5',
            'reportEditor.behavior.responsibility' => 'nullable|integer|min:1|max:5',
            'reportEditor.tracks.*.progress_percent' => 'nullable|integer|min:0|max:100',
            'reportEditor.tracks.*.lessons_completed' => 'nullable|integer|min:0|max:500',
            'reportEditor.tracks.*.lessons_total' => 'nullable|integer|min:0|max:500',
            'reportEditor.tracks.*.quiz_average' => 'nullable|integer|min:0|max:100',
            'reportEditor.tracks.*.projects_count' => 'nullable|integer|min:0|max:100',
            'reportEditor.skills.*.value' => 'nullable|integer|min:0|max:100',
            'reportEditor.performance_metrics.*.value' => 'nullable|numeric|min:0|max:100',
        ]);

        $trackNotes = [];
        $trackMetrics = [];
        foreach ($this->reportEditor['tracks'] ?? [] as $key => $track) {
            $trackNotes[$key] = [
                'strengths' => trim((string) ($track['strengths'] ?? '')) ?: null,
                'next_focus' => trim((string) ($track['next_focus'] ?? '')) ?: null,
            ];

            $projects = $this->linesToList((string) ($track['projects_text'] ?? ''));
            $skillsGained = $this->linesToList((string) ($track['skills_gained_text'] ?? ''));
            $markComplete = filter_var($track['mark_complete'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $forceEnrolled = filter_var($track['force_enrolled'] ?? false, FILTER_VALIDATE_BOOLEAN) || $markComplete;

            $progress = $this->nullableInt($track['progress_percent'] ?? null);
            $lessonsCompleted = $this->nullableInt($track['lessons_completed'] ?? null);
            $lessonsTotal = $this->nullableInt($track['lessons_total'] ?? null);
            if ($markComplete) {
                $progress = 100;
                $lessonsTotal = max($lessonsTotal ?? 0, $lessonsCompleted ?? 0, 1);
                $lessonsCompleted = $lessonsTotal;
                $forceEnrolled = true;
            }

            $trackMetrics[$key] = array_filter([
                'force_enrolled' => $forceEnrolled ?: null,
                'mark_complete' => $markComplete ?: null,
                'progress_percent' => $progress,
                'lessons_completed' => $lessonsCompleted,
                'lessons_total' => $lessonsTotal,
                'quiz_average' => $this->nullableInt($track['quiz_average'] ?? null),
                'projects_count' => $projects !== [] ? count($projects) : $this->nullableInt($track['projects_count'] ?? null),
                'projects' => $projects !== [] ? $projects : null,
                'skills_gained' => $skillsGained !== [] ? $skillsGained : null,
            ], fn ($value) => $value !== null && $value !== false);
        }

        $skills = [];
        foreach ($this->reportEditor['skills'] ?? [] as $key => $skill) {
            $value = $this->nullableInt($skill['value'] ?? null);
            if ($value !== null) {
                $skills[$key] = $value;
            }
        }

        $performanceMetrics = [];
        foreach ($this->reportEditor['performance_metrics'] ?? [] as $key => $metric) {
            $value = $this->nullableFloat($metric['value'] ?? null);
            if ($value !== null) {
                $performanceMetrics[$key] = $value;
            }
        }

        $metricsOverrides = [
            'overall_score' => $this->nullableInt($this->reportEditor['overall_score'] ?? null),
            'attendance' => array_filter([
                'present' => $this->nullableInt($this->reportEditor['attendance_present'] ?? null),
                'total' => $this->nullableInt($this->reportEditor['attendance_total'] ?? null),
                'rate' => $this->nullableInt($this->reportEditor['attendance_rate'] ?? null),
            ], fn ($v) => $v !== null),
            'skills' => $skills !== [] ? $skills : null,
            'tracks' => array_filter($trackMetrics, fn ($t) => $t !== []),
            'performance_metrics' => $performanceMetrics !== [] ? $performanceMetrics : null,
        ];
        $metricsOverrides = array_filter($metricsOverrides, fn ($v) => $v !== null && $v !== []);
        if ($metricsOverrides === []) {
            $metricsOverrides = null;
        }

        CodeClubTermReportDraft::updateOrCreate(
            [
                'code_club_id' => $this->club->id,
                'student_id' => $this->editingReportStudentId,
                'term_key' => $this->reportTermKey,
            ],
            [
                'term_label' => $this->reportTermLabel ?: null,
                'period_start' => $this->reportPeriodStart ?: null,
                'period_end' => $this->reportPeriodEnd ?: null,
                'summary' => trim((string) ($this->reportEditor['summary'] ?? '')) ?: null,
                'overall_label' => trim((string) ($this->reportEditor['overall_label'] ?? '')) ?: null,
                'instructor_comment' => trim((string) ($this->reportEditor['instructor_comment'] ?? '')) ?: null,
                'track_notes' => $trackNotes,
                'behavior' => $this->reportEditor['behavior'] ?? CodeClubTermReportDraft::defaultBehavior(),
                'achievements' => $this->linesToList($this->reportEditor['achievements_text'] ?? ''),
                'improvements' => $this->linesToList($this->reportEditor['improvements_text'] ?? ''),
                'goals' => $this->linesToList($this->reportEditor['goals_text'] ?? ''),
                'metrics_overrides' => $metricsOverrides,
            ]
        );

        $this->loadReportDraftRows();
        $this->closeReportEditor();
        session()->flash('message', 'Report draft saved (including progress overrides).');
    }

    protected function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 1);
    }

    /**
     * @return list<string>
     */
    protected function linesToList(string $text): array
    {
        return CodeClubTermReportDraft::normalizeList(
            preg_split('/\r\n|\r|\n/', $text) ?: []
        );
    }

    /**
     * @return array<string, string>
     */
    public function reportQueryParams(): array
    {
        return array_filter([
            'term_key' => $this->reportTermKey ?: null,
            'from' => $this->reportPeriodStart ?: null,
            'to' => $this->reportPeriodEnd ?: null,
        ]);
    }

    public function exportReportDraftsCsv()
    {
        $this->authorize('generateReports', $this->club);
        $this->loadReportDraftRows();

        $filename = Str::slug($this->club->name).'_report_drafts_'.$this->reportTermKey.'_'.now()->format('Y-m-d').'.csv';

        $rows = $this->reportDraftRows;

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'student_id',
                'name',
                'class_grade',
                'summary',
                'overall_label',
                'instructor_comment',
                'achievements',
                'improvements',
                'goals',
                'participation',
                'collaboration',
                'initiative',
                'responsibility',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['student_code'] ?? $row['student_id'],
                    $row['name'],
                    $row['class_grade'],
                    $row['summary'],
                    $row['overall_label'],
                    $row['instructor_comment'],
                    str_replace("\n", ' | ', $row['achievements_text'] ?? ''),
                    str_replace("\n", ' | ', $row['improvements_text'] ?? ''),
                    str_replace("\n", ' | ', $row['goals_text'] ?? ''),
                    $row['behavior']['participation'] ?? '',
                    $row['behavior']['collaboration'] ?? '',
                    $row['behavior']['initiative'] ?? '',
                    $row['behavior']['responsibility'] ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function toggleEdit(): void
    {
        $this->isEditing = ! $this->isEditing;
        if (! $this->isEditing) {
            $this->fillEditForm();
        }
    }

    public function saveClub(): void
    {
        $this->authorize('update', $this->club);

        $this->validate([
            'editName' => 'required|string|max:255',
            'editDescription' => 'nullable|string',
            'editDayOfWeek' => 'nullable|string|max:20',
            'editSessionStart' => 'nullable|date_format:H:i',
            'editSessionEnd' => 'nullable|date_format:H:i',
            'editMaxCapacity' => 'nullable|integer|min:1',
            'editStatus' => 'required|in:active,inactive,archived',
        ]);

        $this->club->update([
            'name' => $this->editName,
            'description' => $this->editDescription ?: null,
            'day_of_week' => $this->editDayOfWeek ?: null,
            'session_start' => $this->editSessionStart ?: null,
            'session_end' => $this->editSessionEnd ?: null,
            'max_capacity' => $this->editMaxCapacity ?: null,
            'status' => $this->editStatus,
        ]);

        if ($this->editDayOfWeek && ! $this->club->fresh()->hasScheduleRows()) {
            ClubSchedule::create([
                'code_club_id' => $this->club->id,
                'day_of_week' => strtolower($this->editDayOfWeek),
                'session_start' => $this->editSessionStart ?: null,
                'session_end' => $this->editSessionEnd ?: null,
            ]);
            $this->club->load('schedules.instructor');
            $this->loadScheduleRows();
        }

        $this->isEditing = false;
        session()->flash('message', 'Club updated successfully.');
    }

    public function updatedStudentSearch(): void
    {
        if (strlen($this->studentSearch) < 2) {
            $this->studentResults = [];

            return;
        }

        $existingIds = CodeClubMembership::where('code_club_id', $this->club->id)
            ->where('status', 'active')
            ->pluck('student_id');

        $this->studentResults = StudentProfile::query()
            ->with('user:id,name,email')
            ->where('program_type', 'codeclub')
            ->where('school_id', $this->club->school_id)
            ->where('is_active', true)
            ->whereNotIn('user_id', $existingIds)
            ->where(function ($q) {
                $q->where('full_name', 'like', '%' . $this->studentSearch . '%')
                    ->orWhere('student_id', 'like', '%' . $this->studentSearch . '%');
            })
            ->limit(8)
            ->get()
            ->all();
    }

    public function enrollStudent(int $userId): void
    {
        $this->authorize('manageMembers', $this->club);

        $profile = StudentProfile::query()
            ->where('user_id', $userId)
            ->where('program_type', 'codeclub')
            ->firstOrFail();

        abort_unless((int) $profile->school_id === (int) $this->club->school_id, 403, 'Student must belong to the club school.');

        CodeClubMembership::updateOrCreate(
            ['code_club_id' => $this->club->id, 'student_id' => $userId],
            ['status' => 'active', 'enrolled_at' => now(), 'dropped_at' => null]
        );

        $this->studentSearch = '';
        $this->studentResults = [];
        session()->flash('message', 'Student enrolled in club.');
    }

    public function dropStudent(int $userId): void
    {
        $this->authorize('manageMembers', $this->club);

        CodeClubMembership::where('code_club_id', $this->club->id)
            ->where('student_id', $userId)
            ->delete();

        session()->flash('message', 'Student removed from club.');
    }

    public function updatedSelectAllMembers($value): void
    {
        if ($value) {
            $this->selectedMembers = $this->currentMemberUserIds();
        } else {
            $this->selectedMembers = [];
        }
    }

    public function clearMemberSelection(): void
    {
        $this->selectedMembers = [];
        $this->selectAllMembers = false;
    }

    public function openBulkClassModal(): void
    {
        $this->authorize('manageMembers', $this->club);
        abort_unless(count($this->selectedMembers) > 0, 422);
        $this->bulkClassGrade = '';
        $this->showBulkClassModal = true;
    }

    public function closeBulkClassModal(): void
    {
        $this->showBulkClassModal = false;
        $this->bulkClassGrade = '';
    }

    public function applyBulkClassGrade(): void
    {
        $this->authorize('manageMembers', $this->club);

        $this->validate([
            'selectedMembers' => 'required|array|min:1',
            'bulkClassGrade' => 'required|string|max:50',
        ]);

        $updated = 0;

        foreach ($this->selectedMemberProfiles() as $profile) {
            $this->authorize('update', $profile);
            $profile->update(['class_grade' => $this->bulkClassGrade]);
            $updated++;
        }

        $this->closeBulkClassModal();
        $this->clearMemberSelection();
        session()->flash('message', "Updated class/grade for {$updated} student(s).");
    }

    public function bulkDropSelected(): void
    {
        $this->authorize('manageMembers', $this->club);
        abort_unless(count($this->selectedMembers) > 0, 422);

        $removed = CodeClubMembership::query()
            ->where('code_club_id', $this->club->id)
            ->whereIn('student_id', $this->selectedMembers)
            ->delete();

        $this->clearMemberSelection();
        session()->flash('message', "Removed {$removed} student(s) from the club.");
    }

    public function bulkDeactivateStudents(): void
    {
        abort_unless(count($this->selectedMembers) > 0, 422);

        $user = Auth::user();
        abort_unless($user->isAdmin() || $user->isSupervisor() || $user->isOperationsManager(), 403);

        $deactivated = 0;

        foreach ($this->selectedMemberProfiles() as $profile) {
            $this->authorize('update', $profile);
            $profile->update(['is_active' => false]);
            $profile->user?->update(['is_active' => false]);
            $deactivated++;
        }

        $this->clearMemberSelection();
        session()->flash('message', "Deactivated {$deactivated} student(s). Login accounts disabled.");
    }

    public function bulkDeleteStudents(): void
    {
        abort_unless(count($this->selectedMembers) > 0, 422);

        $user = Auth::user();
        abort_unless($user->isAdmin() || $user->isSupervisor(), 403);

        $deleted = 0;

        foreach ($this->selectedMemberProfiles() as $profile) {
            $this->authorize('delete', $profile);
            $profile->removeFromSystem();
            $deleted++;
        }

        $this->clearMemberSelection();
        session()->flash('message', "Removed {$deleted} student(s) from the system. Recorded in Audit Logs.");
    }

    public function bulkEnrollSelectedInCourse(): void
    {
        $this->authorize('manageMembers', $this->club);

        $this->validate([
            'selectedMembers' => 'required|array|min:1',
            'bulkCourseId' => 'required|exists:courses,id',
        ], [
            'bulkCourseId.required' => 'Select a course first.',
        ]);

        $enrolled = 0;

        foreach ($this->selectedMembers as $userId) {
            if (CourseEnrollment::where('course_id', $this->bulkCourseId)->where('user_id', $userId)->exists()) {
                continue;
            }

            CourseEnrollment::create([
                'user_id' => $userId,
                'course_id' => $this->bulkCourseId,
                'club_id' => $this->club->id,
                'enrolled_at' => now(),
                'progress_percentage' => 0,
            ]);

            $enrolled++;
        }

        $this->clearMemberSelection();
        session()->flash('message', "Enrolled {$enrolled} selected student(s) in the course.");
    }

    public function exportSelectedRosterCsv()
    {
        $this->authorize('manageMembers', $this->club);
        abort_unless(count($this->selectedMembers) > 0, 422);

        $memberships = CodeClubMembership::query()
            ->with(['student.studentProfile'])
            ->where('code_club_id', $this->club->id)
            ->whereIn('student_id', $this->selectedMembers)
            ->get();

        $filename = Str::slug($this->club->name) . '_selected_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($memberships) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['full_name', 'student_id', 'class_grade', 'email', 'scratch_account', 'parent_contact']);

            foreach ($memberships as $membership) {
                $profile = $membership->student?->studentProfile;
                $parentData = $profile?->parent_data ?? [];

                fputcsv($handle, [
                    $profile?->full_name ?? $membership->student?->name,
                    $profile?->student_id,
                    $profile?->class_grade,
                    $membership->student?->email,
                    $profile?->scratch_account,
                    $parentData['parent1']['phone'] ?? $profile?->parent_guardian_contact,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function bulkPrintCredentials()
    {
        $this->authorize('manageMembers', $this->club);
        abort_unless(count($this->selectedMembers) > 0, 422);

        $profileIds = StudentProfile::query()
            ->whereIn('user_id', $this->selectedMembers)
            ->pluck('id')
            ->all();

        if ($profileIds === []) {
            session()->flash('message', 'No student profiles found for the selected members.');
            return null;
        }

        return redirect()->route('students.bulk-print-credentials', [
            'ids' => implode(',', $profileIds),
        ]);
    }

    private function currentMemberUserIds(): array
    {
        return CodeClubMembership::query()
            ->where('code_club_id', $this->club->id)
            ->whereHas('student')
            ->when($this->memberFilter === 'active', fn ($q) => $q->where('status', 'active'))
            ->when($this->memberFilter === 'dropped', fn ($q) => $q->where('status', 'dropped'))
            ->when($this->searchStudents, function ($q) {
                $q->whereHas('student.studentProfile', function ($sq) {
                    $sq->where('full_name', 'like', '%' . $this->searchStudents . '%')
                        ->orWhere('student_id', 'like', '%' . $this->searchStudents . '%');
                });
            })
            ->latest('enrolled_at')
            ->paginate(15)
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function selectedMemberProfiles()
    {
        return StudentProfile::query()
            ->whereIn('user_id', $this->selectedMembers)
            ->get();
    }

    public function updatedInstructorSearch(): void
    {
        if (strlen($this->instructorSearch) < 2) {
            $this->instructorResults = [];

            return;
        }

        $existingIds = CodeClubInstructor::where('code_club_id', $this->club->id)
            ->where('status', 'active')
            ->pluck('instructor_id');

        $this->instructorResults = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['teacher', 'codecamp_trainer', 'instructor']))
            ->whereNotIn('id', $existingIds)
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->instructorSearch . '%')
                    ->orWhere('email', 'like', '%' . $this->instructorSearch . '%');
            })
            ->limit(8)
            ->get(['id', 'name', 'email'])
            ->all();
    }

    public function assignInstructor(int $userId): void
    {
        $this->authorize('manageInstructors', $this->club);

        CodeClubInstructor::updateOrCreate(
            ['code_club_id' => $this->club->id, 'instructor_id' => $userId],
            ['role' => 'facilitator', 'status' => 'active', 'assigned_at' => now()]
        );

        $this->instructorSearch = '';
        $this->instructorResults = [];
        $this->club->load('activeInstructors.instructor');
        session()->flash('message', 'Facilitator assigned to club.');
    }

    public function removeInstructor(int $userId): void
    {
        $this->authorize('manageInstructors', $this->club);

        CodeClubInstructor::where('code_club_id', $this->club->id)
            ->where('instructor_id', $userId)
            ->update(['status' => 'inactive']);

        $this->club->load('activeInstructors.instructor');
        session()->flash('message', 'Facilitator removed from club.');
    }

    public function enrollMembersInCourse(): void
    {
        $this->authorize('manageMembers', $this->club);

        $this->validate([
            'bulkCourseId' => 'required|exists:courses,id',
        ]);

        $memberIds = CodeClubMembership::where('code_club_id', $this->club->id)
            ->where('status', 'active')
            ->pluck('student_id');

        $enrolled = 0;

        foreach ($memberIds as $userId) {
            if (CourseEnrollment::where('course_id', $this->bulkCourseId)->where('user_id', $userId)->exists()) {
                continue;
            }

            CourseEnrollment::create([
                'user_id' => $userId,
                'course_id' => $this->bulkCourseId,
                'club_id' => $this->club->id,
                'enrolled_at' => now(),
                'progress_percentage' => 0,
            ]);

            $enrolled++;
        }

        session()->flash('message', "Enrolled {$enrolled} club member(s) in the selected course.");
        $this->bulkCourseId = null;
    }

    public function enrollMemberInCourse(int $userId): void
    {
        $this->authorize('manageMembers', $this->club);

        $this->validate([
            'bulkCourseId' => 'required|exists:courses,id',
        ], [
            'bulkCourseId.required' => 'Select a course first (use the dropdown above the roster).',
        ]);

        if (CourseEnrollment::where('course_id', $this->bulkCourseId)->where('user_id', $userId)->exists()) {
            session()->flash('error', 'Student is already enrolled in that course.');

            return;
        }

        CourseEnrollment::create([
            'user_id' => $userId,
            'course_id' => $this->bulkCourseId,
            'club_id' => $this->club->id,
            'enrolled_at' => now(),
            'progress_percentage' => 0,
        ]);

        session()->flash('message', 'Student enrolled in course.');
    }

    public function toggleImportPanel(): void
    {
        $this->authorize('manageMembers', $this->club);
        $this->showImportPanel = ! $this->showImportPanel;
        $this->importReport = null;

        if (! $this->showImportPanel) {
            $this->importDefaultClassGrade = '';
            $this->importApplyClassToAll = false;
        }
    }

    public function updatedImportCsv(): void
    {
        if ($this->importDefaultClassGrade !== '' || ! $this->importCsv) {
            return;
        }

        $suggested = app(CodeClubStudentImportService::class)
            ->suggestDefaultClassGrade($this->importCsv->getClientOriginalName());

        if ($suggested !== '') {
            $this->importDefaultClassGrade = $suggested;
        }
    }

    public function importStudents(): void
    {
        $this->authorize('manageMembers', $this->club);

        $this->validate([
            'importCsv' => CodeClubStudentImportService::IMPORT_FILE_RULES,
            'importDefaultClassGrade' => 'nullable|string|max:50',
        ]);

        $path = $this->importCsv->getRealPath();
        $report = app(CodeClubStudentImportService::class)->importFromPath(
            $path,
            Auth::user(),
            $this->club->id,
            $this->importCsv->getClientOriginalName(),
            $this->importDefaultClassGrade,
            $this->importApplyClassToAll,
        );

        $this->importReport = $report;
        $this->importCsv = null;

        $message = "Import complete: {$report['imported']} imported, {$report['skipped']} skipped, " . count($report['errors']) . ' issue(s).';
        if (! empty($report['errors'])) {
            $message .= ' ' . $report['errors'][0];
        }

        session()->flash('message', $message);
    }

    public function downloadImportTemplate()
    {
        $this->authorize('manageMembers', $this->club);

        $filename = 'codeclub_import_template.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'full_name',
                'student_id',
                'email',
                'school_name',
                'club_id',
                'parent_name',
                'parent_phone',
                'class_grade',
                'gender',
                'age',
                'scratch_account',
                'scratch_password',
                'github_account',
            ]);
            fputcsv($handle, [
                'Jane Doe',
                '',
                '',
                $this->club->school?->name ?? 'Example School',
                (string) $this->club->id,
                'John Doe',
                '+256700000000',
                'P.5',
                'female',
                '10',
                'janedoe_scratch',
                'secret123',
                'janedoe',
            ]);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportRosterCsv()
    {
        $this->authorize('manageMembers', $this->club);

        $filename = Str::slug($this->club->name) . '_roster_' . now()->format('Y-m-d') . '.csv';

        $memberships = CodeClubMembership::query()
            ->with(['student.studentProfile'])
            ->where('code_club_id', $this->club->id)
            ->where('status', 'active')
            ->orderBy('enrolled_at')
            ->get();

        return response()->streamDownload(function () use ($memberships) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'student_id', 'email', 'enrolled_at', 'parent_contact']);

            foreach ($memberships as $membership) {
                $profile = $membership->student?->studentProfile;
                $parentData = $profile?->parent_data ?? [];
                $parentContact = trim(implode(' · ', array_filter([
                    $parentData['parent1']['name'] ?? $profile?->parent_guardian_name,
                    $parentData['parent1']['phone'] ?? $profile?->parent_guardian_contact,
                ])));

                fputcsv($handle, [
                    $profile?->full_name ?? $membership->student?->name,
                    $profile?->student_id,
                    $membership->student?->email,
                    $membership->enrolled_at?->format('Y-m-d'),
                    $parentContact ?: null,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        $memberships = CodeClubMembership::query()
            ->with(['student.studentProfile', 'student.enrollments.course'])
            ->where('code_club_id', $this->club->id)
            ->whereHas('student')
            ->when($this->memberFilter === 'active', fn ($q) => $q->where('status', 'active'))
            ->when($this->memberFilter === 'dropped', fn ($q) => $q->where('status', 'dropped'))
            ->when($this->searchStudents, function ($q) {
                $q->whereHas('student.studentProfile', function ($sq) {
                    $sq->where('full_name', 'like', '%' . $this->searchStudents . '%')
                        ->orWhere('student_id', 'like', '%' . $this->searchStudents . '%');
                });
            })
            ->when($this->filterClass === self::CLASS_FILTER_UNASSIGNED, function ($q) {
                $q->whereHas('student.studentProfile', function ($sq) {
                    $sq->where(function ($inner) {
                        $inner->whereNull('class_grade')->orWhere('class_grade', '');
                    });
                });
            })
            ->when($this->filterClass !== '' && $this->filterClass !== self::CLASS_FILTER_UNASSIGNED, function ($q) {
                $q->whereHas('student.studentProfile', fn ($sq) => $sq->where('class_grade', $this->filterClass));
            })
            ->orderByDesc('enrolled_at')
            ->paginate(15);

        $classOptions = StudentProfile::query()
            ->whereIn('user_id', CodeClubMembership::query()
                ->where('code_club_id', $this->club->id)
                ->when($this->memberFilter === 'active', fn ($q) => $q->where('status', 'active'))
                ->when($this->memberFilter === 'dropped', fn ($q) => $q->where('status', 'dropped'))
                ->pluck('student_id'))
            ->whereNotNull('class_grade')
            ->where('class_grade', '!=', '')
            ->distinct()
            ->orderBy('class_grade')
            ->pluck('class_grade');

        $unassignedCount = CodeClubMembership::query()
            ->where('code_club_id', $this->club->id)
            ->when($this->memberFilter === 'active', fn ($q) => $q->where('status', 'active'))
            ->when($this->memberFilter === 'dropped', fn ($q) => $q->where('status', 'dropped'))
            ->whereHas('student.studentProfile', function ($sq) {
                $sq->where(function ($inner) {
                    $inner->whereNull('class_grade')->orWhere('class_grade', '');
                });
            })
            ->count();

        $stats = [
            'active' => CodeClubMembership::where('code_club_id', $this->club->id)->where('status', 'active')->count(),
            'dropped' => CodeClubMembership::where('code_club_id', $this->club->id)->where('status', 'dropped')->count(),
            'facilitators' => CodeClubInstructor::where('code_club_id', $this->club->id)->where('status', 'active')->count(),
        ];

        return view('livewire.admin.clubs.show', [
            'memberships' => $memberships,
            'stats' => $stats,
            'courses' => Course::where('is_published', true)->orderBy('title')->get(['id', 'title']),
            'scheduleDays' => self::SCHEDULE_DAYS,
            'facilitatorOptions' => $this->club->activeInstructors->map(fn ($a) => $a->instructor)->filter(),
            'canBulkAdminActions' => Auth::user()->isAdmin() || Auth::user()->isSupervisor() || Auth::user()->isOperationsManager(),
            'classOptions' => $classOptions,
            'unassignedCount' => $unassignedCount,
            'reportQuery' => $this->reportQueryParams(),
            'behaviorKeys' => config('codeclub-reports.behavior_keys', []),
            'overallLabelOptions' => ['Excellent', 'Very Good', 'Good', 'Satisfactory', 'Needs Support', 'Getting Started'],
        ]);
    }
}
