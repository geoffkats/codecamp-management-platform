<?php

namespace App\Livewire\Certificates;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Test extends Component
{
    public $userId;
    public $courseId;
    public $title = 'Sample Certificate';
    public $description = null;
    public $issuedAt;
    public $certificateNumber = null;
    public $sampleCertificateId = null;

    public $availableUsers = [];
    public $availableCourses = [];

    public function mount()
    {
        if (! Auth::user()?->can('generate_certificates')) {
            abort(403, 'Only staff can access the certificate test page.');
        }

        $this->issuedAt = now()->format('Y-m-d');

        $this->availableUsers = User::orderBy('name')
            ->limit(100)
            ->get(['id', 'name', 'email'])
            ->toArray();

        $this->availableCourses = Course::orderBy('title')
            ->limit(100)
            ->get(['id', 'title'])
            ->toArray();

        if (!$this->userId) {
            $this->userId = Auth::id() ?: ($this->availableUsers[0]['id'] ?? null);
        }

        if (!$this->courseId) {
            $this->courseId = $this->availableCourses[0]['id'] ?? null;
        }

        $latestCertificate = Certificate::orderByDesc('issued_at')->orderByDesc('created_at')->first();
        $this->sampleCertificateId = $latestCertificate?->id;
    }

    public function generateSample()
    {
        $this->validate([
            'userId' => ['required', 'exists:users,id'],
            'courseId' => ['required', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'issuedAt' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'certificateNumber' => ['nullable', 'string', 'max:255'],
        ]);

        $course = Course::with('instructor')->findOrFail($this->courseId);
        $user = User::findOrFail($this->userId);

        $certificateNumber = $this->certificateNumber ?: ('SAMPLE-' . strtoupper(Str::random(8)) . '-' . now()->format('Y'));

        $dataService = app(\App\Services\CertificateDataService::class);
        $modules = $dataService->buildModulesForUser($user, $course->id);

        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'certificate_number' => $certificateNumber,
            'title' => $this->title ?: 'CODE Profile Certificate',
            'description' => $this->description,
            'issued_at' => $this->issuedAt,
            'expires_at' => null,
            'completion_data' => [
                'is_sample' => true,
                'generated_by' => Auth::id(),
                'instructor' => $course->instructor?->name,
                'course_title' => $course->title,
                'modules' => $modules,
            ],
            'file_path' => null,
            'is_verified' => true,
        ]);

        $this->sampleCertificateId = $certificate->id;

        session()->flash('message', 'Sample certificate generated successfully.');
    }

    public function render()
    {
        $sampleCertificate = $this->sampleCertificateId
            ? Certificate::with(['user', 'course'])->find($this->sampleCertificateId)
            : null;

        return view('livewire.certificates.test', [
            'sampleCertificate' => $sampleCertificate,
        ]);
    }
}
