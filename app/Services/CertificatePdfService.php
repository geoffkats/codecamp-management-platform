<?php

namespace App\Services;

use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class CertificatePdfService
{
    public function __construct(
        private CertificateDataService $dataService,
    ) {}

    public function render(Certificate $certificate, bool $download = true)
    {
        $view = config('certificate.html_template', 'certificates.profile');
        $viewData = $this->dataService->resolve($certificate) + [
            'certificate' => $certificate,
        ];

        $filename = 'certificate-' . ($certificate->certificate_number ?? $certificate->id) . '.pdf';

        if ($this->shouldUseDomPdf()) {
            return $this->renderWithDomPdf($view, $viewData, $filename, $download);
        }

        if ($this->canUseSnappyBinary()) {
            try {
                $pdf = app('snappy.pdf.wrapper');
                $pdf->setOption('enable-local-file-access', true);
                $pdf->loadView($view, $viewData);

                return $download ? $pdf->download($filename) : $pdf->inline($filename);
            } catch (\Throwable $e) {
                Log::error('Certificate PDF generation failed (snappy), falling back to DomPDF', [
                    'certificate_id' => $certificate->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->renderWithDomPdf($view, $viewData, $filename, $download);
    }

    public function renderPreview(array $data, bool $download = false)
    {
        $view = config('certificate.html_template', 'certificates.profile');
        $filename = 'certificate-preview.pdf';

        return $this->renderWithDomPdf($view, $data, $filename, $download);
    }

    private function renderWithDomPdf(string $view, array $viewData, string $filename, bool $download)
    {
        $pdf = Pdf::loadView($view, $viewData)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
                'dpi'                  => 150,
            ]);

        return $download ? $pdf->download($filename) : $pdf->stream($filename);
    }

    private function shouldUseDomPdf(): bool
    {
        return (bool) config('certificate.use_dompdf', true);
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
