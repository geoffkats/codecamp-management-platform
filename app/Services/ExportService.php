<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Models\Certificate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class ExportService
{
    /**
     * Export assessment results to PDF
     */
    public function exportAssessmentResults(Assessment $assessment, $format = 'pdf')
    {
        $attempts = AssessmentAttempt::where('assessment_id', $assessment->id)
            ->with(['user', 'assessment'])
            ->orderBy('completed_at', 'desc')
            ->get();

        $data = [
            'assessment' => $assessment,
            'attempts' => $attempts,
            'statistics' => [
                'total_attempts' => $attempts->count(),
                'passed' => $attempts->where('is_passed', true)->count(),
                'failed' => $attempts->where('is_passed', false)->count(),
                'average_score' => $attempts->avg('score') ?? 0,
            ],
        ];

        if ($format === 'pdf') {
            return $this->exportToPDF('exports.assessment-results', $data, 'assessment-results-' . $assessment->id . '.pdf');
        }

        return $this->exportToCSV($attempts, 'assessment-results-' . $assessment->id . '.csv');
    }

    /**
     * Export progress report to PDF
     */
    public function exportProgressReport(User $user, $format = 'pdf')
    {
        $enrollments = CourseEnrollment::where('user_id', $user->id)
            ->with(['course', 'course.modules.lessons'])
            ->get();

        $data = [
            'user' => $user,
            'enrollments' => $enrollments,
            'statistics' => [
                'total_courses' => $enrollments->count(),
                'completed_courses' => $enrollments->whereNotNull('completed_at')->count(),
                'in_progress' => $enrollments->whereNull('completed_at')->where('progress_percentage', '>', 0)->count(),
                'total_lessons' => $enrollments->sum('lessons_completed'),
                'average_progress' => $enrollments->avg('progress_percentage') ?? 0,
            ],
            'generated_at' => now(),
        ];

        if ($format === 'pdf') {
            return $this->exportToPDF('exports.progress-report', $data, 'progress-report-' . $user->id . '.pdf');
        }

        return $this->exportToCSV($enrollments, 'progress-report-' . $user->id . '.csv');
    }

    /**
     * Export certificate to PDF
     */
    public function exportCertificate(Certificate $certificate)
    {
        $data = [
            'certificate' => $certificate,
            'user' => $certificate->user,
            'course' => $certificate->course,
            'issue_date' => $certificate->issue_date ?? $certificate->created_at,
        ];

        return $this->exportToPDF('exports.certificate', $data, 'certificate-' . $certificate->id . '.pdf');
    }

    /**
     * Export to PDF
     */
    private function exportToPDF(string $view, array $data, string $filename)
    {
        try {
            // Check if DomPDF is available
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, $data);
                return $pdf->download($filename);
            }
            
            // Fallback: Return HTML view if DomPDF not installed
            Log::warning('DomPDF not installed, returning HTML view instead of PDF');
            return view($view, $data);
        } catch (\Exception $e) {
            Log::error('PDF export failed', [
                'view' => $view,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Export to CSV
     */
    private function exportToCSV($data, string $filename)
    {
        $csvData = [];
        
        if ($data->isEmpty()) {
            return Response::streamDownload(function () {
                echo '';
            }, $filename);
        }

        // Get first item to determine headers
        $firstItem = $data->first();
        
        if ($firstItem instanceof AssessmentAttempt) {
            $csvData[] = ['Student', 'Score', 'Percentage', 'Passed', 'Completed At'];
            foreach ($data as $attempt) {
                $maxScore = $attempt->assessment->questions 
                    ? $attempt->assessment->questions->sum('points') 
                    : 100;
                $percentage = $maxScore > 0 
                    ? ($attempt->score / $maxScore) * 100 
                    : 0;
                    
                $csvData[] = [
                    $attempt->user->name ?? 'N/A',
                    $attempt->score ?? 0,
                    number_format($percentage, 2) . '%',
                    $attempt->is_passed ? 'Yes' : 'No',
                    $attempt->completed_at?->format('Y-m-d H:i:s') ?? 'N/A',
                ];
            }
        } elseif ($firstItem instanceof CourseEnrollment) {
            $csvData[] = ['Course', 'Progress', 'Lessons Completed', 'Enrolled At', 'Completed At'];
            foreach ($data as $enrollment) {
                $csvData[] = [
                    $enrollment->course->title,
                    number_format($enrollment->progress_percentage, 2) . '%',
                    $enrollment->lessons_completed ?? 0,
                    $enrollment->enrolled_at?->format('Y-m-d'),
                    $enrollment->completed_at?->format('Y-m-d'),
                ];
            }
        }

        return Response::streamDownload(function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}

