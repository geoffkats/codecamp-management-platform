<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Student Performance Report — {{ $student['name'] ?? 'Student' }}</title>
    @php
        use Illuminate\Support\Str;

        $cNavy   = '#1e3a5f';
        $cIndigo = '#1e40af';
        $cOrange = '#f97316';
        $cBlack  = '#000000';
        $cText   = '#1e293b';
        $cGray   = '#475569';
        $cLight  = '#f1f5f9';
        $cTrack  = '#cbd5e1';
        $cPill   = '#1e3a5f';

        $studentName = $student['name'] ?? 'Student';
        $clubName = $club['name'] ?? 'Code Club';
        $schoolName = $school['name'] ?? ($student['school_name'] ?? 'Partner School');
        $schoolNameLen = Str::length($schoolName);
        $schoolNameClass = $schoolNameLen > 42 ? 'xlong' : ($schoolNameLen > 28 ? 'long' : '');
        $institutionLabel = $branding['org_name'] ?? config('codeclub-reports.institution_label', 'Code Academy Uganda');
        $termLabel = $term_label ?? 'Term Report';
        $headerSubtitle = $clubName;

        $rawPeriod = trim((string) ($period['label'] ?? ''));
        if ($rawPeriod !== '' && ! in_array(Str::lower($rawPeriod), ['full club period', 'full term', '—', '-'], true)) {
            $termBadge = Str::upper($termLabel).' • '.Str::upper($rawPeriod);
        } else {
            $termBadge = Str::upper($termLabel);
        }

        $overallScore = (float) ($overall['score'] ?? 0);
        $overallLabel = $overall['label'] ?? '';
        $commentMax = (int) config('codeclub-reports.report_comment_max_chars', 340);

        $metrics = collect($performance_metrics ?? [])
            ->map(fn ($m) => [
                'label' => $m['label'] ?? 'Metric',
                'score' => (float) ($m['score'] ?? 0),
                'grade' => $m['grade'] ?? 'D',
                'color' => $m['color'] ?? 'orange',
            ])
            ->values();

        if ($metrics->isEmpty()) {
            $metrics = collect();
        }

        $scores = $metrics->pluck('score');
        $distribution = [
            ['num' => $scores->isNotEmpty() ? number_format($scores->avg(), 1) : '0.0', 'label' => 'Average', 'orange' => false],
            ['num' => $scores->isNotEmpty() ? number_format($scores->max(), 1) : '0.0', 'label' => 'Highest', 'orange' => false],
            ['num' => $scores->isNotEmpty() ? number_format($scores->min(), 1) : '0.0', 'label' => 'Lowest', 'orange' => false],
            ['num' => (string) $metrics->where('grade', 'A+')->count(), 'label' => 'A+ Count', 'orange' => true],
            ['num' => (string) $metrics->whereIn('grade', ['A+', 'A', 'A-'])->count(), 'label' => 'A Range', 'orange' => false],
        ];

        $logoPath = $branding['logo_path'] ?? null;
        $logoSrc = null;
        if (! empty($logoPath) && file_exists($logoPath)) {
            $mime = @mime_content_type($logoPath) ?: 'image/png';
            $logoSrc = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($logoPath));
        }

        $instructorDisplay = trim($instructor['name'] ?? 'Club Facilitator');
        $instructorRole = 'Code Club Facilitator';
        $feedbackText = Str::limit(strip_tags((string) ($instructor_comment ?? '')), $commentMax);
    @endphp
    <style>
        @page { margin: 0; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: {{ $cText }};
            background: #ffffff;
            line-height: 1.45;
        }
        table { border-collapse: collapse; }
        .page {
            width: 210mm;
            background: #ffffff;
            page-break-after: avoid;
        }

        .header {
            background: #ffffff;
            border-bottom: 0.6mm solid {{ $cNavy }};
            padding: 6mm 10mm 5.5mm 10mm;
        }
        .header-table { width: 100%; }
        .header-table td { vertical-align: middle; }
        .header-left { padding-right: 8mm; }
        .header-right { width: 34mm; text-align: right; vertical-align: middle; }
        .school-name {
            font-size: 15pt;
            font-weight: bold;
            color: {{ $cBlack }};
            line-height: 1.15;
        }
        .school-name.long { font-size: 13pt; }
        .school-name.xlong { font-size: 11pt; }
        .school-sub {
            font-size: 9pt;
            font-weight: bold;
            color: {{ $cNavy }};
            margin-top: 1.5mm;
            line-height: 1.3;
        }
        .powered-by {
            font-size: 7.5pt;
            color: {{ $cGray }};
            margin-top: 1.5mm;
        }
        .badges { margin-top: 3mm; }
        .badge {
            display: inline-block;
            background: {{ $cPill }};
            color: #ffffff;
            font-size: 7pt;
            font-weight: bold;
            padding: 1.2mm 3mm;
            border-radius: 3mm;
            margin-right: 2mm;
        }
        .logo-box {
            width: 34mm;
            min-height: 20mm;
            background: #ffffff;
            border: 0.3mm solid {{ $cTrack }};
            border-radius: 2.5mm;
            text-align: center;
            padding: 2mm;
        }
        .logo-box img { display: block; width: 28mm; height: auto; margin: 0 auto; }

        .student-bar {
            background: {{ $cLight }};
            border-bottom: 0.3mm solid {{ $cTrack }};
            padding: 4.5mm 10mm;
        }
        .student-bar-table { width: 100%; }
        .student-bar-table td { vertical-align: middle; }
        .student-name { font-size: 14pt; font-weight: bold; color: {{ $cBlack }}; }
        .student-club { font-size: 8.5pt; color: {{ $cGray }}; margin-top: 0.8mm; }
        .score-label {
            font-size: 6.5pt;
            font-weight: bold;
            color: {{ $cGray }};
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        .score-big { font-size: 23pt; font-weight: bold; color: {{ $cNavy }}; line-height: 1; }
        .score-tag { font-size: 8.5pt; font-weight: bold; color: {{ $cOrange }}; }

        .body-pad { padding: 5mm 10mm 6mm 10mm; }
        .section-title {
            font-size: 8pt;
            font-weight: bold;
            color: {{ $cOrange }};
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding-bottom: 2mm;
            margin-bottom: 2.5mm;
            border-bottom: 0.3mm solid {{ $cTrack }};
        }
        .section-block { margin-bottom: 4mm; }
        .section-block:last-child { margin-bottom: 0; }

        .metric { margin-bottom: 2.4mm; }
        .metric-row { width: 100%; }
        .metric-row td { vertical-align: middle; padding: 0 0 1mm 0; }
        .metric-name { font-size: 8.5pt; font-weight: bold; color: {{ $cBlack }}; }
        .metric-meta { text-align: right; white-space: nowrap; }
        .metric-score { font-size: 8.5pt; font-weight: bold; color: {{ $cNavy }}; }
        .grade-pill {
            display: inline-block;
            border: 0.3mm solid {{ $cNavy }};
            border-radius: 1.2mm;
            font-size: 7pt;
            font-weight: bold;
            color: {{ $cNavy }};
            padding: 0.3mm 1.6mm;
            margin-left: 2.5mm;
        }
        .bar-track { width: 100%; height: 2.8mm; background: {{ $cTrack }}; border-radius: 1.4mm; }
        .bar-fill { height: 2.8mm; border-radius: 1.4mm; }
        .bar-blue { background: {{ $cIndigo }}; }
        .bar-orange { background: {{ $cOrange }}; }

        .dist-table { width: 100%; table-layout: fixed; }
        .dist-table td { padding: 0 1.2mm; vertical-align: top; }
        .dist-table td:first-child { padding-left: 0; }
        .dist-table td:last-child { padding-right: 0; }
        .dist-box { background: {{ $cLight }}; border-radius: 2.5mm; text-align: center; padding: 2.8mm 1mm; }
        .dist-num { font-size: 14pt; font-weight: bold; color: {{ $cNavy }}; line-height: 1; }
        .dist-num.orange { color: {{ $cOrange }}; }
        .dist-lbl { font-size: 7pt; color: {{ $cGray }}; margin-top: 1.8mm; font-weight: bold; }

        .feedback-section { page-break-inside: avoid; }
        .feedback-box {
            background: #eef2f9;
            border-left: 1mm solid {{ $cOrange }};
            border-radius: 0 2mm 2mm 0;
            padding: 3.5mm 4mm 3mm 4mm;
            page-break-inside: avoid;
        }
        .feedback-text {
            font-size: 8.5pt;
            font-style: italic;
            color: {{ $cBlack }};
            line-height: 1.45;
        }
        .feedback-footer {
            margin-top: 3mm;
            padding-top: 2.5mm;
            border-top: 0.3mm solid #c7d2e3;
        }
        .instructor-row { width: 100%; }
        .instructor-row td { vertical-align: middle; }
        .instructor-badge {
            width: 7mm;
            height: 7mm;
            border-radius: 50%;
            background: {{ $cNavy }};
            text-align: center;
            line-height: 7mm;
        }
        .instructor-name { font-size: 8.5pt; font-weight: bold; color: {{ $cBlack }}; }
        .instructor-org { font-size: 7.5pt; color: {{ $cGray }}; }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <div class="school-name {{ $schoolNameClass }}">{{ $schoolName }}</div>
                    <div class="school-sub">{{ $headerSubtitle }}</div>
                    <div class="powered-by">Delivered by {{ $institutionLabel }}</div>
                    <div class="badges">
                        <span class="badge">{{ $termBadge }}</span>
                        <span class="badge">Student Performance Report</span>
                    </div>
                </td>
                <td class="header-right">
                    <div class="logo-box">
                        @if($logoSrc)
                            <img src="{{ $logoSrc }}" alt="{{ $institutionLabel }}">
                        @else
                            <svg width="28" height="28" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" style="display:block;margin:0 auto;">
                                <rect x="4" y="4" width="18" height="18" rx="3" fill="{{ $cOrange }}"/>
                                <rect x="26" y="4" width="18" height="18" rx="3" fill="{{ $cIndigo }}"/>
                                <rect x="4" y="26" width="18" height="18" rx="3" fill="{{ $cIndigo }}"/>
                                <rect x="26" y="26" width="18" height="18" rx="3" fill="{{ $cOrange }}"/>
                            </svg>
                            <div style="font-size:6pt;font-weight:bold;color:#000;margin-top:1mm;line-height:1.15;">CODE ACADEMY</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="student-bar">
        <table class="student-bar-table">
            <tr>
                <td style="width: 65%;">
                    <div class="student-name">{{ $studentName }}</div>
                    <div class="student-club">{{ $clubName }}</div>
                </td>
                <td style="width: 35%; text-align: right;">
                    <div class="score-label">Overall Score</div>
                    <div class="score-big">{{ number_format($overallScore, 1) }}</div>
                    <div class="score-tag">{{ $overallLabel }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="body-pad">

        <div class="section-block">
            <div class="section-title">Performance Metrics</div>
            @foreach($metrics as $metric)
                <div class="metric">
                    <table class="metric-row">
                        <tr>
                            <td class="metric-name">{{ $metric['label'] }}</td>
                            <td class="metric-meta">
                                <span class="metric-score">{{ number_format($metric['score'], 1) }}</span>
                                <span class="grade-pill">{{ $metric['grade'] }}</span>
                            </td>
                        </tr>
                    </table>
                    <div class="bar-track">
                        <div class="bar-fill {{ $metric['color'] === 'blue' ? 'bar-blue' : 'bar-orange' }}" style="width: {{ max(2, $metric['score']) }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="section-block">
            <div class="section-title">Score Distribution</div>
            <table class="dist-table">
                <tr>
                    @foreach($distribution as $box)
                        <td>
                            <div class="dist-box">
                                <div class="dist-num {{ $box['orange'] ? 'orange' : '' }}">{{ $box['num'] }}</div>
                                <div class="dist-lbl">{{ $box['label'] }}</div>
                            </div>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>

        <div class="section-block feedback-section">
            <div class="section-title">Instructor Feedback</div>
            <div class="feedback-box">
                <div class="feedback-text">&ldquo;{{ $feedbackText }}&rdquo;</div>
                <div class="feedback-footer">
                    <table class="instructor-row">
                        <tr>
                            <td style="width: 10mm;">
                                <div class="instructor-badge">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;">
                                        <circle cx="12" cy="7" r="3" stroke="#ffffff" stroke-width="1.8"/>
                                        <path d="M6 20v-1.5c0-2.5 2.7-4.5 6-4.5s6 2 6 4.5V20" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </div>
                            </td>
                            <td>
                                <div class="instructor-name">{{ $instructorDisplay }}</div>
                                <div class="instructor-org">{{ $instructorRole }} · {{ $institutionLabel }}</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
</body>
</html>
