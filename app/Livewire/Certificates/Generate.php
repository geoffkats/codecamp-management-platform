<?php

namespace App\Livewire\Certificates;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Services\CertificateDataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Generate extends Component
{
    use WithFileUploads;

    public string $mode = 'single';

    public ?int $selectedCourseId = null;

    public ?int $selectedUserId = null;

    public string $studentSearch = '';

    public string $eligibilityFilter = 'ready';

    public bool $manualEdit = false;

    public string $candidateName = '';

    public string $candidateNo = '';

    public string $signatureDate = '';

    /** @var array<int, array{name: string, version: string, date: string}> */
    public array $modules = [];

    public $csvFile = null;

    /** @var array<int, array<string, mixed>> */
    public array $bulkCandidates = [];

    public string $bulkError = '';

    /** @var array<int, int> */
    public array $bulkSelectedUserIds = [];

    public string $statusMessage = '';

    public string $statusType = 'success';

    public string $signatoryProfile = 'auto';

    public string $customSignatoryOverride = '';

    protected CertificateDataService $dataService;

    public function boot(CertificateDataService $dataService): void
    {
        $this->dataService = $dataService;
    }

    public function mount(?Course $course = null): void
    {
        $user = Auth::user();

        if (! $user || ! $user->can('generate_certificates')) {
            abort(403, 'Only staff can generate or issue certificates.');
        }

        $this->signatureDate = now()->format('Y-m-d');
        $this->selectedCourseId = $course?->id;
    }

    public function updatedSelectedCourseId(): void
    {
        $this->selectedUserId = null;
        $this->studentSearch = '';
        $this->manualEdit = false;
        $this->resetCertificateFields();
        $this->syncBulkSelection();
    }

    public function updatedStudentSearch(): void
    {
        if ($this->selectedUserId && $this->studentSearch !== '') {
            return;
        }
    }

    public function updatedEligibilityFilter(): void
    {
        $this->syncBulkSelection();
    }

    public function updatedMode(): void
    {
        if ($this->mode === 'bulk') {
            $this->syncBulkSelection();
        }
    }

    public function selectStudent(int $userId): void
    {
        $this->selectedUserId = $userId;
        $this->manualEdit = false;
        $this->loadFromSelection();
    }

    public function clearSelection(): void
    {
        $this->selectedUserId = null;
        $this->manualEdit = false;
        $this->resetCertificateFields();
    }

    public function loadFromSelection(): void
    {
        if (! $this->selectedUserId || ! $this->selectedCourseId) {
            return;
        }

        $user = User::with('studentProfile')->find($this->selectedUserId);
        $course = Course::find($this->selectedCourseId);

        if (! $user || ! $course) {
            return;
        }

        $this->prefillFromUser($user, $course);
    }

    protected function prefillFromUser(User $user, ?Course $course = null): void
    {
        $data = $this->dataService->resolveForUser($user, $course);

        $this->candidateName = $data['candidateName'];
        $this->candidateNo = $data['candidateNo'];
        $this->signatureDate = $data['signatureDate'];
        $this->modules = collect($data['modules'])->map(fn ($row) => [
            'name' => $row['name'],
            'version' => $row['version'],
            'date' => $row['date'],
        ])->all();

        if ($this->modules === []) {
            $this->modules = [[
                'name' => $course?->title ?? 'Course Completion',
                'version' => config('certificate.default_module_version', '1.0'),
                'date' => now()->format('Y-m-d'),
            ]];
        }
    }

    protected function resetCertificateFields(): void
    {
        $this->candidateName = '';
        $this->candidateNo = '';
        $this->signatureDate = now()->format('Y-m-d');
        $this->modules = [];
        $this->statusMessage = '';
    }

    protected function rules(): array
    {
        return [
            'candidateName' => 'required_unless:mode,bulk|string|max:120',
            'candidateNo' => 'required_unless:mode,bulk|string|max:40',
            'signatureDate' => 'required|date',
            'modules' => 'array|min:1',
            'modules.*.name' => 'required|string|max:100',
            'modules.*.version' => 'required|string|max:60',
            'modules.*.date' => 'required|date',
        ];
    }

    public function addModule(): void
    {
        $this->modules[] = ['name' => '', 'version' => '1.0', 'date' => now()->format('Y-m-d')];
    }

    public function removeModule(int $index): void
    {
        if (count($this->modules) > 1) {
            array_splice($this->modules, $index, 1);
        }
    }

    public function toggleBulkStudent(int $userId): void
    {
        if (in_array($userId, $this->bulkSelectedUserIds, true)) {
            $this->bulkSelectedUserIds = array_values(array_filter(
                $this->bulkSelectedUserIds,
                fn ($id) => $id !== $userId
            ));
        } else {
            $this->bulkSelectedUserIds[] = $userId;
        }
    }

