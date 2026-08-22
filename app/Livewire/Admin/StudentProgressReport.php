<?php

namespace App\Livewire\Admin;

use App\Services\Reports\StudentProgressReportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class StudentProgressReport extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $search = '';

    public string $filterCourse = 'all';

    public string $filterProgram = 'all';

    public string $filterCamp = 'all';

    public string $filterCampStatus = 'all';

    public int $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterCourse' => ['except' => 'all'],
        'filterProgram' => ['except' => 'all'],
        'filterCamp' => ['except' => 'all'],
        'filterCampStatus' => ['except' => 'all'],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        if (! Auth::user()->hasAnyRole(['admin', 'supervisor'])) {
            abort(403, 'Unauthorized - Admin or Supervisor access required');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCourse(): void
    {
        $this->resetPage();
    }

    public function updatingFilterProgram(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCamp(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCampStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterCourse = 'all';
        $this->filterProgram = 'all';
        $this->filterCamp = 'all';
        $this->filterCampStatus = 'all';
        $this->resetPage();
    }

    public function selectCamp(string $campId): void
    {
        $this->filterCamp = $campId;
        $this->filterCampStatus = $campId === 'all' ? 'all' : 'active';
        $this->resetPage();
    }

    private function reportFilters(): array
    {
        return [
            'search' => $this->search,
            'course' => $this->filterCourse,
            'program' => $this->filterProgram,
            'camp' => $this->filterCamp,
            'campStatus' => $this->filterCampStatus,
        ];
    }

    public function exportCsv(StudentProgressReportService $reportService)
    {
        $filters = $this->reportFilters();
        $courseId = $this->filterCourse !== 'all' ? (int) $this->filterCourse : null;
        $campId = $this->filterCamp !== 'all' ? (int) $this->filterCamp : null;

        $summary = $reportService->getFilterSummary($filters);

        if ($summary['total'] === 0) {
            session()->flash('message', 'No rows available for export with current filters.');

            return null;
        }

        $filename = 'student_progress_report_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($reportService, $filters, $courseId, $campId) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Student Name',
                'Email',
                'Student ID',
                'Program',
                'Class',
                'School',
                'Courses Enrolled',
                'Completion Rate (%)',
                'Lessons Completed',
                'Assessments Attempted',
                'Average Assessment Score (%)',
                'Badges Earned',
                'Last Activity',
            ]);

            $reportService->chunkFilteredStudents($filters, 100, function ($students) use ($handle, $reportService, $courseId, $campId) {
                $metrics = $reportService->getListMetricsForUsers(
                    $students->pluck('user_id')->filter()->map(fn ($id) => (int) $id)->all(),
                    $courseId,
                    $campId
                );

                foreach ($students as $student) {
                    $studentMetrics = $metrics[$student->user_id] ?? [
                        'courses_enrolled' => 0,
                        'completion_rate' => 0,
                        'lessons_completed' => 0,
                        'assessments_attempted' => 0,
                        'avg_assessment_score' => 0,
                        'badges_earned' => 0,
                        'last_activity_at' => null,
                    ];

                    fputcsv($handle, [
                        $student->full_name ?: $student->user?->name,
                        $student->user?->email,
                        $student->student_id ?: $student->user?->student_id,
                        $student->program_type,
                        $student->class_grade,
                        $student->school?->name,
                        $studentMetrics['courses_enrolled'],
                        $studentMetrics['completion_rate'],
                        $studentMetrics['lessons_completed'],
                        $studentMetrics['assessments_attempted'],
                        $studentMetrics['avg_assessment_score'],
                        $studentMetrics['badges_earned'],
                        $studentMetrics['last_activity_at']
                            ? \Illuminate\Support\Carbon::parse($studentMetrics['last_activity_at'])->toDateTimeString()
                            : null,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render(StudentProgressReportService $reportService)
    {
        $filters = $this->reportFilters();
        $courseId = $this->filterCourse !== 'all' ? (int) $this->filterCourse : null;
        $campId = $this->filterCamp !== 'all' ? (int) $this->filterCamp : null;

        $students = $reportService->getStudentList([
            ...$filters,
            'page' => $this->getPage(),
        ], $this->perPage);

        $metrics = $reportService->getListMetricsForUsers(
            $students->getCollection()->pluck('user_id')->filter()->map(fn ($id) => (int) $id)->all(),
            $courseId,
            $campId
        );

        $summary = $reportService->getFilterSummary($filters);

        $courseOptions = $reportService->getCourseOptions(
            $this->filterProgram !== 'all' ? $this->filterProgram : null,
            $campId
        );

        $campOptions = $reportService->getCampOptions();

        return view('livewire.admin.student-progress-report', [
            'students' => $students,
            'metrics' => $metrics,
            'summary' => $summary,
            'courseOptions' => $courseOptions,
            'campOptions' => $campOptions,
        ]);
    }
}
