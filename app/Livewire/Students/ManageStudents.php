<?php

namespace App\Livewire\Students;

use App\Models\CodeClubMembership;
use App\Models\StudentProfile;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CodeCamp;
use App\Models\Notification;
use App\Models\User;
use App\Support\ProgramScope;
use App\Services\CodeClubStudentImportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ManageStudents extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;
    use WithPagination;

    public $search = '';
    public $filterClass = '';
    public $selected = [];
    public $selectAll = false;
    public $showAssignModal = false;
    public $selectedCourseId = '';
    public $notifyStudents = true;
    public $filterEnrollment = '';
    public $filterEnrollmentCourseId = '';
    public $filterCategory = '';
    public $filterReadiness = '';
    public $filterModuleId = '';
    public string $filterProgram = 'all';
    public string $filterCamp = 'all';
    public string $filterCampStatus = 'all';
    public string $filterClub = 'all';

    public const CLASS_FILTER_UNASSIGNED = '__unassigned__';

    public $importCsv = null;

    public ?array $importReport = null;

    public bool $showImportPanel = false;

    public ?int $importClubId = null;

    public string $importDefaultClassGrade = '';

    public bool $importApplyClassToAll = false;

    public bool $showBulkClassModal = false;

    public string $bulkClassGrade = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterClass' => ['except' => ''],
        'filterProgram' => ['except' => 'all'],
        'filterCamp' => ['except' => 'all'],
        'filterCampStatus' => ['except' => 'all'],
        'filterEnrollment' => ['except' => ''],
        'filterEnrollmentCourseId' => ['except' => ''],
        'filterCategory' => ['except' => ''],
    ];

    public function mount(): void
    {
        if ($this->isCodeClubView()) {
            $this->filterProgram = 'codeclub';
            $this->defaultClubFilterForImporter();
        }
    }

    private function defaultClubFilterForImporter(): void
    {
        if ($this->filterClub !== 'all') {
            return;
        }

        $user = auth()->user();

        if (! $user || $user->isAdmin() || $user->isSupervisor()) {
            return;
        }

        $clubs = ProgramScope::visibleClubs($user);

        if ($clubs->count() === 1) {
            $this->filterClub = (string) $clubs->first()->id;
            $this->importClubId = (int) $clubs->first()->id;
        }
    }

    public function updatedFilterClub(): void
    {
        if ($this->filterClub !== 'all') {
            $this->importClubId = (int) $this->filterClub;
        }
    }

    private function resolveImportClubId(): ?int
    {
        if ($this->importClubId) {
            return (int) $this->importClubId;
        }

        if ($this->filterClub !== 'all') {
            return (int) $this->filterClub;
        }

        return null;
    }

    private function isCodeClubView(): bool
    {
        $user = auth()->user();

        if (! config('features.code_club', false) || ! $user?->hasCodeClubAccess()) {
            return false;
        }

        if ($user->isAdmin() || $user->isSupervisor()) {
            return $this->filterProgram === 'codeclub';
        }

        if ($user->isIctTeacher()) {
            return false;
        }

        if ($user->hasCodeClubAccess() && ! $user->isCodecampTrainer()) {
            return true;
        }

        return ProgramScope::context($user) === 'codeclub';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterClass()
    {
        $this->resetPage();
    }

    public function updatingFilterEnrollment()
    {
        $this->resetPage();
    }

    public function updatingFilterEnrollmentCourseId()
    {
        $this->resetPage();
    }

    public function updatingFilterCategory()
    {
        $this->resetPage();
    }

    public function updatingFilterReadiness()
    {
        $this->resetPage();
    }

    public function updatingFilterModuleId()
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

    public function updatingFilterClub(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCampStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterClass = '';
        $this->filterProgram = $this->isCodeClubView() ? 'codeclub' : 'all';
        $this->filterCamp = 'all';
        $this->filterCampStatus = 'all';
        $this->filterEnrollment = '';
        $this->filterEnrollmentCourseId = '';
        $this->filterCategory = '';
        $this->filterReadiness = '';
        $this->filterModuleId = '';
        $this->filterClub = 'all';
        $this->resetPage();
    }

    public function selectCamp(string $campId): void
    {
        $this->filterCamp = $campId;
        $this->filterCampStatus = $campId === 'all' ? 'all' : 'active';
        $this->resetPage();
    }

    public function selectClass(string $class): void
    {
        $this->filterClass = $class;
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->currentStudentIds();
        } else {
            $this->selected = [];
        }
    }

    public function openAssignModal()
    {
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
    }

    public function assignSelectedToCourse()
    {
        $this->validate([
            'selected' => 'required|array|min:1',
            'selectedCourseId' => 'required|exists:courses,id',
        ], [
            'selected.required' => 'Select at least one student.',
            'selectedCourseId.required' => 'Choose a course to assign.',
        ]);

        if (auth()->user()->isIctTeacher()) {
            $allowedCourseIds = $this->availableIctCourses()->pluck('id')->all();
            if (!in_array((int) $this->selectedCourseId, $allowedCourseIds, true)) {
                abort(403, 'Unauthorized course selection.');
            }
        }

        $courseId = $this->selectedCourseId;
        $enrolledCount = 0;

        $profiles = $this->applyStudentScope(StudentProfile::query())
            ->with('user')
            ->whereIn('id', $this->selected)
            ->get();

        foreach ($profiles as $profile) {
            if (!$profile->user) {
                continue; // skip profiles without linked user accounts
            }

            $enrollment = CourseEnrollment::firstOrCreate(
                [
                    'user_id' => $profile->user_id,
                    'course_id' => $courseId,
                ],
                [
                    'enrolled_at' => now(),
                    'progress_percentage' => 0,
                ]
            );

            if ($enrollment->wasRecentlyCreated) {
                $enrolledCount++;

                if ($this->notifyStudents) {
                    Notification::create([
                        'user_id' => $profile->user_id,
                        'title' => 'New Course Enrollment',
                        'message' => 'You have been enrolled in a new course.',
                        'type' => 'info',
                        'data' => [
                            'course_id' => $courseId,
                            'student_profile_id' => $profile->id,
                        ],
                        'is_read' => false,
                    ]);
                }
            }
        }

        $this->showAssignModal = false;
        $this->selected = [];
        $this->selectAll = false;
        $this->selectedCourseId = '';

        session()->flash('message', "Assigned {$enrolledCount} new enrollment(s).");
    }

    public function markExamReady(int $studentId): void
    {
        $student = $this->findScopedStudent($studentId)->load('user.enrollments');

        $student->exam_readiness_status = 'teacher_approved';
        $student->save();

        session()->flash('message', 'Student marked ICDL Test Ready.');
    }

    public function markNeedsPractice(int $studentId): void
    {
        $student = $this->findScopedStudent($studentId);
        $student->exam_readiness_status = 'needs_practice';
        $student->save();

        session()->flash('message', 'Student marked as Needs Practice.');
    }

    public function removeStudent(int $studentId): void
    {
        $student = $this->findScopedStudent($studentId);
        $student->is_active = false;
        $student->save();

        session()->flash('message', 'Student removed from active list.');
    }

    public function requestExamSession(int $studentId): void
    {
        $student = $this->findScopedStudent($studentId);

        if ($student->exam_readiness_status !== 'teacher_approved') {
            session()->flash('message', 'Student must be marked ICDL Test Ready before requesting an exam session.');
            return;
        }

        $student->exam_request_status = 'requested';
        $student->exam_requested_at = now();
        $student->save();

        session()->flash('message', 'Exam request submitted for admin approval.');

        $this->notifyAdmins(
            'ICDL Exam Request',
            "Exam session requested for {$student->full_name} ({$student->student_id}).",
            [
                'student_profile_id' => $student->id,
                'school_id' => $student->school_id,
            ]
        );
    }

    public function submitExamPayment(int $studentId): void
    {
        $student = $this->findScopedStudent($studentId);

        $student->exam_payment_status = 'submitted';
        $student->exam_payment_submitted_at = now();
        $student->save();

        session()->flash('message', 'Exam payment submitted for verification.');

        $this->notifyAdmins(
            'Exam Payment Submitted',
            "Exam payment submitted for {$student->full_name} ({$student->student_id}).",
            [
                'student_profile_id' => $student->id,
                'school_id' => $student->school_id,
            ]
        );
    }

    public function exportSelectedCsv()
    {
        if (count($this->selected) === 0) {
            session()->flash('message', 'Select at least one student to export.');
            return null;
        }

        $students = $this->getSelectedStudents();

        if ($students->isEmpty()) {
            session()->flash('message', 'No students available for export.');
            return null;
        }

        $filename = 'students_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($students) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Student ID',
                'Full Name',
                'Class',
                'Gender',
                'Program',
                'School',
                'Login Username',
                'Initial Password',
            ]);

            foreach ($students as $student) {
                $user = $student->user;
                fputcsv($handle, [
                    $student->student_id,
                    $student->full_name,
                    $student->class_grade,
                    $student->gender,
                    $student->program_type,
                    $student->school?->name,
                    $user?->loginIdentifier() ?: $student->student_id,
                    $user?->initial_password ?: '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function printSelectedCredentials()
    {
        if (count($this->selected) === 0) {
            session()->flash('message', 'Select at least one student to print.');
            return null;
        }

        $ids = implode(',', $this->selected);

        return redirect()->route('students.bulk-print-credentials', [
            'ids' => $ids,
        ]);
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function openBulkClassModal(): void
    {
        abort_unless($this->isCodeClubView(), 403);
        abort_unless(count($this->selected) > 0, 422);

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
        abort_unless($this->isCodeClubView(), 403);

        $this->validate([
            'selected' => 'required|array|min:1',
            'bulkClassGrade' => 'required|string|max:50',
        ], [
            'bulkClassGrade.required' => 'Enter a class or grade to apply.',
        ]);

        $updated = 0;

        foreach ($this->getSelectedProfiles() as $profile) {
            $this->authorize('update', $profile);
            $profile->update(['class_grade' => $this->bulkClassGrade]);
            $updated++;
        }

        $this->closeBulkClassModal();
        $this->clearSelection();
        session()->flash('message', "Updated class/grade for {$updated} student(s).");
    }

    public function bulkRemoveFromClub(): void
    {
        abort_unless($this->isCodeClubView(), 403);
        abort_unless(count($this->selected) > 0, 422);

        $clubId = $this->resolveBulkClubId();

        if ($clubId === null) {
            session()->flash('message', 'Select a Code Club filter first so we know which club to remove students from.');
            return;
        }

        $user = auth()->user();
        $club = \App\Models\CodeClub::find($clubId);

        if (! $club) {
            session()->flash('message', 'Club not found.');
            return;
        }

        if ($user->isClubFacilitator() && ! in_array($clubId, $user->activeClubIds(), true)) {
            abort(403);
        }

        $removed = 0;

        foreach ($this->getSelectedProfiles() as $profile) {
            if (! $profile->user_id) {
                continue;
            }

            $deleted = CodeClubMembership::query()
                ->where('code_club_id', $clubId)
                ->where('student_id', $profile->user_id)
                ->delete();

            if ($deleted) {
                $removed++;
            }
        }

        $this->clearSelection();
        session()->flash('message', "Removed {$removed} student(s) from {$club->name}.");
    }

    public function bulkDeactivateStudents(): void
    {
        abort_unless(count($this->selected) > 0, 422);

        $user = auth()->user();
        abort_unless($user->isAdmin() || $user->isSupervisor() || $user->isOperationsManager(), 403);

        $deactivated = 0;

        foreach ($this->getSelectedProfiles() as $profile) {
            $this->authorize('update', $profile);
            $profile->update(['is_active' => false]);
            $profile->user?->update(['is_active' => false]);
            $deactivated++;
        }

        $this->clearSelection();
        session()->flash('message', "Deactivated {$deactivated} student(s). Login accounts disabled.");
    }

    public function bulkDeleteStudents(): void
    {
        abort_unless(count($this->selected) > 0, 422);

        $user = auth()->user();
        abort_unless($user->isAdmin() || $user->isSupervisor(), 403);

        $deleted = 0;

        foreach ($this->getSelectedProfiles() as $profile) {
            $this->authorize('delete', $profile);
            $profile->removeFromSystem();
            $deleted++;
        }

        $this->clearSelection();
        session()->flash('message', "Removed {$deleted} student(s) from the system. Recorded in Audit Logs.");
    }

    private function resolveBulkClubId(): ?int
    {
        if ($this->filterClub !== 'all') {
            return (int) $this->filterClub;
        }

        if ($this->importClubId) {
            return (int) $this->importClubId;
        }

        $user = auth()->user();
        $clubs = ProgramScope::visibleClubs($user);

        return $clubs->count() === 1 ? (int) $clubs->first()->id : null;
    }

    private function getSelectedProfiles()
    {
        return $this->applyStudentScope(StudentProfile::query())
            ->whereIn('id', $this->selected)
            ->get();
    }

    public function toggleImportPanel(): void
    {
        abort_unless($this->canImportCodeClubStudents(), 403);
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

    public function importCodeClubStudents(): void
    {
        abort_unless($this->canImportCodeClubStudents(), 403);

        $this->validate([
            'importCsv' => CodeClubStudentImportService::IMPORT_FILE_RULES,
            'importDefaultClassGrade' => 'nullable|string|max:50',
        ]);

        $fixedClubId = $this->resolveImportClubId();

        $report = app(CodeClubStudentImportService::class)->importFromPath(
            $this->importCsv->getRealPath(),
            Auth::user(),
            $fixedClubId,
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

    public function downloadCodeClubImportTemplate()
    {
        abort_unless($this->canImportCodeClubStudents(), 403);

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
                'date_of_birth',
                'scratch_account',
                'scratch_password',
                'github_account',
            ]);
            fputcsv($handle, [
                'Jane Doe',
                '',
                '',
                'Example Primary School',
                '',
                'John Doe',
                '+256700000000',
                'P.5',
                'female',
                '2014-05-01',
                'janedoe_scratch',
                'secret123',
                'janedoe',
            ]);
            fclose($handle);
        }, 'codeclub_import_template.csv', ['Content-Type' => 'text/csv']);
    }

    private function canImportCodeClubStudents(): bool
    {
        if (! config('features.code_club', false)) {
            return false;
        }

        $user = auth()->user();

        return $user->isAdmin()
            || $user->isSupervisor()
            || $user->isOperationsManager()
            || $user->isClubFacilitator();
    }

    private function currentStudentIds(): array
    {
        return $this->filteredStudentsQuery()
            ->latest()
            ->paginate(15)
            ->pluck('id')
            ->all();
    }

    private function getSelectedStudents()
    {
        return $this->applyStudentScope(StudentProfile::query())
            ->with(['user', 'school'])
            ->whereIn('id', $this->selected)
            ->orderBy('full_name')
            ->get();
    }

    public function render()
    {
        $user = auth()->user();
        $isIct = $user->isIctTeacher();
        $isCodeClubView = $this->isCodeClubView();
        $isCodecampOnly = $user->isCodecampTrainer() && ! $user->hasCodeClubAccess();
        $isClubOnly = $isCodeClubView && ! $user->isAdmin() && ! $user->isSupervisor();
        $canBulkAdminActions = $user->isAdmin() || $user->isSupervisor() || $user->isOperationsManager();
        $showProgramTabs = ! $isIct && ! $isCodecampOnly && ! $isCodeClubView;
        $showCampFilters = ! $isIct && ! $isCodeClubView;
        $showClubFilters = $isCodeClubView && ProgramScope::visibleClubs($user)->isNotEmpty();
        $showCodeClubImport = $this->canImportCodeClubStudents() && $isCodeClubView;

        $availableIctCourses = $isIct ? $this->availableIctCourses() : collect();
        $ictCourseIds = $availableIctCourses->pluck('id')->all();

        $studentRelations = [
            'user.enrollments' => function ($query) use ($isIct, $ictCourseIds) {
                if ($isIct) {
                    if (empty($ictCourseIds)) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereIn('course_id', $ictCourseIds);
                    }
                }
                $query->with('course');
            },
            'school:id,name',
            'gadgets',
        ];

        if ($isCodeClubView) {
            $studentRelations[] = 'user.activeCodeClubMembership.club:id,name';
        } else {
            $studentRelations[] = 'user.currentCampEnrollment.camp:id,name,status';
        }

        $students = $this->filteredStudentsQuery()
            ->with($studentRelations)
            ->latest()
            ->paginate(15);

        $baseScope = $this->applyStudentScope(StudentProfile::query());
        $stats = [
            'total' => (clone $baseScope)->count(),
            'codecamp' => (clone $baseScope)->where('program_type', 'codecamp')->count(),
            'ict' => (clone $baseScope)->where('program_type', 'ict')->count(),
            'codeclub' => (clone $baseScope)->where('program_type', 'codeclub')->count(),
            'in_camp' => $showCampFilters
                ? (clone $baseScope)->whereHas('user.campEnrollments', fn ($q) => $q->where('status', 'active'))->count()
                : 0,
            'matching' => $this->filteredStudentsQuery()->count(),
            'unassigned' => (clone $baseScope)->where(function ($q) {
                $q->whereNull('class_grade')->orWhere('class_grade', '');
            })->count(),
        ];

        $classes = $this->classGradeOptions($this->applyStudentScope(StudentProfile::query()));

        $courses = $isIct
            ? $availableIctCourses
            : Course::query()->orderBy('title')->get();

        $campOptions = $showCampFilters
            ? CodeCamp::query()->select('id', 'name', 'status', 'start_date')->orderByDesc('start_date')->get()
            : collect();

        $clubOptions = $showClubFilters
            ? ProgramScope::visibleClubs($user)
            : collect();

        return view('livewire.students.manage-students', [
            'students' => $students,
            'classes' => $classes,
            'courses' => $courses,
            'campOptions' => $campOptions,
            'stats' => $stats,
            'isIct' => $isIct,
            'isCodecampOnly' => $isCodecampOnly,
            'isClubOnly' => $isClubOnly,
            'isCodeClubView' => $isCodeClubView,
            'showProgramTabs' => $showProgramTabs,
            'showCampFilters' => $showCampFilters,
            'showClubFilters' => $showClubFilters,
            'showCodeClubImport' => $showCodeClubImport,
            'clubOptions' => $clubOptions,
            'canBulkAdminActions' => $canBulkAdminActions,
        ]);
    }

    private function filteredStudentsQuery(): Builder
    {
        $user = auth()->user();
        $isIct = $user->isIctTeacher();
        $isCodeClubView = $this->isCodeClubView();
        $isCodecampOnly = $user->isCodecampTrainer() && ! $user->hasCodeClubAccess();

        return $this->applyStudentScope(StudentProfile::query())
            ->when($isCodeClubView, fn ($q) => $q->where('program_type', 'codeclub'))
            ->when(! $isIct && ! $isCodeClubView && ($showProgram = $this->filterProgram !== 'all'), function ($q) {
                $q->where('program_type', $this->filterProgram);
            })
            ->when(! $isIct && ! $isCodeClubView && $this->filterCamp !== 'all', function ($q) {
                $q->whereHas('user.campEnrollments', function ($campQuery) {
                    $campQuery->where('camp_id', (int) $this->filterCamp);
                    if ($this->filterCampStatus !== 'all') {
                        $campQuery->where('status', $this->filterCampStatus);
                    }
                });
            })
            ->when($this->search, function ($q) use ($isIct) {
                $q->where(function ($inner) use ($isIct) {
                    $inner->where('full_name', 'like', '%' . $this->search . '%')
                        ->orWhere('student_id', 'like', '%' . $this->search . '%')
                        ->when($isIct, function ($sub) {
                            $sub->orWhere('icdl_number', 'like', '%' . $this->search . '%');
                        }, function ($sub) {
                            $sub->orWhere('parent_guardian_contact', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filterClass === self::CLASS_FILTER_UNASSIGNED, function ($q) {
                $q->where(function ($inner) {
                    $inner->whereNull('class_grade')
                        ->orWhere('class_grade', '');
                });
            })
            ->when($this->filterClass && $this->filterClass !== self::CLASS_FILTER_UNASSIGNED, fn ($q) => $q->where('class_grade', $this->filterClass))
            ->when($isIct, fn ($q) => $q->where('is_active', true))
            ->when(! $isIct && $this->filterEnrollment === 'enrolled', function ($q) use ($isCodeClubView) {
                $q->whereHas('user.enrollments', function ($sub) use ($isCodeClubView) {
                    if ($this->filterEnrollmentCourseId) {
                        $sub->where('course_id', $this->filterEnrollmentCourseId);
                    }
                    if (! $isCodeClubView && $this->filterCamp !== 'all') {
                        $sub->where('camp_id', (int) $this->filterCamp);
                    }
                });
            })
            ->when(! $isIct && $this->filterEnrollment === 'not_enrolled', function ($q) use ($isCodeClubView) {
                $q->whereDoesntHave('user.enrollments', function ($sub) use ($isCodeClubView) {
                    if ($this->filterEnrollmentCourseId) {
                        $sub->where('course_id', $this->filterEnrollmentCourseId);
                    }
                    if (! $isCodeClubView && $this->filterCamp !== 'all') {
                        $sub->where('camp_id', (int) $this->filterCamp);
                    }
                });
            })
            ->when(! $isIct && $this->filterClub !== 'all', function ($q) {
                $q->whereHas('user.codeClubMemberships', function ($clubQuery) {
                    $clubQuery->where('code_club_id', (int) $this->filterClub)->where('status', 'active');
                });
            })
            ->when(! $isIct && $this->filterCategory, fn ($q) => $q->where('student_category', $this->filterCategory))
            ->when($isIct && $this->filterReadiness, fn ($q) => $q->where('exam_readiness_status', $this->filterReadiness))
            ->when($isIct && $this->filterModuleId, function ($q) {
                $q->whereHas('user.enrollments', fn ($sub) => $sub->where('course_id', $this->filterModuleId));
            });
    }

    private function availableIctCourses()
    {
        $schoolId = auth()->user()->ictSchoolId();

        if (!$schoolId) {
            return collect();
        }

        return Course::whereHas('schools', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId)->where('is_active', true);
        })
            ->orderBy('title')
            ->get();
    }

    private function findScopedStudent(int $studentId): StudentProfile
    {
        $student = $this->applyStudentScope(StudentProfile::query())
            ->where('id', $studentId)
            ->firstOrFail();

        $this->authorize('update', $student);

        return $student;
    }

    private function applyStudentScope(Builder $query): Builder
    {
        return ProgramScope::applyStudentProfileScope($query, auth()->user());
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function classGradeOptions(Builder $query): \Illuminate\Support\Collection
    {
        return (clone $query)
            ->whereNotNull('class_grade')
            ->where('class_grade', '!=', '')
            ->distinct()
            ->orderBy('class_grade')
            ->pluck('class_grade')
            ->filter()
            ->values();
    }

    private function notifyAdmins(string $title, string $message, array $data = []): void
    {
        $admins = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'supervisor']);
        })->get(['id']);

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => $title,
                'message' => $message,
                'type' => 'info',
                'data' => $data,
                'is_read' => false,
            ]);
        }
    }
}
