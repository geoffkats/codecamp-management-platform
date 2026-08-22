<?php

namespace App\Http\Controllers;

use App\Services\CertificateDataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CertificateController extends Controller
{
    public function sampleCsv(): Response
    {
        $rows = [
            ['candidate_name', 'candidate_no', 'module_name', 'module_version', 'module_date', 'signature_date'],
            ['Jane Nakato',    'CAU-2024-001', 'HTML & CSS Fundamentals', 'v2.1', '2024-03-15', '2024-04-01'],
            ['Jane Nakato',    'CAU-2024-001', 'JavaScript Basics',       'v3.0', '2024-03-28', '2024-04-01'],
            ['Brian Ochieng',  'CAU-2024-002', 'Python for Beginners',    'v1.5', '2024-04-10', '2024-04-20'],
        ];

        $csv = implode("\n", array_map(
            fn ($row) => implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', $v) . '"', $row)),
            $rows
        ));

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="certificate_sample.csv"',
        ]);
    }

    /** Live HTML preview of the profile certificate template */
    public function preview(Request $request, CertificateDataService $dataService)
    {
        $modules = [];
        if ($request->filled('modules')) {
            foreach (explode('|', $request->string('modules')) as $chunk) {
                [$name, $version, $date] = array_pad(explode(';', $chunk), 3, null);
                if ($name) {
                    $modules[] = ['name' => $name, 'version' => $version ?: '1.0', 'date' => $date ?: now()->format('Y-m-d')];
                }
            }
        }

        if ($modules === []) {
            $modules = [
                ['name' => 'Scratch Intermediate', 'version' => '1.0', 'date' => '2025-12-19'],
                ['name' => 'Scratch Advanced', 'version' => '1.0', 'date' => '2026-01-29'],
            ];
        }

        $layoutOverrides = [];
        foreach ($request->query() as $key => $value) {
            if (preg_match('/^layout_(signature|signatory|date)_(.+)$/', $key, $matches)) {
                $layoutOverrides[$matches[1]][$matches[2]] = $value;
            }
        }

        $payload = $dataService->formatPayload(
            candidateName: $request->string('name', 'KASIMBA ISHAKA TENDO'),
            candidateNo: $request->string('no', 'STU-2025-0063'),
            signatureDate: Carbon::parse($request->input('date', now()->format('Y-m-d'))),
            modules: $modules,
            context: [
                'signatory_key' => $request->input('signatory', 'default'),
                'custom_signatory' => $request->input('custom_signatory'),
                'layout_overrides' => $layoutOverrides,
            ],
        );

        return view(config('certificate.html_template', 'certificates.profile'), $payload);
    }
}
