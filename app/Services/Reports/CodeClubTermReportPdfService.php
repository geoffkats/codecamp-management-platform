<?php

namespace App\Services\Reports;

use App\Models\CodeClub;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CodeClubTermReportPdfService
{
    public function __construct(
        private CodeClubTermReportService $reportService,
    ) {}

    public function render(
        CodeClub $club,
        User $student,
        bool $download = true,
        ?Carbon $from = null,
        ?Carbon $to = null,
        ?string $termKey = null,
    ) {
        $view = config('codeclub-reports.html_template', 'reports.codeclub-student-report');
        $viewData = $this->reportService->build($club, $student, $from, $to, $termKey);
        $filename = $this->filename($club, $student);

        if ($this->shouldUseDomPdf()) {
            return $this->renderWithDomPdf($view, $viewData, $filename, $download);
        }

        if ($this->canUseSnappyBinary()) {
            try {
                $pdf = app('snappy.pdf.wrapper');
                $pdf->setOption('enable-local-file-access', true);
                $pdf->setOption('page-size', 'A4');
                $pdf->setOption('orientation', 'Portrait');
                $pdf->loadView($view, $viewData);

                return $download ? $pdf->download($filename) : $pdf->inline($filename);
            } catch (\Throwable $e) {
                Log::error('Code Club term report PDF failed (snappy), falling back to DomPDF', [
                    'club_id' => $club->id,
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->renderWithDomPdf($view, $viewData, $filename, $download);
    }

    public function renderSchoolSummary(
        CodeClub $club,
        bool $download = true,
        ?Carbon $from = null,
        ?Carbon $to = null,
        ?string $termKey = null,
    ) {
        $view = config('codeclub-reports.school_summary_template', 'reports.codeclub-school-summary');
        $viewData = $this->reportService->buildSchoolSummary($club, $from, $to, $termKey);
        $filename = $this->schoolFilename($club);

        if ($this->shouldUseDomPdf()) {
            return $this->renderWithDomPdf($view, $viewData, $filename, $download);
        }

        if ($this->canUseSnappyBinary()) {
            try {
                $pdf = app('snappy.pdf.wrapper');
                $pdf->setOption('enable-local-file-access', true);
                $pdf->setOption('page-size', 'A4');
                $pdf->setOption('orientation', 'Portrait');
                $pdf->loadView($view, $viewData);

                return $download ? $pdf->download($filename) : $pdf->inline($filename);
            } catch (\Throwable $e) {
                Log::error('Code Club school summary PDF failed (snappy), falling back to DomPDF', [
                    'club_id' => $club->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->renderWithDomPdf($view, $viewData, $filename, $download);
    }

    public function renderHtml(
        CodeClub $club,
        User $student,
        ?Carbon $from = null,
        ?Carbon $to = null,
        ?string $termKey = null,
    ): string {
        $view = config('codeclub-reports.html_template', 'reports.codeclub-student-report');
        $viewData = $this->reportService->build($club, $student, $from, $to, $termKey);

        return view($view, $viewData)->render();
    }

    public function generatePdfContent(
        CodeClub $club,
        User $student,
        ?Carbon $from = null,
        ?Carbon $to = null,
        ?string $termKey = null,
    ): string {
        $view = config('codeclub-reports.html_template', 'reports.codeclub-student-report');
        $viewData = $this->reportService->build($club, $student, $from, $to, $termKey);

        if ($this->shouldUseDomPdf()) {
            return $this->buildDomPdf($view, $viewData)->output();
        }

        if ($this->canUseSnappyBinary()) {
            try {
                $pdf = app('snappy.pdf.wrapper');
                $pdf->setOption('enable-local-file-access', true);
                $pdf->setOption('page-size', 'A4');
                $pdf->setOption('orientation', 'Portrait');
                $pdf->loadView($view, $viewData);

                return $pdf->output();
            } catch (\Throwable $e) {
                Log::error('Code Club term report PDF failed (snappy), falling back to DomPDF', [
                    'club_id' => $club->id,
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->buildDomPdf($view, $viewData)->output();
    }

    public function filenameFor(CodeClub $club, User $student): string
    {
        return $this->filename($club, $student);
    }

    public function zipFilename(CodeClub $club): string
    {
        $slug = str($club->name)->slug();

        return 'codeclub-term-reports-'.$slug.'-'.now()->format('Y-m-d').'.zip';
    }

    public function schoolFilename(CodeClub $club): string
    {
        $slug = str($club->name)->slug();

        return 'codeclub-school-summary-'.$slug.'-'.now()->format('Y-m-d').'.pdf';
    }

    private function renderWithDomPdf(string $view, array $viewData, string $filename, bool $download)
    {
        $pdf = $this->buildDomPdf($view, $viewData);

        return $download ? $pdf->download($filename) : $pdf->stream($filename);
    }

    private function buildDomPdf(string $view, array $viewData)
    {
        $options = config('codeclub-reports.dompdf', []);

        return Pdf::loadView($view, $viewData)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'isFontSubsettingEnabled' => true,
                'defaultFont' => $options['default_font'] ?? 'DejaVu Sans',
                'dpi' => (int) ($options['dpi'] ?? 150),
                'chroot' => $options['chroot'] ?? [public_path(), storage_path('app/public')],
                'defaultMediaType' => 'print',
                'fontHeightRatio' => (float) ($options['font_height_ratio'] ?? 1.0),
            ]);
    }

    private function filename(CodeClub $club, User $student): string
    {
        $slug = str($club->name)->slug();
        $studentSlug = str($student->studentProfile?->student_id ?? $student->name ?? 'student')->slug();

        return "codeclub-report-{$slug}-{$studentSlug}.pdf";
    }

    private function shouldUseDomPdf(): bool
    {
        return (bool) config('codeclub-reports.use_dompdf', true);
    }

    private function canUseSnappyBinary(): bool
    {
        if (! app()->bound('snappy.pdf.wrapper')) {
            return false;
        }

        $binary = (string) config('snappy.pdf.binary', '');

        if ($binary === '') {
            return false;
        }

        $looksLikePath = str_contains($binary, '\\') || str_contains($binary, '/');

        if (! $looksLikePath) {
            return true;
        }

        return file_exists($binary);
    }
}
