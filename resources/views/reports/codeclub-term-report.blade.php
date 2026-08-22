<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Student Performance Report — {{ $studentName }}</title>
    @php
        $cNavy   = '#1e3a5f';
        $cIndigo = '#1e40af';
        $cOrange = '#f97316';
        $cBlack  = '#000000';
        $cText   = '#1e293b';
        $cGray   = '#475569';
        $cLight  = '#f1f5f9';
        $cTrack  = '#cbd5e1';
        $cPill   = '#1e3a5f';
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

        /* ── HEADER ── */
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

        /* ── STUDENT BAR ── */
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

        /* ── BODY ── */
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

        /* ── METRICS (single column) ── */
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

        /* ── DISTRIBUTION ── */
        .dist-table { width: 100%; table-layout: fixed; }
        .dist-table td { padding: 0 1.2mm; vertical-align: top; }
        .dist-table td:first-child { padding-left: 0; }
        .dist-table td:last-child { padding-right: 0; }
        .dist-box { background: {{ $cLight }}; border-radius: 2.5mm; text-align: center; padding: 2.8mm 1mm; }
        .dist-num { font-size: 14pt; font-weight: bold; color: {{ $cNavy }}; line-height: 1; }
        .dist-num.orange { color: {{ $cOrange }}; }
        .dist-lbl { font-size: 7pt; color: {{ $cGray }}; margin-top: 1.8mm; font-weight: bold; }

        /* ── FEEDBACK (kept together on one page) ── */
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
@php
    use Illuminate\Support\Str;

    $overall = (float) ($summary['overall_score'] ?? 0);
    $metricsLimit = (int) config('codeclub-reports.report_metrics_limit', 10);
    $commentMax = (int) config('codeclub-reports.report_comment_max_chars', 340);

    $sourceValue = function (string $source) use ($summary, $assignments, $overall) {
        $val = match ($source) {
            'attendance'  => (float) ($summary['attendance'] ?? 0),
            'quiz'        => (float) ($summary['quiz_average'] ?? 0),
            'assignments' => (($assignments['total'] ?? 0) > 0)
                ? ((($assignments['on_time'] ?? 0) * 100) + (($assignments['late'] ?? 0) * 70)) / $assignments['total']
                : 0,
            default       => $overall,
        };

        return $val > 0 ? $val : $overall;
    };

    $metricGrade = function (float $score) {
        foreach (config('codeclub-reports.metric_grade_scale', []) as $band) {
            if ($score >= ($band['min'] ?? 0)) {
                return $band['grade'];
            }
        }
        return 'D';
    };

    $metrics = collect(config('codeclub-reports.performance_metrics', []))
        ->map(function ($def) use ($sourceValue, $metricGrade) {
            $score = round(max(0, min(100, $sourceValue($def['source'] ?? 'overall') + ($def['offset'] ?? 0))), 1);
            $grade = $metricGrade($score);

            return [
                'label' => $def['label'],
                'score' => $score,
                'grade' => $grade,
                'color' => in_array($grade, ['A+', 'A'], true) ? 'blue' : 'orange',
            ];
        })
        ->sortByDesc('score')
        ->take($metricsLimit)
        ->values();

    $scores = $metrics->pluck('score');
    $distribution = [
        ['num' => $scores->isNotEmpty() ? number_format($scores->avg(), 1) : '0.0', 'label' => 'Average', 'orange' => false],
        ['num' => $scores->isNotEmpty() ? number_format($scores->max(), 1) : '0.0', 'label' => 'Highest', 'orange' => false],
        ['num' => $scores->isNotEmpty() ? number_format($scores->min(), 1) : '0.0', 'label' => 'Lowest', 'orange' => false],
        ['num' => (string) $metrics->where('grade', 'A+')->count(), 'label' => 'A+ Count', 'orange' => true],
        ['num' => (string) $metrics->whereIn('grade', ['A+', 'A', 'A-'])->count(), 'label' => 'A Range', 'orange' => false],
    ];

    $logoSrc = null;
    if (! empty($logoPath) && file_exists($logoPath)) {
        $mime = @mime_content_type($logoPath) ?: 'image/png';
        $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
    }

    $instructorDisplay = trim($instructorName ?? 'Club Facilitator');
    $instructorRole = trim($instructorTitle ?? 'Code Club Facilitator');
    $feedbackText = Str::limit(strip_tags((string) ($instructorComment ?? '')), $commentMax);
@endphp

<div class="page">

    {{-- ── HEADER ── --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <div class="school-name {{ $schoolNameClass }}">{{ $schoolName }}</div>
                    <div class="school-sub">{{ $headerSubtitle }}</div>
                    <div class="powered-by">Delivered by {{ $institutionLabel }}</div>
                    <div class="badges">
                        <span class="badge">{{ $termLabel }}</span>
                        <span class="badge">Student Performance Report</span>
                    </div>
                </td>
                <td class="header-right">
                    <div class="logo-box">
                        @if($logoSrc)
                            <img src="{{ $logoSrc }}" alt="{{ $institutionLabel }}">
                        @else
                            @include('reports.partials.code-academy-logo')
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── STUDENT BAR ── --}}
    <div class="student-bar">
        <table class="student-bar-table">
            <tr>
                <td style="width: 65%;">
                    <div class="student-name">{{ $studentName }}</div>
                    <div class="student-club">{{ $clubName }}</div>
                </td>
                <td style="width: 35%; text-align: right;">
                    <div class="score-label">Overall Score</div>
                    <div class="score-big">{{ number_format($overall, 1) }}</div>
                    <div class="score-tag">{{ $summary['overall_score_label'] ?? '' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="body-pad">

        {{-- ── PERFORMANCE METRICS ── --}}
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

        {{-- ── SCORE DISTRIBUTION ── --}}
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

        {{-- ── INSTRUCTOR FEEDBACK ── --}}
        <div class="section-block feedback-section">
            <div class="section-title">Instructor Feedback</div>
            <div class="feedback-box">
                <div class="feedback-text">&ldquo;{{ $feedbackText }}&rdquo;</div>
                <div class="feedback-footer">
                    <table class="instructor-row">
                        <tr>
                            <td style="width: 10mm;">
                                <div class="instructor-badge">
                                    @include('reports.partials.codeclub-icon', ['icon' => 'instructor', 'color' => '#ffffff', 'size' => 11])
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
