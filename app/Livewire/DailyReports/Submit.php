<?php

namespace App\Livewire\DailyReports;

use App\Models\Course;
use App\Models\DailyReport;
use App\Models\DailyReportAttachment;
use App\Models\DailyReportAttendance;
use App\Models\DailyReportIssue;
use App\Models\DailyReportMention;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Submit extends Component
{
    use WithFileUploads;

    public $reportDate;
    public $courseId;
    public $summary = '';
    public $challenges = '';
    public $issuesText = '';
    public $followUpRequired = false;
    public $attachments = [];

    public $attendance = [];
    public $mentions = [];
    public $issues = [];

    public $courses;
    public $students = [];

    public function mount(): void
    {
        $this->reportDate = now()->toDateString();
        $this->courses = $this->loadInstructorCourses();
        $this->attendance = [['student_id' => null, 'status' => 'present', 'reason' => '']];
        $this->mentions = [['mentionable_type' => 'user', 'mentionable_id' => null, 'role' => null, 'note' => null]];
        $this->issues = [['title' => '', 'description' => '', 'severity' => 'normal', 'assigned_to' => null]];
    }

    public function updatedCourseId(): void
    {
        $this->students = $this->loadCourseStudents((int) $this->courseId);
        
        // Auto-populate attendance with enrolled students
        $this->attendance = [];
        foreach ($this->students as $student) {
            $this->attendance[] = [
                'student_id' => $student->id,
                'status' => 'present',
                'reason' => ''
            ];
        }
        
        // If no students, add one empty row
        if (empty($this->attendance)) {
            $this->attendance[] = ['student_id' => null, 'status' => 'present', 'reason' => ''];
        }
    }

    public function addAttendanceRow(): void
    {
        $this->attendance[] = ['student_id' => null, 'status' => 'present', 'reason' => ''];
    }

    public function addMentionRow(): void
    {
        $this->mentions[] = ['mentionable_type' => 'user', 'mentionable_id' => null, 'role' => null, 'note' => null];
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
                    'summary' => $this->summary,
                    'challenges' => $this->challenges,
                    'issues' => $this->issuesText,
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
                    'mentionable_type' => $mention['mentionable_type'] ?? User::class,
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
                        'type' => $file->getMimeType(),
                    ]);
                }
            }

            return $report;
        });

        session()->flash('message', 'Daily report submitted.');
        return redirect()->route('dashboard');
    }

    public function rules(): array
    {
        return [
            'reportDate' => 'required|date',
            'courseId' => 'required|exists:courses,id',
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
    }

    private function loadInstructorCourses()
    {
        $userId = Auth::id();
        return Course::where('instructor_id', $userId)
            ->orWhereHas('collaborators', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    private function loadCourseStudents(int $courseId)
    {
        // Simple approach: fetch users enrolled via course_enrollments if available, else empty list
        if (class_exists(\App\Models\CourseEnrollment::class)) {
            return \App\Models\CourseEnrollment::where('course_id', $courseId)
                ->with('user:id,name')
                ->get()
                ->pluck('user')
                ->filter()
                ->values();
        }

        return collect();
    }

    public function render()
    {
        return view('livewire.daily-reports.submit', [
            'courses' => $this->courses,
            'students' => $this->students,
        ]);
    }
}
