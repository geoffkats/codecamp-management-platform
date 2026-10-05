<?php

namespace App\Livewire\Certificates;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Services\CertificateDataService;
use App\Services\CertificatePdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Certificates')]
class Generate extends Component
{
    use WithFileUploads, WithPagination;

    /** issue | issued | single | csv */
    #[Url(except: 'issue')]
    public string $tab = 'issue';

    // Ready to issue (bulk)
    #[Url(as: 'course', except: null)]
    public ?int $courseFilter = null;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    /** pending | new | update | issued | all */
    #[Url(as: 'status', except: 'pending')]
    public string $statusFilter = 'pending';

    /** @var array<int, int> */
    public array $selected = [];

    /** merge | separate */
    public string $issueMode = 'merge';

    public bool $downloadZip = true;

    // Issued list
    public string $issuedSearch = '';

    // One student
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

    // CSV import
    public $csvFile = null;

    /** @var array<int, array<string, mixed>> */
    public array $bulkCandidates = [];

    public string $bulkError = '';

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
        $this->authorizeStaff();

        $this->signatureDate = now()->format('Y-m-d');

        if ($course?->exists) {
            $this->courseFilter = $course->id;
            $this->selectedCourseId = $course->id;
        }
    }

    public function updating($property): void
    {
        if (in_array($property, ['courseFilter', 'search', 'statusFilter'], true)) {
            $this->selected = [];
            $this->resetPage();
        }

        if ($property === 'issuedSearch') {
            $this->resetPage('issuedPage');
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['issue', 'issued', 'single', 'csv'], true) ? $tab : 'issue';
        $this->statusMessage = '';
    }

    public function showStatus(string $status): void
    {
        $this->setTab('issue');
        $this->statusFilter = $status;
        $this->selected = [];
        $this->resetPage();
    }

    // ── Ready to issue ────────────────────────────────────────────────

    /** @return Collection<int, array<string, mixed>> */
    protected function candidates(?string $status = null): Collection
    {
        return $this->dataService->certificateCandidates(
            Auth::user(),
            $this->courseFilter,
            trim($this->search),
            $status ?? $this->statusFilter,
        );
    }

    public function toggle(int $userId): void
    {
        $this->selected = in_array($userId, $this->selected, true)
            ? array_values(array_diff($this->selected, [$userId]))
            : [...$this->selected, $userId];
    }

    public function togglePage(string $ids): void
    {
        $pageIds = array_map('intval', array_filter(explode(',', $ids)));
        $allSelected = $pageIds !== [] && array_diff($pageIds, $this->selected) === [];

        $this->selected = $allSelected
            ? array_values(array_diff($this->selected, $pageIds))
            : array_values(array_unique([...$this->selected, ...$pageIds]));
    }

    public function selectAllMatching(): void
    {
        $this->selected = $this->candidates()->pluck('user_id')->all();
    }

    public function clearSelected(): void
    {
        $this->selected = [];
    }

    public function issueOne(int $userId)
    {
        $this->selected = [$userId];

        return $this->issueSelected();
    }

    public function issueSelected()
    {
        $this->authorizeStaff();

        if ($this->selected === []) {
            $this->flash('Select at least one student.', 'error');

            return null;
        }

        $rows = $this->candidates('all')->keyBy('user_id');
        $meta = $this->certificateMeta() + ['mode' => $this->issueMode === 'separate' ? 'separate' : 'merge'];
        $certificateIds = [];
        $courseCount = 0;
        $courses = Course::whereIn('id', $rows->flatMap(fn ($r) => array_column($r['courses'], 'id'))->unique())->get()->keyBy('id');

        foreach ($this->selected as $userId) {
            $row = $rows->get($userId);
            $user = $row ? User::with('studentProfile')->find($userId) : null;
            if (! $user) {
                continue;
            }

            // New courses go on the certificate; if everything is already covered, refresh them all.
            $courseIds = collect($row['courses'])->where('covered', false)->pluck('id');
            if ($courseIds->isEmpty()) {
                $courseIds = collect($row['courses'])->pluck('id');
            }

            foreach ($courseIds as $courseId) {
                $course = $courses->get($courseId);
                if (! $course) {
                    continue;
                }

                $certificate = $this->dataService->createOrUpdateCertificate(
                    $user,
                    $course,
                    $this->dataService->modulesForCourse($user, $course),
                    now(),
                    $meta,
                );
                $certificateIds[$certificate->id] = true;
                $courseCount++;
            }
        }

        $students = count($this->selected);
        $this->selected = [];

        if ($certificateIds === []) {
            $this->flash('Nothing to issue for the selected students.', 'error');

            return null;
        }

        $this->flash(sprintf(
            '%d %s issued or updated for %d %s (%d %s).',
            count($certificateIds), str('certificate')->plural(count($certificateIds)),
            $students, str('student')->plural($students),
            $courseCount, str('course')->plural($courseCount),
        ));

        return $this->downloadZip ? $this->zipDownload(array_keys($certificateIds)) : null;
    }

    public function downloadSelectedIssued(string $ids)
    {
        $this->authorizeStaff();

        $ids = array_map('intval', array_filter(explode(',', $ids)));

        return $ids === [] ? null : $this->zipDownload($ids);
    }

    /** @param  array<int, int>  $certificateIds */
    protected function zipDownload(array $certificateIds)
    {
        $pdfService = app(CertificatePdfService::class);
        $certificates = $this->issuedQuery()->whereIn('certificates.id', $certificateIds)->with('user.studentProfile')->get();

        if ($certificates->count() === 1) {
            $certificate = $certificates->first();

            return response()->streamDownload(fn () => print($pdfService->output($certificate)), $pdfService->filename($certificate), ['Content-Type' => 'application/pdf']);
        }

        $zipPath = storage_path('app/temp/certificates_'.now()->format('Ymd_His').'_'.uniqid().'.zip');
        @mkdir(dirname($zipPath), 0755, true);

        $zip = new \ZipArchive;
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($certificates as $certificate) {
            $zip->addFromString($pdfService->filename($certificate), $pdfService->output($certificate));
        }
        $zip->close();

        return response()->download($zipPath, 'certificates_'.now()->format('Y-m-d').'.zip')->deleteFileAfterSend(true);
    }

    // ── Issued list ────────────────────────────────────────────────────

    protected function issuedQuery()
    {
        $staff = Auth::user();
        $courseIds = $this->dataService->coursesForGenerator($staff)->pluck('id');
        $everything = $staff->isAdmin() || $staff->isSupervisor() || $staff->isOperationsManager();

        return Certificate::query()->when(! $everything, fn ($q) => $q->whereIn('course_id', $courseIds));
    }

    // ── One student ───────────────────────────────────────────────────

    public function updatedSelectedCourseId(): void
    {
        $this->selectedUserId = null;
        $this->studentSearch = '';
        $this->manualEdit = false;
        $this->resetCertificateFields();
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
        $user = $this->selectedUserId ? User::with('studentProfile')->find($this->selectedUserId) : null;
        $course = $this->selectedCourseId ? Course::find($this->selectedCourseId) : null;

        if (! $user || ! $course) {
            return;
        }

        $data = $this->dataService->resolveForUser($user, $course);
        $this->candidateName = $data['candidateName'];
        $this->candidateNo = $data['candidateNo'];
        $this->signatureDate = $data['signatureDate'];
        $this->modules = collect($this->dataService->modulesForCourse($user, $course))
            ->map(fn ($row) => ['name' => $row['name'], 'version' => $row['version'], 'date' => $row['date']])
            ->all();
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
            'candidateName' => 'required|string|max:120',
            'candidateNo' => 'required|string|max:40',
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

    public function issueAndDownload()
    {
        $this->authorizeStaff();
        $this->validate();

        $user = User::with('studentProfile')->find($this->selectedUserId);
        $course = Course::find($this->selectedCourseId);

        if (! $user || ! $course) {
            return null;
        }

        $certificate = $this->dataService->createOrUpdateCertificate(
            $user,
            $course,
            $this->modules,
            \Carbon\Carbon::parse($this->signatureDate),
            $this->certificateMeta() + ['mode' => $this->issueMode === 'separate' ? 'separate' : 'merge'],
        );

        $this->flash('Certificate '.$certificate->certificate_number.' saved to the student profile.');

        return $this->zipDownload([$certificate->id]);
    }

    public function generate()
    {
        $this->authorizeStaff();
        $this->validate();

        $pdf = $this->buildPdf([
            'candidateName' => $this->candidateName,
            'candidateNo' => $this->candidateNo,
            'signatureDate' => $this->signatureDate,
            'modules' => $this->modules,
        ]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'certificate_'.str()->slug($this->candidateName).'_'.now()->format('Ymd_His').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    // ── CSV import ────────────────────────────────────────────────────

    public function updatedCsvFile(): void
    {
        $this->bulkError = '';
        $this->bulkCandidates = [];

        if (! $this->csvFile) {
            return;
        }

        try {
            $rows = array_map('str_getcsv', file($this->csvFile->getRealPath()));
            $header = array_map('trim', array_shift($rows));

            foreach (['candidate_name', 'candidate_no', 'module_name', 'module_version', 'module_date', 'signature_date'] as $col) {
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
                $grouped[$key] ??= [
                    'candidateName' => trim($data['candidate_name']),
                    'candidateNo' => trim($data['candidate_no']),
                    'signatureDate' => trim($data['signature_date']),
                    'modules' => [],
                ];
                $grouped[$key]['modules'][] = [
                    'name' => trim($data['module_name']),
                    'version' => trim($data['module_version']),
                    'date' => trim($data['module_date']),
                ];
            }

            $this->bulkCandidates = array_values($grouped);
        } catch (\Throwable $e) {
            $this->bulkError = 'Could not parse CSV: '.$e->getMessage();
        }
    }

    public function generateBulk()
    {
        $this->authorizeStaff();

        if ($this->bulkCandidates === []) {
            $this->bulkError = 'No candidates loaded. Upload a valid CSV first.';

            return null;
        }

        $zipPath = storage_path('app/temp/certificates_'.now()->format('Ymd_His').'.zip');
        @mkdir(dirname($zipPath), 0755, true);

        $zip = new \ZipArchive;
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($this->bulkCandidates as $candidate) {
            $filename = 'certificate_'.str()->slug($candidate['candidateName']).'_'.$candidate['candidateNo'].'.pdf';
            $zip->addFromString($filename, $this->buildPdf($candidate)->output());
        }
        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    // ── Helpers ───────────────────────────────────────────────────────

    private function flash(string $message, string $type = 'success'): void
    {
        $this->statusMessage = $message;
        $this->statusType = $type;
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
        return [
            'course' => $course ?? Course::find($this->selectedCourseId),
            'student' => $student ?? User::find($this->selectedUserId),
            'signatory_key' => $this->signatoryProfile !== 'auto' ? $this->signatoryProfile : null,
            'custom_signatory' => trim($this->customSignatoryOverride) ?: null,
        ];
    }

    private function buildPdf(array $data): \Barryvdh\DomPDF\PDF
    {
        $payload = $this->dataService->formatPayload(
            candidateName: $data['candidateName'],
            candidateNo: $data['candidateNo'],
            signatureDate: \Carbon\Carbon::parse($data['signatureDate']),
            modules: $data['modules'],
            context: $this->pdfContext(),
        );

        return Pdf::loadView(config('certificate.html_template', 'certificates.profile'), $payload)
            ->setPaper('a4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false, 'defaultFont' => 'DejaVu Sans', 'dpi' => 150]);
    }

    /**
     * Where the single-student certificate will land and the full module list it will print.
     *
     * @return array{target: ?Certificate, modules: array<int, array<string, mixed>>, otherCourses: array<int, string>}
     */
    private function singleTarget(): array
    {
        $user = $this->selectedUserId ? User::find($this->selectedUserId) : null;
        if (! $user || ! $this->selectedCourseId) {
            return ['target' => null, 'modules' => $this->modules, 'otherCourses' => []];
        }

        $certificates = Certificate::where('user_id', $user->id)->orderBy('created_at')->orderBy('id')->get();
        $courseId = (int) $this->selectedCourseId;
        $target = $this->issueMode === 'separate'
            ? $certificates->first(fn ($c) => $this->dataService->isSeparate($c) && $this->dataService->coursesOn($c) === [$courseId])
            : ($certificates->first(fn ($c) => in_array($courseId, $this->dataService->coursesOn($c), true))
                ?? $certificates->first(fn ($c) => ! $this->dataService->isSeparate($c)));

        if (! $target) {
            return ['target' => null, 'modules' => $this->modules, 'otherCourses' => []];
        }

        $kept = array_values(array_filter($this->dataService->modulesOn($target), fn ($row) => (int) $row['course_id'] !== $courseId));
        $otherIds = array_values(array_diff($this->dataService->coursesOn($target), [$courseId]));

        return [
            'target' => $target,
            'modules' => [...$kept, ...$this->modules],
            'otherCourses' => Course::whereIn('id', $otherIds)->pluck('title')->all(),
        ];
    }

    public function render()
    {
        $staff = Auth::user();
        $courses = $this->dataService->coursesForGenerator($staff);
        $data = ['courses' => $courses, 'signatoryProfiles' => $this->dataService->signatoryProfileOptions()];

        $everyone = $this->dataService->certificateCandidates($staff, $this->courseFilter, '', 'all');
        $data['counts'] = [
            'new' => $everyone->where('status', 'new')->count(),
            'update' => $everyone->where('status', 'update')->count(),
            'issued' => $everyone->where('status', 'issued')->count(),
            'certificates' => (clone $this->issuedQuery())->count(),
        ];

        if ($this->tab === 'issue') {
            $candidates = $this->candidates();
            $perPage = 25;
            $page = max(1, (int) $this->getPage());
            $data['candidates'] = new \Illuminate\Pagination\LengthAwarePaginator(
                $candidates->forPage($page, $perPage)->values(),
                $candidates->count(),
                $perPage,
                $page,
                ['path' => route('certificates.generator'), 'pageName' => 'page'],
            );
            $data['matchingCount'] = $candidates->count();
        }

        if ($this->tab === 'issued') {
            $courseTitles = Course::pluck('title', 'id');
            $data['issued'] = $this->issuedQuery()
                ->with(['user:id,name', 'user.studentProfile:id,user_id,full_name,student_id'])
                ->when(trim($this->issuedSearch) !== '', function ($q) {
                    $term = '%'.trim($this->issuedSearch).'%';
                    $q->where(fn ($w) => $w->where('certificate_number', 'like', $term)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)
                            ->orWhereHas('studentProfile', fn ($p) => $p->where('full_name', 'like', $term))));
                })
                ->latest('issued_at')
                ->paginate(20, pageName: 'issuedPage');
            $data['courseTitles'] = $courseTitles;
        }

        if ($this->tab === 'single') {
            $data['studentResults'] = $this->selectedCourseId
                ? $this->dataService->searchStudentsForCertificate($this->selectedCourseId, $this->studentSearch, $this->eligibilityFilter)
                : collect();

            $data['selectedStudent'] = null;
            if ($this->selectedUserId && $this->selectedCourseId) {
                $enrollment = CourseEnrollment::where('user_id', $this->selectedUserId)
                    ->where('course_id', $this->selectedCourseId)
                    ->with(['user.studentProfile', 'course.modules'])
                    ->first();
                $data['selectedStudent'] = $enrollment ? $this->dataService->summarizeStudentEligibility($enrollment) : null;
            }

            $single = $this->singleTarget();
            $data['single'] = $single;
            $data['previewUrl'] = $this->candidateName
                ? $this->dataService->buildPreviewUrl($this->candidateName, $this->candidateNo, $this->signatureDate, $single['modules'], $this->pdfContext())
                : null;
            $data['resolvedSignatoryKey'] = ($this->selectedUserId && $this->selectedCourseId)
                ? $this->dataService->resolveSignatoryKey(Course::find($this->selectedCourseId), User::find($this->selectedUserId), $this->signatoryProfile !== 'auto' ? $this->signatoryProfile : null)
                : 'default';
        }

        return view('livewire.certificates.generate', $data);
    }
}
