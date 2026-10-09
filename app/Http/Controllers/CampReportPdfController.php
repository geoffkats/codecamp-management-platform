<?php

namespace App\Http\Controllers;

use App\Models\CampReport;
use App\Models\CodeCamp;
use App\Models\SystemSetting;
use App\Services\Reports\CampReportNarrator;
use App\Services\Reports\CampReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CampReportPdfController extends Controller
{
    public function __invoke(Request $request, CodeCamp $camp, CampReportService $reports, CampReportNarrator $narrator)
    {
        abort_unless($request->user()->can('review_daily_reports'), 403);

        $data = $reports->build($camp);
        $narrative = CampReport::where('camp_id', $camp->id)->first()
            ?? new CampReport($narrator->write($data, useAi: false));

        $pdf = Pdf::loadView('reports.camp-report', [...$data, 'narrative' => $narrative, 'logo' => $this->logo()])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 120,
                'chroot' => [public_path()],
            ]);

        $filename = 'camp-report-'.str($camp->name)->slug().'-'.now()->format('Y-m-d').'.pdf';

        return $request->boolean('inline') ? $pdf->stream($filename) : $pdf->download($filename);
    }

    private function logo(): ?string
    {
        foreach (array_filter([SystemSetting::get('logo'), SystemSetting::get('logo_dark')]) as $relative) {
            $relative = ltrim((string) $relative, '/\\');

            foreach ([storage_path('app/public/'.$relative), public_path('storage/'.$relative), public_path($relative)] as $path) {
                if (is_file($path) && in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg'], true)) {
                    return 'data:'.(mime_content_type($path) ?: 'image/png').';base64,'.base64_encode(file_get_contents($path));
                }
            }
        }

        return null;
    }
}
