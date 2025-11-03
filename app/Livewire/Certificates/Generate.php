<?php

namespace App\Livewire\Certificates;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Generate extends Component
{
    public Course $course;
    public $studentId = null;
    public $issueDate = null;
    public $expirationDate = null;
    public $showPreview = false;
    public $certificateData = [];

    public function mount(Course $course)
    {
        $this->course = $course->load('instructor');
        $this->issueDate = now()->format('Y-m-d');
    }

    public function generateForStudent($studentId)
    {
        $this->studentId = $studentId;
        $this->generateCertificate();
    }

    public function generateCertificate()
    {
        // Check if user has completed the course
        $enrollment = CourseEnrollment::where('user_id', $this->studentId ?? Auth::id())
            ->where('course_id', $this->course->id)
            ->first();

        if (!$enrollment || !$enrollment->completed_at) {
            session()->flash('error', 'Course must be completed before certificate can be generated.');
            return;
        }

        // Check if certificate already exists
        $existing = Certificate::where('user_id', $this->studentId ?? Auth::id())
            ->where('course_id', $this->course->id)
            ->first();

        if ($existing) {
            session()->flash('message', 'Certificate already exists for this course completion.');
            return $this->redirect(route('certificates.show', $existing), navigate: true);
        }

        // Generate certificate
        $certificate = Certificate::create([
            'user_id' => $this->studentId ?? Auth::id(),
            'course_id' => $this->course->id,
            'certificate_number' => $this->generateCertificateNumber(),
            'issue_date' => $this->issueDate ?: now(),
            'expiration_date' => $this->expirationDate,
            'completion_date' => $enrollment->completed_at,
            'verification_status' => 'verified',
            'metadata' => [
                'instructor' => $this->course->instructor->name,
                'course_title' => $this->course->title,
                'completion_percentage' => $enrollment->progress_percentage,
                'lessons_completed' => $enrollment->lessons_completed,
            ],
        ]);

        // Award badge if applicable
        $this->checkCertificateBadge($certificate);

        session()->flash('message', 'Certificate generated successfully!');
        return $this->redirect(route('certificates.show', $certificate), navigate: true);
    }

    public function preview()
    {
        $user = $this->studentId ? \App\Models\User::find($this->studentId) : Auth::user();
        
        $this->certificateData = [
            'user_name' => $user->name,
            'course_title' => $this->course->title,
            'issue_date' => $this->issueDate ?: now()->format('F d, Y'),
            'instructor' => $this->course->instructor->name,
            'certificate_number' => $this->generateCertificateNumber(),
        ];

        $this->showPreview = true;
    }

    private function generateCertificateNumber(): string
    {
        return 'CERT-' . strtoupper(substr(md5(time() . $this->course->id . ($this->studentId ?? Auth::id())), 0, 8)) . '-' . date('Y');
    }

    private function checkCertificateBadge(Certificate $certificate)
    {
        $badge = \App\Models\Badge::where('slug', 'course-master')->first();
        if ($badge && !$certificate->user->badges()->where('badge_id', $badge->id)->exists()) {
            $certificate->user->badges()->attach($badge->id, ['earned_at' => now()]);
            
            // Award points
            if ($certificate->user->points) {
                $certificate->user->points->increment('total_points', $badge->points_reward ?? 200);
            }
        }
    }

    public function render()
    {
        $enrolledStudents = CourseEnrollment::where('course_id', $this->course->id)
            ->whereNotNull('completed_at')
            ->with('user')
            ->get();

        return view('livewire.certificates.generate', [
            'enrolledStudents' => $enrolledStudents,
        ]);
    }
}