    public function selectAllBulkReady(): void
    {
        $this->bulkSelectedUserIds = $this->eligibleBulkStudents()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function clearBulkSelection(): void
    {
        $this->bulkSelectedUserIds = [];
    }

    protected function syncBulkSelection(): void
    {
        if ($this->mode !== 'bulk' || ! $this->selectedCourseId) {
            $this->bulkSelectedUserIds = [];

            return;
        }

        $this->bulkSelectedUserIds = $this->eligibleBulkStudents()
            ->filter(fn ($row) => ! $row['has_certificate'])
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function eligibleBulkStudents(): Collection
    {
        if (! $this->selectedCourseId) {
            return collect();
        }

        return $this->dataService->searchStudentsForCertificate(
            $this->selectedCourseId,
            $this->studentSearch,
            'ready',
            200,
        );
    }

    public function updatedCsvFile(): void
    {
        $this->bulkError = '';
        $this->bulkCandidates = [];

        if (! $this->csvFile) {
            return;
        }

        try {
            $path = $this->csvFile->getRealPath();
            $rows = array_map('str_getcsv', file($path));
            $header = array_map('trim', array_shift($rows));

            $required = ['candidate_name', 'candidate_no', 'module_name', 'module_version', 'module_date', 'signature_date'];
            foreach ($required as $col) {
                if (! in_array($col, $header, true)) {
                    $this->bulkError = "CSV is missing column: {$col}";

                    return;
                }
            }

            $grouped = [];
            foreach ($rows as $row) {
                if (count($row) !== count($header)) {
                    continue;
                }
                $data = array_combine($header, $row);
                $key = $data['candidate_no'];
                if (! isset($grouped[$key])) {
                    $grouped[$key] = [
                        'candidateName' => trim($data['candidate_name']),
                        'candidateNo' => trim($data['candidate_no']),
                        'signatureDate' => trim($data['signature_date']),
                        'modules' => [],
                    ];
                }
                $grouped[$key]['modules'][] = [
                    'name' => trim($data['module_name']),
                    'version' => trim($data['module_version']),
                    'date' => trim($data['module_date']),
                ];
            }

            $this->bulkCandidates = array_values($grouped);
        } catch (\Throwable $e) {
            $this->bulkError = 'Could not parse CSV: ' . $e->getMessage();
        }
    }

    public function issueAndDownload()
    {
        $this->validate();

        $user = User::with('studentProfile')->find($this->selectedUserId);
        $course = Course::find($this->selectedCourseId);

        if ($user && $course) {
            $this->dataService->createOrUpdateCertificate(
                $user,
                $course,
                $this->modules,
                \Carbon\Carbon::parse($this->signatureDate),
                $this->certificateMeta(),
            );

            $this->statusMessage = 'Certificate saved to the student profile.';
            $this->statusType = 'success';
        }

        return $this->downloadCurrentPdf();
    }

    public function generate()
    {
        $this->authorizeStaff();
        $this->validate();

        return $this->downloadCurrentPdf();
    }

    public function generateBulkFromCourse()
    {
        $this->authorizeStaff();

        if (! $this->selectedCourseId) {
            $this->bulkError = 'Select a course first.';

            return;
        }

        $course = Course::findOrFail($this->selectedCourseId);
        $userIds = $this->bulkSelectedUserIds;

        if ($userIds === []) {
            $this->bulkError = 'Select at least one student.';

            return;
        }

        $zipPath = storage_path('app/temp/certificates_' . now()->format('Ymd_His') . '.zip');
        @mkdir(dirname($zipPath), 0755, true);

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($userIds as $userId) {
            $user = User::with('studentProfile')->find($userId);
            if (! $user) {
                continue;
            }

            $data = $this->dataService->resolveForUser($user, $course);
            $modules = collect($data['modules'])->map(fn ($row) => [
                'name' => $row['name'],
                'version' => $row['version'],
                'date' => $row['date'],
            ])->all();

            $this->dataService->createOrUpdateCertificate(
                $user,
                $course,
                $modules,
                \Carbon\Carbon::parse($data['signatureDate']),
                array_merge($this->certificateMeta(), [
                    'signatory_key' => $this->signatoryProfile === 'auto'
                        ? $this->dataService->resolveSignatoryKey($course, $user)
                        : ($this->signatoryProfile !== 'auto' ? $this->signatoryProfile : null),
                ]),
            );

            $pdf = $this->buildPdf([
                'candidateName' => $data['candidateName'],
                'candidateNo' => $data['candidateNo'],
                'signatureDate' => $data['signatureDate'],
                'modules' => $modules,
            ], $user, $course);

            $filename = 'certificate_' . str()->slug($data['candidateName']) . '_' . $data['candidateNo'] . '.pdf';
            $zip->addFromString($filename, $pdf->output());
        }

        $zip->close();

        $this->statusMessage = count($userIds) . ' certificate(s) issued and packaged.';
        $this->statusType = 'success';

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function generateBulk()
    {
        $this->authorizeStaff();

        if ($this->bulkCandidates === []) {
            $this->bulkError = 'No candidates loaded. Upload a valid CSV or use course bulk generation.';

            return;
        }

        $zipPath = storage_path('app/temp/certificates_' . now()->format('Ymd_His') . '.zip');
        @mkdir(dirname($zipPath), 0755, true);

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($this->bulkCandidates as $candidate) {
            $pdf = $this->buildPdf($candidate);
            $filename = 'certificate_' . str()->slug($candidate['candidateName']) . '_' . $candidate['candidateNo'] . '.pdf';
            $zip->addFromString($filename, $pdf->output());
        }

        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    private function authorizeStaff(): void
    {
        if (! Auth::user()?->can('generate_certificates')) {
            abort(403, 'Only staff can generate or issue certificates.');
        }
    }

    private function certificateMeta(): array
    {
        return [
            'issuer' => Auth::user(),
            'signatory_key' => $this->signatoryProfile !== 'auto' ? $this->signatoryProfile : null,
            'custom_signatory' => trim($this->customSignatoryOverride) ?: null,
        ];
    }

    private function pdfContext(?User $student = null, ?Course $course = null): array
    {
        $student ??= User::find($this->selectedUserId);
        $course ??= Course::find($this->selectedCourseId);

        return [
            'course' => $course,
            'student' => $student,
            'signatory_key' => $this->signatoryProfile !== 'auto' ? $this->signatoryProfile : null,
            'custom_signatory' => trim($this->customSignatoryOverride) ?: null,
        ];
    }

    private function downloadCurrentPdf()
    {
        $pdf = $this->buildPdf([
            'candidateName' => $this->candidateName,
            'candidateNo' => $this->candidateNo,
            'signatureDate' => $this->signatureDate,
            'modules' => $this->modules,
        ]);

        $filename = 'certificate_' . str()->slug($this->candidateName) . '_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    private function buildPdf(array $data, ?User $student = null, ?Course $course = null): \Barryvdh\DomPDF\PDF
    {
        $payload = $this->dataService->formatPayload(
            candidateName: $data['candidateName'],
            candidateNo: $data['candidateNo'],
            signatureDate: \Carbon\Carbon::parse($data['signatureDate']),
            modules: $data['modules'],
            context: $this->pdfContext($student, $course),
        );

        return Pdf::loadView(config('certificate.html_template', 'certificates.profile'), $payload)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 150,
            ]);
    }

    public function render()
    {
        $staff = Auth::user();
        $courses = $this->dataService->coursesForGenerator($staff);

        $studentResults = $this->selectedCourseId
            ? $this->dataService->searchStudentsForCertificate(
                $this->selectedCourseId,
                $this->studentSearch,
                $this->eligibilityFilter,
            )
            : collect();

        $selectedStudent = null;
        $existingCertificate = null;

        if ($this->selectedUserId && $this->selectedCourseId) {
            $enrollment = CourseEnrollment::query()
                ->where('user_id', $this->selectedUserId)
                ->where('course_id', $this->selectedCourseId)
                ->with(['user.studentProfile', 'course.modules'])
                ->first();

            if ($enrollment) {
                $selectedStudent = $this->dataService->summarizeStudentEligibility($enrollment);
            }

            $existingCertificate = Certificate::query()
                ->where('user_id', $this->selectedUserId)
                ->where('course_id', $this->selectedCourseId)
                ->first();
        }

        $bulkStudents = $this->mode === 'bulk' && $this->selectedCourseId
            ? $this->eligibleBulkStudents()
            : collect();

        $previewUrl = $this->candidateName
            ? $this->dataService->buildPreviewUrl(
                $this->candidateName,
                $this->candidateNo,
                $this->signatureDate,
                $this->modules,
                $this->pdfContext(),
            )
            : null;

        $resolvedSignatoryKey = ($this->selectedUserId && $this->selectedCourseId)
            ? $this->dataService->resolveSignatoryKey(
                Course::find($this->selectedCourseId),
                User::find($this->selectedUserId),
                $this->signatoryProfile !== 'auto' ? $this->signatoryProfile : null,
            )
            : 'default';

        $auditTrail = $existingCertificate
            ? data_get($existingCertificate->completion_data, 'issued_by')
            : null;

        return view('livewire.certificates.generate', [
            'courses' => $courses,
            'studentResults' => $studentResults,
            'selectedStudent' => $selectedStudent,
            'existingCertificate' => $existingCertificate,
            'bulkStudents' => $bulkStudents,
            'previewUrl' => $previewUrl,
            'signatoryProfiles' => $this->dataService->signatoryProfileOptions(),
            'resolvedSignatoryKey' => $resolvedSignatoryKey,
            'auditTrail' => $auditTrail,
        ]);
    }
}
