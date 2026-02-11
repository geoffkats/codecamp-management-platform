<?php

namespace App\Services;

use App\Models\Certificate;
use Illuminate\Support\Arr;

class CertificatePdfService
{
    public function render(Certificate $certificate, bool $download = true)
    {
        $name = $certificate->user?->name ?? 'Student';
        $courseTitle = $certificate->course?->title ?? 'Course';
        $issueDate = ($certificate->issued_at ?? $certificate->created_at)?->format('F d, Y') ?? now()->format('F d, Y');
        $certificateNumber = $certificate->certificate_number ?? 'CERT-' . $certificate->id;
        $version = data_get($certificate->completion_data, 'version', 'v1');
        $layout = config('certificate.layout');
        $backgroundImage = config('certificate.background_image');
        $page = config('certificate.page');
        $unit = config('certificate.unit', 'mm');

        $view = config('certificate.html_template', 'certificates.ict');
        $filename = 'certificate-' . $certificate->id . '.pdf';

        $pdf = app('snappy.pdf.wrapper');
        $pdf->setOption('enable-local-file-access', true);
        $pdf->loadView($view, [
            'certificate' => $certificate,
            'studentName' => $name,
            'candidateNo' => $certificateNumber,
            'module' => $courseTitle,
            'version' => $version,
            'date' => $issueDate,
            'footerDate' => $issueDate,
            'layout' => $layout,
            'backgroundImage' => $backgroundImage,
            'page' => $page,
            'unit' => $unit,
        ]);

        return $download ? $pdf->download($filename) : $pdf->inline($filename);
    }
}
