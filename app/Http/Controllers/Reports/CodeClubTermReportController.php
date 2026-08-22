<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\CodeClub;
use App\Models\User;
use App\Services\Reports\CodeClubTermReportPdfService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class CodeClubTermReportController extends Controller
{
    use AuthorizesRequests;

    public function download(
        CodeClub $club,
        User $student,
        CodeClubTermReportPdfService $service,
        Request $request,
    ) {
        $this->authorizeAccess($club);

        [$from, $to, $termKey] = $this->periodFromRequest($request);

        return $service->render($club, $student, true, $from, $to, $termKey);
    }

    public function preview(
        CodeClub $club,
        User $student,
        CodeClubTermReportPdfService $service,
        Request $request,
    ) {
        $this->authorizeAccess($club);

        [$from, $to, $termKey] = $this->periodFromRequest($request);

        return $service->render($club, $student, false, $from, $to, $termKey);
    }

    public function html(
        CodeClub $club,
        User $student,
        CodeClubTermReportPdfService $service,
        Request $request,
    ) {
        $this->authorizeAccess($club);

        [$from, $to, $termKey] = $this->periodFromRequest($request);

        return response($service->renderHtml($club, $student, $from, $to, $termKey));
    }

    public function schoolSummary(
        CodeClub $club,
        CodeClubTermReportPdfService $service,
        Request $request,
    ) {
        $this->authorizeAccess($club);

        [$from, $to, $termKey] = $this->periodFromRequest($request);

        return $service->renderSchoolSummary($club, true, $from, $to, $termKey);
    }

    public function bulkDownload(
        CodeClub $club,
        CodeClubTermReportPdfService $service,
        Request $request,
    ) {
        $this->authorizeAccess($club);

        [$from, $to, $termKey] = $this->periodFromRequest($request);

        $memberships = $club->activeMemberships()
            ->with(['student.studentProfile'])
            ->get();

        if ($memberships->isEmpty()) {
            abort(404, 'No active members in this club.');
        }

        $zipPath = storage_path('app/temp/codeclub_reports_'.$club->id.'_'.now()->format('Ymd_His').'.zip');
        @mkdir(dirname($zipPath), 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create zip archive.');
        }

        $added = 0;

        foreach ($memberships as $membership) {
            $student = $membership->student;
            if (! $student) {
                continue;
            }

            try {
                $pdfContent = $service->generatePdfContent($club, $student, $from, $to, $termKey);
                $zip->addFromString($service->filenameFor($club, $student), $pdfContent);
                $added++;
            } catch (\Throwable $e) {
                Log::warning('Code Club bulk term report skipped for student', [
                    'club_id' => $club->id,
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $zip->close();

        if ($added === 0) {
            @unlink($zipPath);
            abort(500, 'Could not generate any term reports.');
        }

        return response()->download($zipPath, $service->zipFilename($club))->deleteFileAfterSend(true);
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon, 2: ?string}
     */
    private function periodFromRequest(Request $request): array
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : null;
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : null;
        $termKey = $request->query('term_key') ?: $request->query('term');

        return [$from, $to, $termKey ? (string) $termKey : null];
    }

    private function authorizeAccess(CodeClub $club): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $this->authorize('generateReports', $club);
    }
}
