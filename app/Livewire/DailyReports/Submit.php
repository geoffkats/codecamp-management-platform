<?php

namespace App\Livewire\DailyReports;

use App\Models\CampEnrollment;
use App\Models\CodeCamp;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\DailyReport;
use App\Models\DailyReportAttachment;
use App\Models\DailyReportAttendance;
use App\Models\DailyReportIssue;
use App\Models\DailyReportMention;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Submit extends Component
{
    use WithFileUploads;

    public $reportDate;
    public $courseId;
    public $campId;
    public $summary = '';
    public $challenges = '';
    public $issuesText = '';
    public $followUpRequired = false;
    public $attachments = [];
    public array $approaches = [];

    public $attendance = [];
    public $mentions = [];
    public $issues = [];

    public $courses;
    public $students = [];
    public $staff = [];
    public $camps = [];

    public function mount(): void
    {
        $this->reportDate = now()->toDateString();
        $this->courses = $this->loadInstructorCourses();
        $this->staff   = $this->loadStaff();
        $this->camps   = CodeCamp::whereIn('status', ['upcoming', 'active'])->orderBy('start_date')->get(['id', 'name', 'status']);

        if ($this->courses->count() === 1) {
            $this->courseId = $this->courses->first()->id;
            $this->updatedCourseId();
        }

        $activeCamps = $this->camps->where('status', 'active');
        if ($activeCamps->count() === 1) {
            $this->campId = $activeCamps->first()->id;
        }

        $this->attendance = [];
        $this->mentions = [['mentionable_type' => User::class, 'mentionable_id' => null, 'role' => 'recognition', 'note' => null]];
        $this->issues = [['title' => '', 'description' => '', 'severity' => 'normal', 'assigned_to' => null]];
        $this->approaches = DailyReport::emptyApproaches();
    }

    public function updatedCourseId(): void
    {
        $this->students = $this->loadCourseStudents((int) $this->courseId);
        $this->detectCampFromCourse((int) $this->courseId);
        $this->prefillAttendanceFromRecords();
    }

    public function updatedCampId(): void
    {
        if ($this->courseId) {
            $this->prefillAttendanceFromRecords();
        }
    }

    public function updatedReportDate(): void
    {
        if ($this->courseId) {
            $this->prefillAttendanceFromRecords();
        }
    }

    private function prefillAttendanceFromRecords(): void
    {
        if (! $this->courseId) {
            return;
        }

        $attendanceService = app(AttendanceService::class);
        $rows = $attendanceService->prefillDailyReport(
            (int) $this->courseId,
            $this->campId ? (int) $this->campId : null,
            $this->reportDate
        );

        $this->attendance = $rows ?: [['student_id' => null, 'status' => 'absent', 'reason' => '']];

        if (empty($this->attendance)) {
            $this->attendance = [['student_id' => null, 'status' => 'absent', 'reason' => '']];
        }
    }

    private function detectCampFromCourse(int $courseId): void
    {
        if (!$courseId) {
            return;
        }

        $campIdsFromEnrollments = CourseEnrollment::where('course_id', $courseId)
            ->whereNotNull('camp_id')
            ->distinct()
            ->pluck('camp_id');

        if ($campIdsFromEnrollments->count() === 1) {
            $this->campId = $campIdsFromEnrollments->first();
            return;
        }

        $studentIds = User::whereHas('enrollments', fn ($q) => $q->where('course_id', $courseId))
            ->where('student_type', 'codecamp')
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return;
        }

        $activeCampIds = CampEnrollment::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->distinct()
            ->pluck('camp_id');

        if ($activeCampIds->count() === 1) {
            $this->campId = $activeCampIds->first();
        }
    }

    public function addAttendanceRow(): void
    {
        $this->attendance[] = ['student_id' => null, 'status' => 'present', 'reason' => ''];
    }

    public function addMentionRow(): void
    {
        $this->mentions[] = ['mentionable_type' => User::class, 'mentionable_id' => null, 'role' => null, 'note' => null];
    }

    public function addIssueRow(): void
    {
        $this->issues[] = ['title' => '', 'description' => '', 'severity' => 'normal', 'assigned_to' => null];
    }

    public function removeAttendanceRow(int $index): void
    {
        unset($this->attendance[$index]);
        $this->attendance = array_values($this->attendance);
    }

    public function removeMentionRow(int $index): void
    {
        unset($this->mentions[$index]);
        $this->mentions = array_values($this->mentions);
    }

    public function removeIssueRow(int $index): void
    {
        unset($this->issues[$index]);
        $this->issues = array_values($this->issues);
    }

    public function save()
    {
        $this->validate($this->rules());

        $user = Auth::user();

        $report = DB::transaction(function () use ($user) {
            $report = DailyReport::updateOrCreate(
                [
                    'report_date' => $this->reportDate,
                    'course_id' => $this->courseId,
                    'instructor_id' => $user->id,
                ],
                [
                    'status' => 'submitted',
                    'camp_id' => $this->campId ?: null,
                    'summary' => $this->summary,
                    'challenges' => $this->challenges,
                    'issues' => $this->issuesText,
                    'pedagogical_approaches' => $this->serializedApproaches(),
                    'follow_up_required' => (bool) $this->followUpRequired,
                    'submitted_at' => now(),
                ]
            );

            // Reset related data
            $report->attendance()->delete();
            $report->mentions()->delete();
            $report->reportIssues()->delete();
            $report->attachments()->delete();

            // Attendance
            foreach ($this->attendance as $row) {
                if (empty($row['student_id'])) {
                    continue;
                }
                DailyReportAttendance::create([
                    'daily_report_id' => $report->id,
                    'student_id' => $row['student_id'],
                    'status' => $row['status'],
                    'reason' => $row['reason'] ?? null,
                    'tagged_by' => $user->id,
                ]);
            }

            // Mentions
            foreach ($this->mentions as $mention) {
                if (empty($mention['mentionable_id'])) {
                    continue;
                }
                DailyReportMention::create([
                    'daily_report_id' => $report->id,
                    'mentionable_type' => $this->normalizeMentionableType($mention['mentionable_type'] ?? null),
                    'mentionable_id' => $mention['mentionable_id'],
                    'role' => $mention['role'] ?? null,
                    'note' => $mention['note'] ?? null,
                ]);
            }

            // Issues
            foreach ($this->issues as $issue) {
                if (empty($issue['title'])) {
                    continue;
                }
                DailyReportIssue::create([
                    'daily_report_id' => $report->id,
                    'title' => $issue['title'],
                    'description' => $issue['description'] ?? null,
                    'severity' => $issue['severity'] ?? 'normal',
                    'assigned_to' => $issue['assigned_to'] ?? null,
                    'status' => 'open',
                ]);
            }

            // Attachments
            if (!empty($this->attachments)) {
                foreach ($this->attachments as $file) {
                    $path = $file->store('daily-reports/' . $this->reportDate, 'public');
                    DailyReportAttachment::create([
                        'daily_report_id' => $report->id,
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        // MIME types like Office Open XML can exceed varchar(50)
                        'type' => Str::limit((string) ($file->getMimeType() ?: $file->getClientOriginalExtension() ?: 'file'), 255, ''),
                    ]);
                }
            }

            return $report;
        });

        app(AttendanceService::class)->syncFromDailyReport($report, $user);

        session()->flash('message', 'Daily report submitted.');
        return redirect()->route('dashboard');
    }

    public function rules(): array
    {
        $rules = [
            'reportDate' => 'required|date',
            'courseId' => [
                'required',
                'exists:courses,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! Course::accessibleBy(Auth::user())->where('id', $value)->exists()) {
                        $fail('You do not have access to that course.');
                    }
                },
            ],
            'summary' => 'nullable|string',
            'challenges' => 'nullable|string',
            'issuesText' => 'nullable|string',
            'followUpRequired' => 'boolean',
            'attachments.*' => 'file|max:10240',
            'attendance.*.student_id' => 'nullable|exists:users,id',
            'attendance.*.status' => 'required_with:attendance.*.student_id|in:present,absent,late',
            'attendance.*.reason' => 'nullable|string',
            'mentions.*.mentionable_id' => 'nullable|integer',
            'mentions.*.mentionable_type' => 'nullable|string',
            'mentions.*.role' => 'nullable|string',
            'mentions.*.note' => 'nullable|string',
            'issues.*.title' => 'nullable|string|max:255',
            'issues.*.description' => 'nullable|string',
            'issues.*.severity' => 'nullable|in:normal,high,critical',
            'issues.*.assigned_to' => 'nullable|exists:users,id',
        ];

        foreach (array_keys(DailyReport::PEDAGOGICAL_APPROACHES) as $key) {
            $rules["approaches.{$key}.used"] = 'boolean';
            $rules["approaches.{$key}.description"] = [
                'nullable',
                'string',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail) use ($key) {
                    if (! empty($this->approaches[$key]['used']) && trim((string) $value) === '') {
                        $fail('Add a brief note on how this approach was used.');
                    }
                },
            ];
        }

        return $rules;
    }

    private function serializedApproaches(): array
    {
        $out = DailyReport::emptyApproaches();

        foreach (array_keys($out) as $key) {
            $used = (bool) ($this->approaches[$key]['used'] ?? false);
            $out[$key] = [
                'used' => $used,
                'description' => $used ? trim((string) ($this->approaches[$key]['description'] ?? '')) : '',
            ];
        }

        return $out;
    }

    private function loadStaff()
    {
        return User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'supervisor', 'teacher', 'ict_teacher', 'instructor']);
        })->orderBy('name')->get(['id', 'name']);
    }

    private function loadInstructorCourses()
    {
        return Course::accessibleBy(Auth::user())
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    private function loadCourseStudents(int $courseId)
    {
        return User::whereHas('enrollments', function ($q) use ($courseId) {
            $q->where('course_id', $courseId);
        })
        ->where('student_type', 'codecamp')
        ->orderBy('name')
        ->get(['id', 'name']);
    }

    private function normalizeMentionableType(?string $type): string
    {
        if ($type === null || $type === '' || $type === 'user') {
            return User::class;
        }

        return $type;
    }

    public function render()
    {
        return view('livewire.daily-reports.submit', [
            'courses' => $this->courses,
            'students' => $this->students,
            'staff'    => $this->staff,
            'camps'    => $this->camps,
            'approachOptions' => DailyReport::PEDAGOGICAL_APPROACHES,
        ]);
    }
}
